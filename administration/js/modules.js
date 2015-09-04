function BxManageModules(oOptions) {    
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oMM' : oOptions.sObjName;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'fade' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}

BxManageModules.prototype.checkForUpdates = function(oButton) {
	var sUpdateKey = 'mi-update';
    var oParent = $(oButton).parents('.disignBoxFirst');

    oParent.find('.' + sUpdateKey).remove();
    oParent.find("[name = 'pathes[]']").each(function() {
    	var oCheckbox = $(this);
    	if(parseInt(oCheckbox.attr('bx_can_update')) != 1)
    		return;

    	oCheckbox.parent().append('<span class="' + sUpdateKey + ' bx-def-font-grayed">' + aDolLang['_sys_txt_btn_loading'] + '</span>');
    	$.post(
    		this._sActionsUrl,
    		{
    			action: 'check_for_updates',
    		    path: oCheckbox.val()
    		},
    	    function(oResult) {
    			if(oResult.content && oResult.content.length > 0)
    				oCheckbox.siblings('.' + sUpdateKey).replaceWith(oResult.content);
    		},
    		'json'
    	);
    });
};

BxManageModules.prototype.downloadUpdate = function(sLink) {
	var $this = this;

	$.post(
		this._sActionsUrl,
		{
			action: 'download_updates',
		    link: sLink
		},
	    function(oResult) {
			if(oResult.message && oResult.message.length > 0)
				alert(oResult.message);

			if(oResult.code == 0)
				window.location.href = $this._sActionsUrl; 
		},
		'json'
	);
};

BxManageModules.prototype.onSubmitUninstall = function(oButton) {
	$(document).dolPopupConfirm({
		message: _t('_adm_txt_modules_data_will_be_lost'), 
		onClickYes: function() {
			$(oButton).removeAttr('onclick').trigger('click');
		}
	});

	return false;
};