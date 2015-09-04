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

function BxDolNotifications(oOptions) {
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oBxDolNotifications' : oOptions.sObjName;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
    this._iDisplayCount = oOptions.iDisplayCount == undefined ? 3 : oOptions.iDisplayCount;
    this._iTimeoutLength = oOptions.iTimeoutLength == undefined ? 10000 : oOptions.iTimeoutLength;
    this._iTimeoutId = 0;
}
BxDolNotifications.prototype.correctLayout = function() {
    
}
BxDolNotifications.prototype.toggle = function() {
    if(this._iTimeoutId == 0) {
        var $this = this;
        this._iTimeoutId = setTimeout(this._sObjName + '.update();', this._iTimeoutLength);
    }
    else
        clearTimeout(this._iTimeoutId);
}
BxDolNotifications.prototype.update = function() {    
    var $this = this;
    var oDate = new Date();
    $.post (
        this._sActionsUrl,
        {action: 'update', _t: oDate.getTime()},
        function(sResult) {
            if(!$.trim(sResult)) return;

            var iCountHide = $('#sys-ntns > .sys-ntn:visible').length + $(sResult).length - $this._iDisplayCount;
            var oResponseHide = function() {
                iCountHide--;
                if($(this).next('.sys-ntn:visible') && iCountHide > 0) {
                    $(this).next('.sys-ntn:visible').bx_anim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseHide);                    
                }
                else if(iCountHide <= 0 && $.trim(sResult)) {
                    var iCountShow = $(sResult).length;                
                    var oResponseShow = function() {
                        iCountShow--;
                        if($(this).next('.sys-ntn:hidden') && iCountShow > 0)
                            $(this).next('.sys-ntn:hidden').bx_anim('show', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseShow);
                    }
                    $(this).nextAll('.sys-ntn:last').after($(sResult).hide()).next('.sys-ntn:hidden').bx_anim('show', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseShow);
                }
                $(this).remove();
            }
            if(iCountHide > 0)
                $('#sys-ntns > .sys-ntn:first').bx_anim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseHide);
            else {
                var iCountShow = $(sResult).length;                
                var oResponseShow = function() {
                    iCountShow--;
                    if($(this).next('.sys-ntn:hidden') && iCountShow > 0)
                        $(this).next('.sys-ntn:hidden').bx_anim('show', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseShow);
                }
                $('#sys-ntns > .clear_both').before($(sResult).hide()).prevAll('.sys-ntn:hidden:last').bx_anim('show', $this._sAnimationEffect, $this._iAnimationSpeed, oResponseShow);                
            }
                
        }
    );
    this._iTimeoutId = setTimeout(this._sObjName + '.update();', this._iTimeoutLength);
}