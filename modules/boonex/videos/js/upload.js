var bPossibleToReload = false;
var bVideoRecorded = false;

function rayVideoReady(bMode, sExtra) {
	bVideoRecorded = bMode;
	shVideoEnableSubmit(bMode);
	if(!bVideoRecorded)
		$('#video_accepted_files_block').text("");
}

function shVideoEnableSubmit(bMode) {
	if($('#video_upload_form').attr("name") == 'embed')
		return;

	var oButton = $('#video_upload_form .form_input_submit');
	if(bMode)
		oButton.removeAttr('disabled');
	else
		oButton.attr('disabled', 'disabled');
}

function BxVideoUpload(oOptions) {    
    //this._sActionsUrl = oOptions.sActionUrl;
    //this._sObjName = oOptions.sObjName == undefined ? 'oCommonUpload' : oOptions.sObjName;
    this._iOwnerId = oOptions.iOwnerId == undefined ? 0 : parseInt(oOptions.iOwnerId);
    //this._iGlobAllowHtml = 0;
    // this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    // this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}

BxVideoUpload.prototype.genSendFileInfoForm = function(iMID, sForm) {
    if (iMID > 0 && sForm != '') {
    	$(sForm).appendTo('#video_accepted_files_block').addWebForms();
    	this.changeContinueButtonStatus();
    }
}

BxVideoUpload.prototype.getType = function() {
	return $('#video_upload_form').attr("name");
}

BxVideoUpload.prototype.changeContinueButtonStatus = function () {
	switch(this.getType()) {
		case 'upload':
			var sFileVal = $('#video_upload_form .video_upload_form_wrapper .form_input_file').val();
			var sAcceptedFilesBlockVal = $('#video_accepted_files_block').text();
			shVideoEnableSubmit(sFileVal != null && sFileVal != '' && sAcceptedFilesBlockVal == '');

            bPossibleToReload = true;
			break;
			
		case 'embed':
			this.checkEmbed();

            bPossibleToReload = true;
			break;
		
		case 'record':
			shVideoEnableSubmit(bVideoRecorded && $('#video_accepted_files_block').text() == "");

            bPossibleToReload = true;
			break;
			
		default:
			break;
	}
}

BxVideoUpload.prototype.doValidateFileInfo = function(oButtonDom, iFileID) {
	var bRes = true;
	if ($('#send_file_info_' + iFileID + ' input[name=title]').val()=='') {
		$('#send_file_info_' + iFileID + ' input[name=title]').parent().parent().children('.warn').show().attr('float_info', _t('_bx_videos_val_title_err'));
		bRes = false;
	}
	else
		$('#send_file_info_' + iFileID + ' input[name=title]').parent().parent().children('.warn').hide();	
	
        return bRes; //can submit
}

BxVideoUpload.prototype.cancelSendFileInfo = function(iMID, sWorkingFile) {
	if(iMID == "")
		this.cancelSendFileInfoResult("");
    else if(iMID > 0 && sWorkingFile == "")
		this.cancelSendFileInfoResult(iMID);
	else
	{
		var $this = this;
		$.post(bx_append_url_params(sWorkingFile, "action=cancel_file&file_id=" + iMID), function(data){
			if (data==1)
				$this.cancelSendFileInfoResult(iMID);
		});
	}
}

BxVideoUpload.prototype.cancelSendFileInfoResult = function(iMID) {
	$('#send_file_info_'+iMID).remove();
	this.changeContinueButtonStatus();

    $('#video_accepted_files_block script').remove();
    if (bPossibleToReload && $('#video_accepted_files_block').text() == '')
        window.location.href = window.location.href;
}

BxVideoUpload.prototype.onSuccessSendingFileInfo = function(iMID) {
	$('#send_file_info_'+iMID).remove();

	setTimeout( function(){
		$('#video_success_message').show(1000)
		setTimeout( function(){
			$('#video_success_message').hide(1000);
		}, 3000);
	}, 500);

	this.changeContinueButtonStatus();

    $('#video_accepted_files_block script').remove();
    if (bPossibleToReload && $('#video_accepted_files_block').text() == '')
        window.location.href = window.location.href;
	
	switch(this.getType()) {
		case 'upload':
			this.resetUpload();
			break
		case 'embed':
			this.resetEmbed();
			break;
		case 'record':
			getRayFlashObject("video", "recorder").removeRecord();
			break;
	}
}

BxVideoUpload.prototype.showErrorMsg = function(sErrorCode) {
	var oErrorDiv = $('#' + sErrorCode);

	var $this = this;

	setTimeout( function(){
		oErrorDiv.show(1000)
		setTimeout( function(){
			oErrorDiv.hide(1000);
			$this._loading(false);
		}, 3000);
	}, 500);

}

BxVideoUpload.prototype.onFileChangedEvent = function (oElement) {
	this.changeContinueButtonStatus();
};

BxVideoUpload.prototype._loading = function (bShow) {
	$('.upload-loading-container').bx_loading(bShow);
}

BxVideoUpload.prototype.resetUpload = function () {
	var oCheck = $('#video_upload_form [type="checkbox"]');
	oCheck.removeAttr("checked");

	var oFiles = $('#video_upload_form .input_wrapper_file');
	var oFileIcons = $('#video_upload_form .multiply_remove_button');
	if (oFiles.length>1) {
		oFiles.each( function(iInd) {
			if (iInd != 0) {
				$(this).remove();
			}
		});
		oFileIcons.each( function(iIndI) {
			$(this).remove();
		});
	}

	var oFile = $('#video_upload_form [type="file"]');
	oFile.val("");

	shVideoEnableSubmit(false);
}

BxVideoUpload.prototype.resetEmbed = function () {
	var tText = $('#video_upload_form [name="embed"]');
	tText.attr("value", "");
	shVideoEnableSubmit(false);
}

BxVideoUpload.prototype.checkEmbed = function (bAlert) {
	var tText = $('#video_upload_form [name="embed"]');
	var sText = tText.attr("value").split(" ").join("");
	var aUrlParts = sText.split("?", 2);
	sText = aUrlParts[0];
	if(aUrlParts[1] != undefined && aUrlParts[1] != "")
	{
		aUrlParts = aUrlParts[1].split("&");
		for(var i=0; i<aUrlParts.length; i++)
			if(aUrlParts[i].indexOf("v=") == 0)
			{
				sText += "?" + aUrlParts[i];
				break;
			}
	}
	var bResult = (/^https?:\/\/(www.)?youtube.com\/watch\?v=([0-9A-Za-z_-]{11})$/.test(sText) || /^https?:\/\/(www.)?youtu.be\/([0-9A-Za-z_-]{11})$/.test(sText)) && $('#video_accepted_files_block').text() == "";
	if(bResult)
		tText.attr("value", sText);
    if (bAlert && !bResult)
        alert(_t('_bx_videos_emb_err'));
	return bResult;
}
