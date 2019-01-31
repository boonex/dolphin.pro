var bPossibleToReload = false;
var bPhotoRecorded = false;

function rayPhotoReady(bMode, sExtra) {
	bPhotoRecorded = bMode;
	shPhotoEnableSubmit(bMode);
	if(!bPhotoRecorded)
		$('#photo_accepted_files_block').text("");
}

function shPhotoEnableSubmit(bMode) {
	if($('#photo_upload_form').attr("name") == 'embed')
		return;

	var oButton = $('#photo_upload_form .form_input_submit');
	if(bMode)
		oButton.removeAttr('disabled');
	else
		oButton.attr('disabled', 'disabled');
}

function BxPhotoUpload(oOptions) {    
    //this._sActionsUrl = oOptions.sActionUrl;
    //this._sObjName = oOptions.sObjName == undefined ? 'oCommonUpload' : oOptions.sObjName;
    this._iOwnerId = oOptions.iOwnerId == undefined ? 0 : parseInt(oOptions.iOwnerId);
    //this._iGlobAllowHtml = 0;
    // this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    // this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}

BxPhotoUpload.prototype.genSendFileInfoForm = function(iMID, sForm) {
    if (iMID > 0 && sForm != '') {    	
    	$(sForm).appendTo('#photo_accepted_files_block').addWebForms();
    	this.changeContinueButtonStatus();
    }
}

BxPhotoUpload.prototype.getType = function() {
	return $('#photo_upload_form').attr("name");
}

BxPhotoUpload.prototype.changeContinueButtonStatus = function () {
	switch(this.getType()) {
		case 'upload':
			var sFileVal = $('#photo_upload_form .photo_upload_form_wrapper .form_input_file').val();
			var sAcceptedFilesBlockVal = $('#photo_accepted_files_block').text();
			shPhotoEnableSubmit(sFileVal != null && sFileVal != '' && sAcceptedFilesBlockVal == '');

            bPossibleToReload = true;
			break;
			
		case 'embed':
			this.checkEmbed();

            bPossibleToReload = true;
			break;
		
		case 'record':
			shPhotoEnableSubmit(bPhotoRecorded && $('#photo_accepted_files_block').text() == "");

            bPossibleToReload = true;
			break;
			
		default:
			break;
	}
}

BxPhotoUpload.prototype.doValidateFileInfo = function(oButtonDom, iFileID) {
	var bRes = true;
	if ($('#send_file_info_' + iFileID + ' input[name=title]').val()=='') {
		$('#send_file_info_' + iFileID + ' input[name=title]').parent().parent().children('.warn').show().attr('float_info', _t('_bx_photos_val_title_err'));
		bRes = false;
	}
	else
		$('#send_file_info_' + iFileID + ' input[name=title]').parent().parent().children('.warn').hide();
	
	return bRes; //can submit
}

BxPhotoUpload.prototype.cancelSendFileInfo = function(iMID, sWorkingFile) {
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

BxPhotoUpload.prototype.cancelSendFileInfoResult = function(iMID) {
	$('#send_file_info_'+iMID).remove();
	this.changeContinueButtonStatus();

    $('#photo_accepted_files_block script').remove();
    if (bPossibleToReload && $('#photo_accepted_files_block').text() == '')
        window.location.href = window.location.href;
}

BxPhotoUpload.prototype.onSuccessSendingFileInfo = function(iMID) {
	$('#send_file_info_'+iMID).remove();

	setTimeout( function(){
		$('#photo_success_message').show(1000)
		setTimeout( function(){
			$('#photo_success_message').hide(1000);
		}, 3000);
	}, 500);


	this.changeContinueButtonStatus();

    $('#photo_accepted_files_block script').remove();
    if (bPossibleToReload && $('#photo_accepted_files_block').text() == '')
        window.location.href = window.location.href;
	
	switch(this.getType()) {
		case 'upload':
			this.resetUpload();
			break
		case 'embed':
			this.resetEmbed();
			break;
		case 'record':
			getRayFlashObject("photo", "shooter").removeRecord();
			break;
	}
}

BxPhotoUpload.prototype.changeErrorMsgBoxMsg = function(sSelector, sErrorCode) {
    var oErrorDiv = $('#' + sSelector + ' .msgbox_text');
    oErrorDiv.text(sErrorCode);
}

BxPhotoUpload.prototype.showErrorMsg = function(sErrorCode) {
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

BxPhotoUpload.prototype.onFileChangedEvent = function (oElement) {
	this.changeContinueButtonStatus();
};

BxPhotoUpload.prototype._loading = function (bShow) {
    var oLoading = $('.upload-loading-container');
    if(bShow) {
        oLoading.css('left', (oLoading.parent().width() - oLoading.width())/2);
        oLoading.css('top', (oLoading.parent().height() - oLoading.height())/2);
        oLoading.show();
    }
    else
        oLoading.hide();
}

BxPhotoUpload.prototype.resetUpload = function () {
	var oCheck = $('#photo_upload_form [type="checkbox"]');
	oCheck.removeAttr("checked");

	var oFiles = $('#photo_upload_form .input_wrapper_file');
	var oFileIcons = $('#photo_upload_form .multiply_remove_button');
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

	var oFile = $('#photo_upload_form [type="file"]');
	oFile.val("");

	shPhotoEnableSubmit(false);
}

BxPhotoUpload.prototype.resetEmbed = function () {
    $('#photo_upload_form [name="embed"]').attr("value", "");

    shPhotoEnableSubmit(false);
}

BxPhotoUpload.prototype.checkEmbed = function (bAlert) {
    var sText = $('#photo_upload_form [name="embed"]').attr("value").split(" ").join("");

    var bResult = /^https?:\/\/(www.)?flickr.com\/photos\/([0-9A-Za-z_@-]+)\/([0-9]{11})\/$/.test(sText) && $('#photo_accepted_files_block').text() == "";
    if(bAlert && !bResult)
        alert(_t('_bx_photos_emb_err'));

    return bResult;
}
