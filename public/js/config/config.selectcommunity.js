$("#moveTable").treeTable({
  callbackSelect: selectCommunityCallbackSelect
  });
$("div.MainDialogContent img.tableLoading").hide();
$("table#moveTable").show();

if($('div.MainDialogContent #selectElements') != undefined)
  {
  $('div.MainDialogContent #selectElements').click(function(){
    var folderName = $('#selectedDestination').html();
    var folderId = $('#selectedDestinationHidden').val();
    midas.doCallback('CALLBACK_CORE_UPLOAD_FOLDER_CHANGED', {folderName: folderName, folderId: folderId});

    $('#destinationUpload').html(folderName);
    $('#destinationId').val(folderId);
    $('.destinationUpload').html(folderName);
    $('.destinationId').val(folderId);
    $( "div.MainDialog" ).dialog('close');

    if(typeof folderSelectionCallback == 'function')
      {
      folderSelectionCallback(folderName, folderId);
      }
    return false;
    });
  }

//dependance: common/browser.js
var ajaxSelectRequest = '';
function selectCommunityCallbackSelect(node) {
    var selectedElement = node.find('span:eq(1)').html();

    var parent = true;
    var current = node;

    while(parent != null) {
        parent = null;
        var classNames = current[0].className.split(' ');
        for(key in classNames) {
            if(classNames[key].match("child-of-")) {
                parent = $("div.MainDialogContent #" + classNames[key].substring(9));
            }
        }
        if(parent != null) {
            selectedElement = parent.find('span:eq(1)').html()+'/'+selectedElement;
            current = parent;
        }
    }

    $('div.MainDialogContent #createFolderContent').hide();
    if(node.attr('element') == -1 || node.attr('element') == -2) {
        $('div.MainDialogContent #selectElements').attr('disabled', 'disabled');
        $('div.MainDialogContent #createFolderButton').hide();
    }
    else {
        $('div.MainDialogContent #selectedDestinationHidden').val(node.attr('element'));
        $('div.MainDialogContent #selectedDestination').html(sliceFileName(selectedElement, 40));
        $('div.MainDialogContent #selectElements').removeAttr('disabled');

        if($('div.MainDialogContent #defaultPolicy').val() != 0) {
            $('div.MainDialogContent #createFolderButton').show();
        }
    }
}

$('#moveTable ajaimg.infoLoading').show();
$('div.MainDialogContent div.ajaxInfoElement').html('');
