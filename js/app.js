App = {
	socket: null,
	host: "ws://127.0.0.1:8080/~ken/haunted_photobooth/server.php",
	blank_photo: "http://img.37i.net/pixel_ffffff_0.png",
	photos_to_take: 3,
	timer: 3,
	in_progress: false,
	
	countdown: function(){
		App.in_progress = true;
		
		if ( App.timer == 3 ) {
			$('#photo-strips').hide();
		}
		
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
							var $html = $('<div class="brick"><img src="' + photo + '" alt="" style="-webkit-transform:rotate(-' + d + 'deg)" /></div>');
														
							// show success message
							$('#status').text("Alright! Your photos are on Facebook.");
							
							// wait a couple seconds and clean up
							setTimeout(function(){
								//window.location.reload(); return;
								
								App.timer = 3;
								App.in_progress = false;
								
								var $status = $('#status');
								$status.text($status.attr('title'));
																								
								$('#photos').fadeOut('fast', function(){
									$('#photos img').remove();							
									$('#photo-strips').fadeIn();
									$('#photo-strips').append($html).masonry({appendedContent: $html});															
								});
							}, 4000);					
							
							return;	
						}
						
						$('#photos').prepend('<img src="' + photo + '" alt="" />');

						if ( $('#photos img').length == App.photos_to_take ) {
							$('#status').text("Sending photos to Facebook...");				
						}
					}
					else {
						$('#flash').fadeIn(800, function(){
							$('#photos').show();
							$(this).fadeOut(1800);
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
				if (App.in_progress) return;
				
				var photo_count = 0;
				
				switch ( e.keyCode ) {
					// 1 keys
					case 35:
					case 97:
					case 49:
						photo_count = 1;
						break;
						
					// 2 keys
					case 40:
					case 98:
					case 50:
						photo_count = 2;
						break;
						
					// 3 keys
					case 34:
					case 99:
					case 51:
						photo_count = 3;
						break;
						
					// 4 keys
					case 37:
					case 100:
					case 52:
						photo_count = 4;
						break;
					
					// the star key refreshes the page
					case 106:
						window.location.reload();
						break;
						
					// all other number keys = 4, because that's how many FB will let us upload
					case 53:
					case 54:
					case 55:
					case 56:
					case 57:
					case 48:
					case 101:
					case 102:
					case 103:
					case 104:
					case 105:
					case 96:
					case 39:
					case 36:
					case 38:
					case 33:
						photo_count = 4;
						break;
						
					default:
						console.log(e.keyCode);
				}

				// the numbers 1-9 = keyCodes 49-57
				if ( photo_count > 0 ) {
					App.photos_to_take = photo_count;
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
