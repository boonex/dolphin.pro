/***************************************************************************
 *                            Dolphin Web Community Software
 *                              -------------------
 *     begin                : Mon Mar 23 2006
 *     copyright            : (C) 2007 BoonEx Group
 *     website              : http://www.boonex.com
 *
 *
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This is a free software; you can modify it under the terms of BoonEx
 *   Product License Agreement published on BoonEx site at http://www.boonex.com/downloads/license.pdf
 *   You may not however distribute it for free or/and a fee.
 *   This notice may not be removed from the source code. You may not also remove any other visible
 *   reference and links to BoonEx Group as provided in source code.
 *
 ***************************************************************************/

function BxDolSubscription(oOptions) {
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'BxDolSubscription' : oOptions.sObjName;
    this._sVisitorPopup = oOptions.sVisitorPopup == undefined ? 'sbs_visitor_popup' : oOptions.sVisitorPopup;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
}

BxDolSubscription.prototype.send = function(oForm) {
    var $this = this;
    
    $(oForm).ajaxSubmit({
        dataType: 'json',
		success: function(oData) {
			alert( oData.message );
			
			if(parseInt(oData.code) == 0)
                $('#' + $this._sVisitorPopup).dolPopupHide();
		}
	});
	return false;
};

BxDolSubscription.prototype.subscribe = function(iUserId, sUnit, sAction, iObjectId, onResult) {
    var oParams = {
        direction: 'subscribe',
        unit: sUnit,
        action: sAction,
        object_id: iObjectId
    };
    
    iUserId = parseInt(iUserId);
    if(iUserId != 0) {
        oParams['user_id'] = iUserId;
        this._sbs_action(oParams, onResult);
    }
    else {
        $("#" + this._sVisitorPopup + " [name='direction']").val('subscribe');
        $("#" + this._sVisitorPopup + " [name='unit']").val(sUnit);
        $("#" + this._sVisitorPopup + " [name='action']").val(sAction);
        $("#" + this._sVisitorPopup + " [name='object_id']").val(iObjectId);
        $("#" + this._sVisitorPopup).dolPopup();
    }
};

BxDolSubscription.prototype.unsubscribe = function(iUserId, sUnit, sAction, iObjectId, onResult) {
    var oParams = {
        direction: 'unsubscribe',
        unit: sUnit,
        action: sAction,
        object_id: iObjectId
    };
    
    iUserId = parseInt(iUserId);
    if(iUserId != 0) {
        oParams['user_id'] = iUserId;
        this._sbs_action(oParams, onResult);        
    }
};

BxDolSubscription.prototype.unsubscribeConfirm = function(sUrl) {
	$(document).dolPopupConfirm({
		message: _t('_sbs_wrn_unsubscribe'), 
		onClickYes: function() {
			$.get(
				sUrl + '&js=1',
				{},
				function(oData){
					alert(oData.message);

					if(oData.code == 0)
						window.location.href = window.location.href;
				},
				'json'
			);
		}
	});
};

BxDolSubscription.prototype._sbs_action = function(oParams, onResult) {
    if(onResult == undefined)
        onResult = function(oData) {
            alert(oData.message);
        }
    
    $.post(
        this._sActionsUrl,
        oParams,
        onResult,
        'json'
    );
};