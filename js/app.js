App = {
	socket: null,
	host: "ws://127.0.0.1:8080/~ken/haunted_photobooth/server.php",
	blank_photo: "http://img.37i.net/pixel_ffffff_0.png",
	
	take_photo: function(count) {
		count = count || 3;
		App.socket.send(count);
	},
	
	init: function(){
		$(document).ready(function(){
			try {
				App.socket = new WebSocket(App.host);
				
				App.socket.onopen = function(msg){
					console.log('opened')
				};
				
				App.socket.onmessage = function(msg){
					console.log('`' + msg.data + '`');
					var photo = msg.data;
					if ( photo.match(/.jpg$/) ) {
						$('#photos img:first').attr('src', photo);
					}
					else {
						$('#flash').fadeIn(1000, function(){
							$('#photos').prepend('<img src="' + App.blank_photo + '" alt="" />');
							$(this).fadeOut(2000);
						});
					}
				};
				
				App.socket.onclose = function(msg){
					console.log('closed') 
				};
			}
			catch (ex) {
				console.log(ex); 
			}
			
			// take photo button
			$('.take-photo').click(function(){
				var $div = $('#countdown');
				
				$div.show();				
				var timer = parseInt($div.find('h1').text());
				
				if ( timer > 0 ) {				
					setTimeout(function(){
						var $h1 = $('#countdown h1');
						var t = parseInt($h1.text()) - 1;
						$h1.text(t);
						$('.take-photo').click();
					}, 1000);
				}
				else {
					$div.hide();
					$div.find('h1').text(3);
					App.take_photo(3);
				}
				
				return false;
			});
		});
	}
};
