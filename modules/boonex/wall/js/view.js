function BxWallView(oOptions) {
    this._sActionsUrl = oOptions.sActionUrl;
    this._sObjName = oOptions.sObjName == undefined ? 'oWallView' : oOptions.sObjName;
    this._iOwnerId = oOptions.iOwnerId == undefined ? 0 : oOptions.iOwnerId;
    this._iGlobAllowHtml = 0;
    this._sAnimationEffect = oOptions.sAnimationEffect == undefined ? 'slide' : oOptions.sAnimationEffect;
    this._iAnimationSpeed = oOptions.iAnimationSpeed == undefined ? 'slow' : oOptions.iAnimationSpeed;
    this._oRequestParams = oOptions.oRequestParams == undefined ? {} : oOptions.oRequestParams;

    this._fOutsideOffset = 0.8;

    var $this = this;
    $(document).ready(function($){
    	$this.initEvents($('.wall-event'));
    });
}

BxWallView.prototype.initEvents = function(oEvents) {
	var $this = this;

	//hide timeline Events which are outside the viewport
	$this.hideEvents(oEvents, this._fOutsideOffset);

	//on scolling, show/animate timeline Events when enter the viewport
	$(window).on('scroll', function() {
		if(!window.requestAnimationFrame) 
			setTimeout(function() {
				$this.showEvents(oEvents, $this._fOutsideOffset);
			}, 100);
		else
			window.requestAnimationFrame(function() {
				$this.showEvents(oEvents, $this._fOutsideOffset);
			});
	});
};

BxWallView.prototype.hideEvents = function(oEvents, fOffset) {
	oEvents.each(function() {
		( $(this).offset().top > $(window).scrollTop() + $(window).height() * fOffset ) && $(this).find('.thumbnail_block, .wall-event-cnt').addClass('is-hidden');
	});
};

BxWallView.prototype.showEvents = function(oEvents, fOffset) {
	oEvents.each(function() {
		( $(this).offset().top <= $(window).scrollTop() + $(window).height() * fOffset && $(this).find('.thumbnail_block').hasClass('is-hidden') ) && $(this).find('.thumbnail_block, .wall-event-cnt').removeClass('is-hidden').addClass('bounce-in');
	});
};

BxWallView.prototype.deletePost = function(iId) {
	var $this = this;
    var oData = this._getDefaultData();
    var oLoading = $('#bx-wall-view-loading');

    oData['WallEventId'] = iId;

	$(document).dolPopupConfirm({
		message: _t('_Are_you_sure'), 
		onClickYes: function() {
		    if(oLoading)
		    	oLoading.bx_loading();

		    $.post(
		        $this._sActionsUrl + 'delete/',
		        oData,
		        function(oData) {
		        	if(oLoading)
		        		oLoading.bx_loading();

		            if(oData.code == 0)
		                $('#wall-event-' + oData.id + ', #wall-event-' + oData.id + ' + .wall-divider-nerrow').bxwallanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
		                    $(this).remove();
		                    
		                    if($('#bxwall .wall-view :last').is('.wall-divider-nerrow'))
		                    	$('#bxwall .wall-view :last').remove();

		                    if($('#bxwall .wall-view .wall-events .wall-event').length == 0) {
		                    	$('.wall-view .wall-events div.wall-divider-today').hide();
		                    	$('.wall-view .wall-events div.wall-load-more').hide();
		                    	$('.wall-view .wall-events div.wall-empty').show();
		                    }
		                });                        
		        },
		        'json'
		    );
		}
	});
};

BxWallView.prototype.changeFilter = function(oLink) {
    var sId = $(oLink).attr('id');

    this._oRequestParams.WallStart = 0;
    this._oRequestParams.WallPerPage = null;
    this._oRequestParams.WallFilter = sId.substr(sId.lastIndexOf('-') + 1, sId.length);
    this._oRequestParams.WallTimeline = null;

    //--- Change Control ---//
    $(oLink).parent().siblings('.active:visible').hide().siblings('.notActive:hidden').show().siblings('#' + sId + '-pas:visible').hide().siblings('#' + sId + '-act:hidden').show();

    this.getTimeline();
    this.getPosts('filter');

    //--- Is used with common Pagination
    //this.getPaginate();
};

