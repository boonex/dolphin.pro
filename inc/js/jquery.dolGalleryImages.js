// jQuery plugin - Dolphin RSS Aggregator
(function($){
	$.fn.dolGalleryImages = function(options) {
        
        var m_aSettings = $.extend({
            'noimg' : 'templates/base/images/icons/no-photo-110.png',
            'icons_size' : 32,
            'icons_opacity' : 0.7,
            'icons_animation_speed' : 400 
        }, options);

        var m_eCurrent = null;

        var aMethods = {

            Activate: function (e) {
                var eCont = $(this);
                var iId = aMethods.GetID.apply (eCont, [e]);
                var eImgCont = jQuery('#bx-gallery-img-cont-' + iId);       
                var callbackOnLoad = function () {
                    eImgCont.find('.bx-gallery-img')[0].src = this.src;
                    aMethods.Show.apply (eCont, [eImgCont, this.height, this.width]);
                    bx_loading(eCont.find('.bx-gallery-imgs').attr('id'), false);                
                };
                var callbackOnError = function () {
                    eImgCont.find('.bx-gallery-img')[0].src = m_aSettings.noimg;
                    aMethods.Show.apply (eCont, [eImgCont, 0, 0]);
                    bx_loading(eCont.find('.bx-gallery-imgs').attr('id'), false);
                };
                bx_loading(eCont.find('.bx-gallery-imgs').attr('id'), true);                
                if ($.browser.opera) {
                    var eImg = new Image();
                    eImg.src = eImgCont.attr('data-img');
                    eImg.onload = callbackOnLoad;
                    eImg.onerror = callbackOnError;
                } else {
                    $('<img src="' + eImgCont.attr('data-img') + '" />').filter('img').bind({
                        load: callbackOnLoad,
                        error: callbackOnError
                    });
                }
            },

            Show: function (e, iHeight, iWidth) {
                var eCont = $(this);
                var iId = aMethods.GetID.apply (eCont, [e]);
                var eImgCont = jQuery('#bx-gallery-img-cont-' + iId);                
                var eImg = jQuery('#bx-gallery-img-' + iId).get(0);
                var eIcon = jQuery('#bx-gallery-icon-' + iId);
                var eTitle = eImgCont.find('.bx-gallery-img-title');
                var isFixed = aMethods.FixContainerHeightAndCenter.apply (eCont, [eImgCont, iHeight, iWidth]);

                if (null != m_eCurrent) 
                    m_eCurrent.fadeOut();

                eImgCont.fadeIn(function () {
                    eTitle.fadeIn();
                    if (!isFixed)
                        aMethods.FixContainerHeightAndCenter.apply (eCont, [eImgCont]);
                });

                eCont.find('.bx-gallery-icon').fadeTo(0, m_aSettings.icons_opacity);
                eIcon.fadeTo(m_aSettings.icons_animation_speed, 1);
                eCont.find('.bx-gallery-icons-rails').animate({marginLeft: (parseInt(eCont.find('.bx-gallery-icons').innerWidth()) / 2 - eIcon.position().left - m_aSettings.icons_size / 2) + 'px'}, m_aSettings.icons_animation_speed);

                m_eCurrent = eImgCont;
            },

            FixContainerHeightAndCenter: function (e, iHeight, iWidth) {
                var eCont = $(this);
                var iId = aMethods.GetID.apply (eCont, [e]);
                var eImgCont = jQuery('#bx-gallery-img-cont-' + iId);
                var eImg = jQuery('#bx-gallery-img-' + iId).get(0);
                var eTitle = eImgCont.find('.bx-gallery-img-title');

                eTitle.hide();

                if (undefined == iHeight || !iHeight)
                    iHeight = eImg != undefined && eImg.complete > 0 ? parseInt(eImg.height) : 0;
                if (undefined == iWidth || !iWidth)
                    iWidth = eImg != undefined && eImg.complete > 0 ? parseInt(eImg.width) : 0;
                eCont.find('.bx-gallery-imgs').css('height',  iHeight ? iHeight + 'px' : 'auto');
                eImgCont.find('.bx-gallery-img').css('marginLeft', iWidth && eCont.innerWidth() ? (eCont.innerWidth() - iWidth) / 2 + 'px': 0);

                return iHeight > 0 ? true : false;
            },

            GetID: function (e) {
                var sId = e.attr('id');
                if (undefined == sId || !sId.length)
                    return false;
                var aMatches = sId.match(/(\d+)$/);
                if (null == aMatches)
                    return false;
                return parseInt(aMatches[1]);
            }
        };

		return this.each( function() {
			var eCont = $(this);
 
            eCont.find('.bx-gallery-icon-selector').css('left', (parseInt(eCont.find('.bx-gallery-icons').innerWidth()) / 2 - m_aSettings.icons_size / 2) + 'px');
           
            eCont.find('.bx-gallery-icon').each(function () {
 
                $(this).fadeTo(0, m_aSettings.icons_opacity);
 
                $(this).bind ('click', function () {
                    aMethods.Activate.apply (eCont, [$(this)]);
                });
            });

            eCont.find('.bx-gallery-img-cont').each(function () {
                
                $(this).bind ('click', function () {
                    var eNextImgCont = $(this).next('.bx-gallery-img-cont');
                    if (eNextImgCont.length)                        
                        aMethods.Activate.apply (eCont, [eNextImgCont]);
                    else
                        aMethods.Activate.apply (eCont, [eCont.find('.bx-gallery-img-cont:first-child')]);
                });
            });

            if (null == m_eCurrent)                                 
                aMethods.Activate.apply (eCont, [eCont.find('.bx-gallery-img-cont:first-child')]);

		} );
	};

})(jQuery);
