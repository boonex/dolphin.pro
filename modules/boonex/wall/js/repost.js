function BxWallRepost(oOptions) {
	this._sActionsUri = oOptions.sActionUri;
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oWallRepost' : oOptions.sObjName;
    this._iOwnerId = oOptions.iOwnerId == undefined ? 0 : oOptions.iOwnerId;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
    this._aHtmlIds = oOptions.aHtmlIds == undefined ? {} : oOptions.aHtmlIds;
    this._oRequestParams = oOptions.oRequestParams == undefined ? {} : oOptions.oRequestParams;
}

BxWallRepost.prototype.repostItem = function(oLink, iOwnerId, sType, sAction, iId) {
	var $this = this;
	var oParams = $.extend(this._getDefaultParams(), {
		owner_id: iOwnerId,
		type: sType,
		action: sAction,
		object_id: iId
	});

	var oLoading = $('#bx-wall-view-loading');
    if(oLoading)
    	oLoading.bx_loading(true);

	jQuery.post(
        this._sActionsUrl + 'repost/',
        oParams,
        function(oData) {
        	if(oLoading)
            	oLoading.bx_loading(false);

        	if(oData && oData.msg != undefined && oData.msg.length > 0)
                alert(oData.msg);

        	if(oData && oData.counter != undefined) {
        		var sCounter = $(oData.counter).attr('id');
        		/*
        		 * Full replace (with link)
        		$('#' + sCounter).replaceWith(oData.counter);
        		*/
        		$('#' + sCounter + ' i').html(oData.count);
        		$('#' + sCounter).parents('.wall-repost-counter-holder:first').bx_anim(oData.count > 0 ? 'show' : 'hide');
        	}

        	if(oData && oData.disabled)
    			$(oLink).removeAttr('onclick').addClass($(oLink).hasClass('bx-btn') ? 'bx-btn-disabled' : 'wall-repost-disabled');
        },
        'json'
    );
};

BxWallRepost.prototype.toggleByPopup = function(oLink, iId) {
	var $this = this;
    var oParams = this._getDefaultParams();
    oParams['id'] = iId;

	var oLoading = $('#bx-wall-view-loading');
    if(oLoading)
    	oLoading.bx_loading(true);

    jQuery.get(
    	this._sActionsUrl + 'get_reposted_by/',
        oParams,
        function(oData) {
        	if(oLoading)
            	oLoading.bx_loading(false);

        	$('#' + $this._aHtmlIds['by_popup'] + iId).remove();

        	$(oData.content).hide().prependTo('body').dolPopup({
                fog: {
    				color: '#fff',
    				opacity: .7
                }
            });
        },
        'json'
    );

	return false;
};

BxWallRepost.prototype._getDefaultParams = function () {
	var oDate = new Date();
    return $.extend({}, this._oRequestParams, {_t:oDate.getTime()});
};