BxWallView.prototype.changeTimeline = function(oEvent) {
	this._oRequestParams.WallStart = 0;
    this._oRequestParams.WallPerPage = null;
    this._oRequestParams.WallTimeline = $(oEvent.target).siblings("[name='timeline']").val();

	this.getPosts('timeline');

	//--- Is used with common Pagination
    //this.getPaginate();
};

BxWallView.prototype.changePage = function(iStart, iPerPage) {
	this._oRequestParams.WallStart = iStart;
    this._oRequestParams.WallPerPage = iPerPage;

    this.getPosts('page');

    //--- Is used with common Pagination
    //this.getPaginate();
};

BxWallView.prototype.getPosts = function(sAction) {
    var $this = this;

	switch(sAction) {
		case 'page':
			oLoading = $('#wall-load-more .bx-btn');
			oLoading.bx_btn_loading();
			break;

		default:
			oLoading = $('#bx-wall-view-loading');
			oLoading.bx_loading();
			break;
	}

    jQuery.post(
        this._sActionsUrl + 'get_posts/',
        this._getDefaultData(),
        function(sResult) {
            if(sAction == 'page') {
            	if(oLoading)
            		oLoading.bx_btn_loading();

	            $('#bxwall .wall-view .wall-load-more').bxwallanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
	                var oEvents = $(this).after(sResult).nextAll('.wall-event');
	                oEvents.find('a.bx-link').dolEmbedly();
	                
	                $this.initEvents(oEvents);

	                $(this).remove();
	            });
            }
            else {
            	if(oLoading)
            		oLoading.bx_loading();

            	$('#bxwall .wall-view .wall-events').bxwallanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
	                $(this).html(sResult).bxwallanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
	                	$(this).find('a.bx-link').dolEmbedly();
	                });
	            });
            }
        }
    );
};

BxWallView.prototype.getTimeline = function() {
    var $this = this;

    var oLoading = $('#bx-wall-view-loading');
    if(oLoading)
    	oLoading.bx_loading();

    jQuery.post (
        this._sActionsUrl + 'get_timeline/',
        this._getDefaultData(),
        function(sResult) {                                    
        	if(oLoading)
        		oLoading.bx_loading();

            $('#bxwall .wall-view .wall-timeline').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
            	$(this).html(sResult);
            	$(document).addWebForms();
            	$(this).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
            });            
        }
    );
};

BxWallView.prototype.getPaginate = function() {
    var $this = this;

    var oLoading = $('#bx-wall-view-loading');
    if(oLoading)
    	oLoading.bx_loading();

    jQuery.post (
        this._sActionsUrl + 'get_paginate/',
        this._getDefaultData(),
        function(sResult) {                                    
        	if(oLoading)
        		oLoading.bx_loading();

            $('#bxwall > .paginate').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                if(sResult.length > 0) {
                    $(this).replaceWith(sResult);
                    $(this).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
                }
            });            
        }
    );
};

BxWallView.prototype.showMoreContent = function(oLink) {
	$(oLink).parent('span').next('span').show().prev('span').remove();
	this.reloadMasonry();
};

BxWallView.prototype._getDefaultData = function () {
	var oDate = new Date();
	this._oRequestParams._t = oDate.getTime();
    return this._oRequestParams;
};

BxWallView.prototype._err = function (oElement, bShow, sMessage) {    
	if (bShow && !$(oElement).next('.wall-post-err').length)
        $(oElement).after(' <b class="wall-post-err">' + sMessage + '</b>');
    else if (!bShow && $(oElement).next('.wall-post-err').length)
        $(oElement).next('.wall-post-err').remove();    
};