function BxFeedbackMain(oOptions) {
    this._sSystem = oOptions.sSystem;
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oFeedbackMain' : oOptions.sObjName;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}
BxFeedbackMain.prototype.changePage = function(iStart, iPerPage, sType, sTypeParams) {
	var $this = this;
    var oDate = new Date();    
    var oParams = {
    	_t:oDate.getTime()
    }

    if(sTypeParams)
    	oParams['params'] = sTypeParams;

    if($('#feedback-filter-chb:checked').length > 0 && $('#feedback-filter-txt').val().length > 0)
    	oParams['filter_value'] = $('#feedback-filter-txt').val();

    var sLoadingId = '#feedback-' + sType + '-loading'; 
    $(sLoadingId).bx_loading();

    $.post(
        this._sActionsUrl + 'act_get_feedback/' + (sType ? sType + '/' : '') + iStart + '/' + iPerPage + '/',
        oParams,
        function(sData) {
        	$(sLoadingId).bx_loading();

            $('.feedback-view #feedback-content-' + sType).bx_anim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                $(this).replaceWith(sData);
            });
        },
        'html'
    );
}
BxFeedbackMain.prototype.deleteEntry = function(iId) {
	var $this = this;

	$.post(
		this._sActionsUrl + "act_delete/",
		{id:iId},
		function(sData) {
			var iCode = parseInt(sData);
			if(iCode == 1) {
				alert(aDolLang['_feedback_msg_success_delete']);
				window.location.href = $this._sActionsUrl
			}
			else
				alert(aDolLang['_feedback_msg_failed_delete']);
		}
	)
}