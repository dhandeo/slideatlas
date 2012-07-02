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

$(document).ready(function() {  
    $('#configForm').ajaxForm({
        beforeSubmit: midas.slideatlas.config.validateConfig,
        success: midas.slideatlas.config.successConfig
    });
});
