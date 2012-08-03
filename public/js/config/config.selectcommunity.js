var midas = midas || {};
midas.slideatlas = midas.slideatlas || {};
midas.slideatlas.config = midas.slideatlas.config || {};

midas.slideatlas.config.selectCommunityCallbackSelect = function (node) {
    var selectedElement = node.find('span:eq(0)').html();
    if(node.attr('element') == -1 || node.attr('element') == -2) {
        $('div.MainDialogContent #selectElements').attr('disabled', 'disabled');
    }
    else {
        $('div.MainDialogContent #selectedDestinationHidden').val(node.attr('element'));
        $('div.MainDialogContent #selectedDestination').html(sliceFileName(selectedElement, 40));
        $('div.MainDialogContent #selectElements').removeAttr('disabled');
    }
}

$(document).ready(function() {
    $("#moveTable").treeTable({
        callbackSelect: midas.slideatlas.config.selectCommunityCallbackSelect
    });
    $("div.MainDialogContent img.tableLoading").hide();
    $("table#moveTable").show();

    if($('div.MainDialogContent #selectElements') != undefined)
        {
        $('div.MainDialogContent #selectElements').click(function(){
            var communityName = $('#selectedDestination').html();
            var communityId = $('#selectedDestinationHidden').val();
            $( "div.MainDialog" ).dialog('close');
            if(typeof midas.communitySelectionCallback == 'function')
                {
                midas.communitySelectionCallback(communityName, communityId);
                }
            return;
            });
        }
});

