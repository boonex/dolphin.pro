function BxMbpJoin(oOptions) {
    this._sSystem = oOptions.sSystem;
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oMbpJoin' : oOptions.sObjName;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}

BxMbpJoin.prototype.onSubmit = function(oForm) {
	if(!$(oForm).find(":radio[name = 'descriptor']:checked").length) {
		alert(_t('_membership_err_need_select_level'));
		return false;
	}

	if(!$(oForm).find(":radio[name = 'provider']:checked").length && !$(oForm).find(":hidden[name = 'provider']").val()) {
		alert(_t('_membership_err_need_select_provider'));
		return false;
	}

	return true;
};