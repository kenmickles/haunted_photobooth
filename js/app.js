App = {
	socket: null,
	host: "ws://127.0.0.1:8080/~ken/haunted_photobooth/server.php",
	blank_photo: "http://img.37i.net/pixel_ffffff_0.png",
	photos_to_take: 3,
	photos_received: 0,
	timer: 3,
	
	countdown: function(){
		if ( App.timer > 0 ) {	
			$('#status').text(App.timer);
			setTimeout(function(){
				App.timer = App.timer - 1;				
				App.countdown();
			}, 1000);
		}
		else {
			$('#status').text('Say "cheese"');
			App.take_photo();
			App.timer = 3;			
		}
	},
	
	take_photo: function() {
		App.socket.send(App.photos_to_take);
	},
	
	init: function(){
		$(window).load(function(){
			$('#photo-strips').masonry({
				resizeable: true,
				itemSelector: '.brick',
				columnWidth: 150
			});
			
			$.each($('#photo-strips img'), function(i, img){
				var d = Math.random()*8+1;
				if ( Math.floor(Math.random()*2+1) == 1 ) {
					d *= -1;
				}
				$(img).css('-webkit-transform', 'rotate(-' + d + 'deg)');
			});
		});
		
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
						
						if ( photo.match(/combined/) ) {
							// add new photo strip the the pile	
							var d = Math.random()*8+1;
							var $html = $('<div class="brick" style="-webkit-transform:rotate(-' + d + 'deg)"><img src="' + photo + '" alt="" /></div>');
							$('#photo-strips').append($html).masonry({appendedContent: $html});
														
							// show success message
							$('#status').text("Alright! Your photos are on Facebook.");
							
							// wait a couple seconds and clean up
							setTimeout(function(){
								App.photos_received = 0;
								App.timer = 3;
								
								var $status = $('#status');
								$status.text($status.attr('title'));
																
								$('#photos').fadeOut('normal', function(){
									$('#photos img').remove();							
								});
							}, 4000);					
							
							return;	
						}
						
						App.photos_received = App.photos_received + 1;
						$('#photos').prepend('<img src="' + photo + '" alt="" />');

						if ( $('#photos img').length == App.photos_to_take ) {
							$('#status').text("Sending photos to Facebook...");				
						}
					}
					else {
						$('#flash').fadeIn(1000, function(){
							$('#photos').show();
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
			
			// hide popup when escape key is pressed
			$(document).keyup(function(e) {
				// the numbers 1-9 = keyCodes 49-57
				if ( e.keyCode >= 49 && e.keyCode <= 57 ) {
					App.photos_to_take = e.keyCode - 48;
					App.countdown();
					return;
				}
			});
			
			// take photo button
			$('.take-photo').click(function(){
				App.photos_to_take = 3;
				App.countdown();
				return false;
			});
		});
	}
};
