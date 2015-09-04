$.fn.bx_anim = function(action, effect, speed, h) {    
   return this.each(function() {   		
   		var sFunc = '';
   		var sEval;

   		if (speed == 0)
   			effect = 'default';
   			
  		switch (action) {
  			case 'show':
  				switch (effect) {
  					case 'slide': sFunc = 'slideDown'; break;
  					case 'fade': sFunc = 'fadeIn'; break;
  					default: sFunc = 'show';
  				}  				
  				break;
  			case 'hide':
  				switch (effect) {
  					case 'slide': sFunc = 'slideUp'; break;
  					case 'fade': sFunc = 'fadeOut'; break;
  					default: sFunc = 'hide';
  				}  				
  				break;  				
  			default:
  			case 'toggle':
  				switch (effect) {
  					case 'slide': sFunc = 'slideToggle'; break;
  					case 'fade': sFunc = ($(this).filter(':visible').length) ? 'fadeOut' : 'fadeIn'; break;
  					default: sFunc = 'toggle';
  				}  				  			  				
  		}
  		
  		if ((0 == speed || undefined == speed) && undefined == h) {
  			sEval = '$(this).' + sFunc + '();';
  		}
  		else if ((0 == speed || undefined == speed) && undefined != h) {
  			sEval = '$(this).' + sFunc + '(); $(this).each(h);';
  		}
  		else {
  			sEval = '$(this).' + sFunc + "('" + speed + "', h);";
  		}
  		eval(sEval);
  		
  		return this;
   });  
};

$.fn.bx_loading = function(bShow, sParentSelector) {
    var iWidth = 0, iHeight = 0, iMinHeight = 0;
    var oParent = null;
    var sLoading = 'sys-loading', sLoadingIcon = 'sys-loading-icon';

    if(sParentSelector != undefined)
        oParent = $(sParentSelector);

    return this.each(function() {
        var oLoading = $(this);
        if(oLoading.find('.' + sLoading).length > 0)
        	oLoading = oLoading.find('.' + sLoading);
        else if(!oLoading.is('.' + sLoading))
        	oLoading = oLoading.append('<div class="' + sLoading + '"></div>').find('.' + sLoading);

        var oLoadingIcon = oLoading.find('.' + sLoadingIcon);
        if(!oLoadingIcon.is('.' + sLoadingIcon))
        	oLoadingIcon = oLoading.append(
        		'<div class="sys-loading-smog"></div>' + 
        		'<div class="' + sLoadingIcon + '">' + 
        			'<div class="spinner bx-def-margin-topbottom bx-def-margin-sec-leftright"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' + 
        	    '</div>').find('.' + sLoadingIcon);

        if(bShow == undefined)
        	bShow = !oLoading.is(':visible');

        if(bShow) {
        	oParent = oParent != null ? oParent : oLoading.parent();

        	if(oParent.children(':not(.' + sLoading + ')').length > 0) {
        		iWidth = oParent.outerWidth();
                iHeight = oParent.outerHeight();
                iMinHeight = oLoadingIcon.outerHeight() + 20;

                oLoading.width(iWidth);
    			oLoading.height(iHeight > iMinHeight ? iHeight : iMinHeight);

    			oLoadingIcon.css('left', (oLoading.outerWidth() - parseInt(oLoadingIcon.css('width')))/2);
        	}
        	else
        		oLoading.addClass('sys-loading-inline');           

        	oLoadingIcon.css('top', (oLoading.outerHeight() - parseInt(oLoadingIcon.css('height')))/2);
            oLoading.show();
        }
        else
            oLoading.hide();
    });
};

$.fn.bx_btn_loading = function(sParentSelector) {
	var sAttr = 'bx-loading-id';
	var oDate = new Date();
    return this.each(function() {
        var oObject = $(this);
        if(!oObject.hasClass('bx-btn'))
        	return;

        if(oObject.is(':visible')) {
        	var oClone = oObject.attr(sAttr, oDate.getTime()).clone().attr('disabled', 'disabled').addClass('bx-btn-loading').html(aDolLang['_sys_txt_btn_loading']);
        	oObject.hide().after(oClone);
        }
        else {
        	var iId = oObject.attr(sAttr);
        	oObject.removeAttr(sAttr).show().siblings("[" + sAttr + "='" + iId + "']").remove();
        }
    });
};

$.fn.bx_message_box = function(sMessage, iTimer, onClose) {
    return this.each(function() {
        var oParent = $(this);
        
        if(oParent.children(':first').hasClass('MsgBox'))
            oParent.children(':first').replaceWith(sMessage);
        else 
            oParent.prepend(sMessage);

        if(iTimer == undefined || parseInt(iTimer) == 0)
            return;

        setTimeout(function(oParent, onClose) {
            oParent.children('div.MsgBox:first').bx_anim('hide', 'fade', 'slow', function(){
                $(this).remove();
                if(onClose != undefined)
                    onClose();
            });
        }, 1000 * parseInt(iTimer), oParent, onClose);
    });
};
