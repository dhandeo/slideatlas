var midas = midas || {};
midas.slideatlas = midas.slideatlas || {};
midas.slideatlas.config = midas.slideatlas.config || {};

midas.slideatlas.config.validateConfig = function (formData, jqForm, options) {
}

midas.slideatlas.config.successConfig = function (responseText, statusText, xhr, form) {
  try {
      var jsonResponse = jQuery.parseJSON(responseText);
  } catch (e) {
      midas.createNotice("An error occured. Please check the logs.", 4000, 'error');
      return false;
  }
  if(jsonResponse == null) {
      midas.createNotice('Error', 4000, 'error');
      return;
  }
  if(jsonResponse[0]) {
      midas.createNotice(jsonResponse[1], 4000);
  }
  else {
      midas.createNotice(jsonResponse[1], 4000, 'error');
  }
}

midas.slideatlas.config.initSupportedFormats = function () {
    var inputSupportAll = $('input[name=supportAll]');
    var inputImageFormats = $('input[name=imageFormats]');
    var inputFormatsDiv = $('div#imageformatsDiv');

    if(inputSupportAll.filter(':checked').val() == 1) {
        inputImageFormats.attr('disabled', 'disabled');
        inputImageFormats.removeAttr('checked');
        inputImageFormats.filter('[value=0]').attr('checked', true);
        inputFormatsDiv.hide();
    }
    else {
        inputImageFormats.removeAttr('disabled');
        inputFormatsDiv.show();
    }
    inputSupportAll.change(function () {
        midas.slideatlas.config.initSupportedFormats();
    });
}

$(document).ready(function() {
    midas.slideatlas.config.initSupportedFormats();  
    $('#configForm').ajaxForm({
        beforeSubmit: midas.slideatlas.config.validateConfig,
        success: midas.slideatlas.config.successConfig
    });
    $('#imageformatsForm').ajaxForm({
        beforeSubmit: midas.slideatlas.config.validateConfig,
        success: midas.slideatlas.config.successConfig
    });
    
    $('a.communityDeleteLink img').fadeTo('fast', 0.4);
    $('a.communityDeleteLink img').hover(function() {
        $(this).fadeTo('fast', 1.0);
    },
    function() {
        $(this).fadeTo('fast', 0.4);
    });
    $('a.communityDeleteLink').click(function () {
        var communityCell = $(this).parents('tr');
        var communityId = $(this).attr('element');
        var html = '';
        html+=json.message['deleteCommunityMessage'];
        html+='<br/>';
        html+='<br/>';
        html+='<br/>';
        html+='<input style="margin-left:140px;" class="globalButton deleteCommunityYes" element="'+$(this).attr('element')+'" type="button" value="'+json.global.Yes+'"/>';
        html+='<input style="margin-left:50px;" class="globalButton deleteCommunityNo" type="button" value="'+json.global.No+'"/>';
        midas.showDialogWithContent(json.message['delete'],html,false);

        $('input.deleteCommunityYes').unbind('click').click(function () {
            $.post(json.global.webroot+'/slideatlas/config', { element: communityId, deleteCommunity: true});
            communityCell.remove();
            $( "div.MainDialog" ).dialog('close');
        });
        $('input.deleteCommunityNo').unbind('click').click(function() {
            $( "div.MainDialog" ).dialog('close');
        });
    });
    $('a.addCommunityLink').click(function () {
        midas.loadDialog("selectcommunity","/slideatlas/config/selectcommunity");
        midas.showDialog('Browse for adding a community to slide atlas community list');
    });
});
