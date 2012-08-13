var midas = midas || {};
midas.slideatlas = midas.slideatlas || {};
midas.slideatlas.user = midas.slideatlas.user || {};

/**
 * Call this to display the large image viewer section on the item
 */
midas.slideatlas.user.displayviewer = function(url) {
    'use strict';
    $('#slideatlasViewerSection').find('a')
      .attr('href', url);
    $('a#popup').qtip({
                    content: 'Click to view the image in a big pop-up window',
                    position: {
                        target: 'mouse',
                        my: 'bottom left',
                        viewport: $(window), // Keep the qtip on-screen at all times
                        effect: true // Disable positioning animation
                    }
    }).click(function(){
       var NWin = window.open($(this).prop('href'), '', 'height=1000, width=1200');
       if(window.focus){
          NWin.focus();
          }
        return false;
       });
    $('#slideatlasViewerSection').show()
      .find('iframe')
      .attr('src', url);
}

midas.slideatlas.user.viewersetup = function(item_id) {
    'use strict';
    // Hide the thumbnailcreatorLargeImageSection
    if($('#thumbnailcreatorLargeImageSection').length > 0 )
      {
      $('#thumbnailcreatorLargeImageSection').hide()
        .parent()
        .attr('class', 'disableitemViewMainSection');    
      }

    $.get(json.global.webroot+'/slideatlas/user/getiteminfo', {
        itemId: item_id
        }, function(data) {
            var resp = $.parseJSON(data);
            if(resp.status == 'ok') {
                var html = json.global.webroot+'/slideatlas/user/fullscreen?image='
                   + resp.itemname +'&levels='+ resp.levels+'&tileSize='+ resp.tilesize;
            midas.slideatlas.user.displayviewer(html);
            }
            else
              {
               // Show the thumbnailcreatorLargeImageSection
               if($('#thumbnailcreatorLargeImageSection').length > 0 )
                {
                $('#thumbnailcreatorLargeImageSection').show()
                  .parent()
                  .attr('class', 'itemViewMainSection');  
               }  
              }
        });
}

$(window).load(function () {
    midas.slideatlas.user.viewersetup(json.item.item_id);

});
