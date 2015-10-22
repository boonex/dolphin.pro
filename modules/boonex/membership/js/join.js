function BxMbpJoin(oOptions) {
    this._sSystem = oOptions.sSystem;
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oMbpJoin' : oOptions.sObjName;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
    this._sErrSelectLevel = oOptions.sErrSelectLevel == undefined ? _t('_Error occured') : oOptions.sErrSelectLevel;
    this._sErrSelectProvider = oOptions.sErrSelectProvider == undefined ? _t('_Error occured') : oOptions.sErrSelectProvider;
}

BxMbpJoin.prototype.onSubmit = function(oForm) {
	if(!$(oForm).find(":radio[name = 'descriptor']:checked").length) {
		alert(this._sErrSelectLevel);
		return false;
	}

	if(!$(oForm).find(":radio[name = 'provider']:checked").length && !$(oForm).find(":hidden[name = 'provider']").val()) {
		alert(this._sErrSelectProvider);
		return false;
	}

	return true;
};