$(document).ready(function () {
    midas.registerCallback('CALLBACK_UPDATE_BIG_THUMBNAIL', 'slideatlas', function(args) {
    // get parameters
    $.get(json.global.webroot+'/slideatlas/user/getiteminfo', {
        itemId: args.item_id
        }, function(data) {
            var resp = $.parseJSON(data);
            if(resp.status == 'ok') {
                // add an anchor
                var html = json.global.webroot+'/slideatlas/user/fullscreen?image='
                   + resp.itemname +'&levels='+ resp.levels+'&tileSize='+ resp.tilesize;
                $('img.largeImage').wrap($('<a id="popup">').attr('href', html));
                // use a pop-up window 
                $('a#popup').qtip({
                    content: 'Click to view the whole image',
                    position: {
                        target: 'mouse',
                        my: 'bottom left',
                        viewport: $(window), // Keep the qtip on-screen at all times
                        effect: true // Disable positioning animation
                   }
                }).click(function() {
	                  var NWin = window.open($(this).prop('href'), '', 'height=800,width=1200');
	                  if(window.focus) {
	                      NWin.focus();
	                      }
	                  return false;
	                  });  
            }
        });
    });
});
