 
$.fn.bxdolcmtanim = function(action, effect, speed, h) 
{    
   return this.each(function() 
   {   		
   		var sFunc = '';
   		var sEval;

   		if (0 == speed)
   			effect = 'default';
   			
  		switch (action)
  		{
  			case 'show':
  				switch (effect)
  				{
  					case 'slide': sFunc = 'slideDown'; break;
  					case 'fade': sFunc = 'fadeIn'; break;
  					default: sFunc = 'show';
  				}  				
  				break;
  			case 'hide':
  				switch (effect)
  				{
  					case 'slide': sFunc = 'slideUp'; break;
  					case 'fade': sFunc = 'fadeOut'; break;
  					default: sFunc = 'hide';
  				}  				
  				break;  				
  			default:
  			case 'toggle':
  				switch (effect)
  				{
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


function BxDolCmts (options) {
	this._sObjName = undefined == options.sObjName ? 'oCmts' : options.sObjName; // javascript object name, to run current object instance from onTimer
	this._sSystem = options.sSystem; // current comment system
	this._sSystemTable = options.sSystemTable; // current comment system table name
	this._iAuthorId = options.iAuthorId; // this comment's author ID.
	this._iObjId = options.iObjId; // this object id comments
	this._sOrder = options.sOrder == 'asc' || options.sOrder == 'desc' ? options.sOrder : 'asc'; // comments' order	
    this._sActionsUrl = options.sBaseUrl + 'cmts.php'; // actions url address
    this._sVideoActionsUrl = options.sBaseUrl + 'flash/XML.php'; // video actions url address
    this._sDefaultErrMsg = undefined == options.sDefaultErrMsg ? _t('_Error occured') : options.sDefaultErrMsg; // default error message
    this._sConfirmMsg = undefined == options.sConfirmMsg ? _t('_Are_you_sure') : options.sConfirmMsg; // confirmation message

    this._isEditAllowed = parseInt(undefined == options.isEditAllowed ? 0 : options.isEditAllowed); // is edit allowed
    this._isRemoveAllowed = parseInt(undefined == options.isRemoveAllowed ? 0 : options.isRemoveAllowed); // is remove allowed
    this._iSecsToEdit = parseInt(undefined == options.iSecsToEdit ? 0 : options.iSecsToEdit); // number of seconds to allow edit comment

    this._bAutoHideRootPostForm = parseInt(undefined == options.iAutoHideRootPostForm ? 0 : options.iAutoHideRootPostForm); // auto hide root post form after posting a comment 

    this._sAnimationEffect = undefined == options.sAnimationEffect ? 'slide' : options.sAnimationEffect;
    this._iAnimationSpeed = undefined == options.sAnimationSpeed ? 'slow' : options.sAnimationSpeed;

    this._iGlobAllowHtml = undefined == options.iGlobAllowHtml ? '0' : options.iGlobAllowHtml;

    this._oCmtElements = undefined == options.oCmtElements ? {} : options.oCmtElements; // form elements
    this._oSavedTexts = {};

    // init post comment form (because browser remeber last inputs, we need to clear it)
    if ($('#cmts-box-' + this._sSystem + '-' + this._iObjId + ' .cmt-post-reply form').length) {
    	$('#cmts-box-' + this._sSystem + '-' + this._iObjId + ' .cmt-post-reply form')[0].reset();
    	$('#cmts-box-' + this._sSystem + '-' + this._iObjId + ' .cmt-post-reply form > [name=CmtParent]').val(0);    
    }
}

/*--- Browsing and Pagination ---*/
BxDolCmts.prototype.changeOrder = function(oSelectDom) {
    var oSelect = $(oSelectDom);
    
    this._sOrder = oSelect.val();
    this._getCmts(oSelect, 0, function(){});
};
BxDolCmts.prototype.expandAll = function(oCheckbox) {
    var $this = this;

    $.each($('#cmts-box-' + this._sSystem + '-' + this._iObjId + ' .cmts > .cmts > .cmt > .cmt-cont .cmt-replies-show'), function(){
        $(this).trigger('click');
    });
};
BxDolCmts.prototype.changePerPage = function(oDropDown) {
	var $this = this;

    var iPerPage = parseInt(oDropDown.value);
    this._getCmts(oDropDown, 0, function(){
    	$this._getPaginate(oDropDown,0, iPerPage);
    }, 0, iPerPage);
    
};
BxDolCmts.prototype.changePage = function(oElement, iStart, iPerPage) {
	var $this = this;

    this._getCmts(oElement, 0, function() {
    	$this._getPaginate(oElement, iStart, iPerPage);
    }, iStart, iPerPage);
};
BxDolCmts.prototype.showReplacement = function(iCmtId) {
    $('#cmt' + iCmtId + '-hidden').bxdolcmtanim('hide', this._sAnimationEffect, this._iAnimationSpeed, function(){
        $(this).next('#cmt' + iCmtId).bxdolcmtanim('show', this._sAnimationEffect, this._iAnimationSpeed);
    });
};

/*--- Main layout functionality ---*/
BxDolCmts.prototype.reloadComment = function(iCmtId) {
    var $this = this;
    var oData = this._getDefaultActions();
    oData['action'] = 'CmtGet';
    oData['Cmt'] = iCmtId;
    oData['Type'] = 'reload';
    	
    var eUl = $('#cmts-box-' + $this._sSystem + '-' + $this._iObjId + ' > div.cmts > ul').get();
    this._loading (eUl, true);        

    jQuery.post (
        this._sActionsUrl,        
        oData,
        function (s) {
        	$this._loading (eUl, false);
        	$('#cmts-box-' + $this._sSystem + '-' + $this._iObjId + ' li#cmt' + iCmtId).bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                $(this).replaceWith(s);
        	});
        	
        }
    );
    
};
BxDolCmts.prototype.toggleReply = function(oElement, iCmtParentId) {
	var $this = this;

    //--- Get form for posting comment in Root ---//
    if(iCmtParentId == 0) {
    	var sCmtsBox = 'cmts-box-' + this._sSystem + '-' + this._iObjId;

    	var oParent = $(oElement).parents('#' + sCmtsBox + ':first').find('.cmt-reply');
        if(oParent.children().length) {
        	oParent.bxdolcmtanim('hide', this._sAnimationEffect, this._iAnimationSpeed, function() {
        		$this.removeReply($(this));
        	});
        }
        else {
            var $this = this;
		    var oData = this._getDefaultActions();		    
            oData['action'] = 'FormGet';
            oData['CmtType'] = 'comment';
            oData['CmtParent'] = iCmtParentId;

            $this._loading (oElement, true);

            jQuery.post (
                this._sActionsUrl,
                oData,
                function (s) {
                	$this._loading(oElement, false);

                	//--- Remove all similar reply boxes ---//
                	$this.removeReply($('.' + sCmtsBox).find('.cmt-reply:not(:empty)').not(oParent));               	

                	oParent.append($(s).addClass('cmt-post-reply-expanded').css('display', 'none')).children('.cmt-post-reply').bxdolcmtanim('toggle', $this._sAnimationEffect, $this._iAnimationSpeed);

                	//--- Move page scroll to post box ---//
                	var iScrollPos = $(oElement).offset().top;
                	if($('.sys_sub_menu.sys_sm_fixed').is(':visible'))
                		iScrollPos -= ($('.sys_sub_menu.sys_sm_fixed').height() + 5);

                	$('html, body').animate({scrollTop:iScrollPos}, 500);
                }
            );
            
        }
    }

    //--- Get form for posting a reply ---//
	else {
		var oParent = $(oElement).parents('#cmt' + iCmtParentId + ':first');

		if (oParent.children('.cmt-post-reply').length)
			oParent.children('.cmt-post-reply').bxdolcmtanim('toggle', this._sAnimationEffect, this._iAnimationSpeed);
		else {
		    var $this = this;
		    var oData = this._getDefaultActions();		    
            oData['action'] = 'FormGet';
            oData['CmtType'] = 'reply';
            oData['CmtParent'] = iCmtParentId;
            
            $this._loading (oElement, true);
    
            jQuery.post (
                this._sActionsUrl,
                oData,
                function (s) {                	            
                	$this._loading(oElement, false);

                	oParent.children('.cmt-cont').after($(s).addClass('cmt-post-reply-expanded').css('display', 'none')).next('.cmt-post-reply').bxdolcmtanim('toggle', $this._sAnimationEffect, $this._iAnimationSpeed);
                }
            );					
		}
	}
};

BxDolCmts.prototype.removeReply = function(oReply) {
	if(this._iGlobAllowHtml == 1)
		this.editorDestroy(oReply.find("textarea.cmt-text-post[name='CmtText']"));
	
	$(oReply).empty().show();
};

BxDolCmts.prototype.toggleType = function(oLink) {
    var aSwitcher = {'cmt-post-reply-text': 'cmt-post-reply-video', 'cmt-post-reply-video': 'cmt-post-reply-text'};
    var sKey = $(oLink).hasClass('cmt-post-reply-text') ? 'cmt-post-reply-text' : 'cmt-post-reply-video';
    
    if($(oLink).parents('.cmt-balloon').find('div.' + sKey).is(':hidden')) {
        $(oLink).addClass('inactive').parents('.cmt-balloon').find('div.' + aSwitcher[sKey]).bxdolcmtanim('hide', this._sAnimationEffect, this._iAnimationSpeed, function(){
            var sType = sKey.substring(sKey.lastIndexOf('-') + 1, sKey.length);
            
            $(oLink).siblings(':visible.inactive').removeClass('inactive');            
            $(this).siblings('[name = CmtType]').val(sType);
            $(this).siblings('div.' + sKey).bxdolcmtanim('show', this._sAnimationEffect, this._iAnimationSpeed);
            if(sType == 'video')
                $(this).siblings('.cmt-post-reply-post').children(':submit').attr('disabled', 'disabled');
            else
                $(this).siblings('.cmt-post-reply-post').children(':submit').removeAttr('disabled');
        });    
    }
};

/**
 * show/hide comment replies
 */
BxDolCmts.prototype.toggleCmts = function(e, iCmtParentId) {
    //--- Load Root Comments ---//
    if(iCmtParentId == 0) {
    	var sCmtsBox = 'cmts-box-' + this._sSystem + '-' + this._iObjId + ':first';
    	$(e).parents('#' + sCmtsBox).find('.cmt-comments').toggle();

        if(!$(e).parents('#' + sCmtsBox).find('div.cmts > ul > li').length)
            this._getCmts(e, iCmtParentId, function() {});
        else
        	$(e).parents('#' + sCmtsBox).find('div.cmts').bxdolcmtanim('toggle', this._sAnimationEffect, this._iAnimationSpeed);
    }
    //--- Load Replies ---//
    else {
        var sId = '#cmt' + iCmtParentId;    
        if ($(sId + ' > ul').length) {
            if ($(sId + ' > ul').is(':visible'))        
            	$( sId + ' > ul').bxdolcmtanim('hide', this._sAnimationEffect, this._iAnimationSpeed, function() {
            		$(sId + '  > .cmt-cont .cmt-replies-hide').hide().siblings('.cmt-replies-show').show();
            	});        
            else        
                $(sId + ' > ul').bxdolcmtanim('show', this._sAnimationEffect, this._iAnimationSpeed, function() {
                	$(sId + '  > .cmt-cont .cmt-replies-show').hide().siblings('.cmt-replies-hide').show();
                });            
        }
        else
            this._getCmts(e, iCmtParentId, function (){ 
                $(sId + ' > .cmt-cont .cmt-replies-show').hide().siblings('.cmt-replies-hide').show(); 
            });
    }
};

BxDolCmts.prototype.cmtRate = function(oLink) {
	var $this = this;

	oLink = $(oLink);

	var iRate = 0;
	if(oLink.hasClass('cmt-pos'))
		iRate = 1;
	else if(oLink.hasClass('cmt-neg'))
		iRate = -1;

	if(iRate == 0 || oLink.parents('.cmt-rate:first').hasClass('cmt-rate-disabled'))
		return;

	var oCounter = oLink.parents('.cmt-buttons').siblings('.cmt-points').children('span');
	var oData = this._getDefaultActions();
    oData['action'] = 'CmtRate';
    oData['Cmt'] = parseInt(oLink.attr('id').substr(8));
    oData['Rate'] = iRate;

    this._loading(oCounter, true);

    jQuery.post (
		this._sActionsUrl,
		oData,
        function (s) {
        	$this._loading (oCounter, false);

			if(jQuery.trim(s).length) {
        		alert(s);
        		return;
			}

			if(iRate == 1)
				oCounter.html(parseInt(oCounter.html()) + iRate).parents('.cmt-rate:first').addClass('cmt-rate-disabled');
			else if(iRate == -1)
                $this.reloadComment(oData['Cmt']);
		}
	);
};

BxDolCmts.prototype.cmtRemove = function(e, iCmtId) {
	var $this = this;

	$(document).dolPopupConfirm({
		message: this._sConfirmMsg,
		onClickYes: function() {
		    var oData = $this._getDefaultActions();
		    oData['action'] = 'CmtRemove';
		    oData['Cmt'] = iCmtId;

		    $this._loading (e, true);

		    jQuery.post (
		    	$this._sActionsUrl,
		        oData,
		        function (s) {                	            
		        	$this._loading (e, false);

		        	if (jQuery.trim(s).length)
		        		alert(s);
		        	else
		        		$('#cmt' + iCmtId).bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function () { $(this).remove(); } );            
		        }
		    );
		}
	});
};

BxDolCmts.prototype.cmtEdit = function(e, iCmtId) {	
    var $this = this;
    var oBody = $('#cmt' + iCmtId + ' .cmt-body:first');
    if (oBody.find('textarea.cmt-text-edit').length) {
    	oBody.bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
    		if($this._iGlobAllowHtml == 1)
    			$this.editorDestroy($(this).find("textarea.cmt-text-edit[name='CmtText']"));

    		$(this).html($this._oSavedTexts[iCmtId]).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
    	});

    	return;
    }

    var oData = this._getDefaultActions();
    oData['action'] = 'CmtEdit';
    oData['Cmt'] = iCmtId;

    this._oSavedTexts[iCmtId] = oBody.html();

    jQuery.post (
        this._sActionsUrl,
        oData,
        function(s) {
        	if (s.substring(0,3) == 'err') {
        		alert (s.substring(3));
        		return;
        	}

        	oBody.bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
				var oMood = $(this).html(s).find("[name='CmtMood']");
				if(oMood.size())
					oMood.find("[value='" + $.trim($('#cmt' + iCmtId + ' .cmt-mood:first').html()) + "']").attr('checked', 'checked');

				$(this).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
    		});
        }
    );
};

BxDolCmts.prototype._getPaginate = function(e, iStart, iPerPage) {
    var $this = this;
    var oData = this._getDefaultActions();
    oData['action'] = 'PaginateGet';    
    if(iStart != undefined)
        oData['CmtStart'] = iStart;
    if(iPerPage != undefined)
        oData['CmtPerPage'] = iPerPage;
    
    this._loading (e, true);

    jQuery.post (
        this._sActionsUrl,        
        oData,
        function(s) {
            $('#cmts-box-' + $this._sSystem + '-' + $this._iObjId + ' .cmt-show-more').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                if(s.length > 0) {                    
                    $(this).find('.paginate').remove();
                    $(this).append(s);
                    $(this).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
                }
            });
            $this._loading (e, false);
        }
    );
};
// get comment replies via ajax request
BxDolCmts.prototype._getCmts = function (e, iCmtParentId, onLoad, iStart, iPerPage)
{
    var $this = this;
    var oData = this._getDefaultActions();
    oData['action'] = 'CmtsGet';
    oData['CmtParent'] = iCmtParentId;    
    oData['CmtOrder'] = this._sOrder;
    if(iStart)
        oData['CmtStart'] = iStart;
    if(iPerPage)
        oData['CmtPerPage'] = iPerPage;

    if(e)
        this._loading (e, true);

    jQuery.post (
        this._sActionsUrl,        
        oData,
        function(s) {
            if(iCmtParentId == 0) {
                $(e).parents('#cmts-box-' + $this._sSystem + '-' + $this._iObjId + ':first').find('.cmts > ul').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                    $(this).replaceWith(s).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                    	$(this).find('a.bx-link').dolEmbedly();
                    });
                });
            }
            else
                $(e).parents('#cmt' + iCmtParentId + ':first').append($(s).filter('.cmts').addClass('cmts-margin').hide()).children('.cmts').bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                	$(this).find('a.bx-link').dolEmbedly();
                });
            onLoad();
            $this._loading (e, false);
        }
    );
};

// get just posted 1 comment via ajax request
BxDolCmts.prototype._getCmt = function (oForm, iCmtParentId, iCmtId)
{
    var $this = this;
    var oData = this._getDefaultActions();
    oData['action'] = 'CmtGet';
    oData['Cmt'] = iCmtId;

    var oBox = $(oForm).parents('#cmts-box-' + $this._sSystem + '-' + $this._iObjId + ':first');

    var eUl = oBox.find('div.cmts > ul').get();
    this._loading (eUl, true);        

    jQuery.post (
        this._sActionsUrl,
        oData,
        function (s) {
        	$this._loading (eUl, false);

        	if (iCmtParentId == 0) {
        		var oProcessResults = function () {
	        	    var oParent = oBox.find('div.cmts > ul');
	        	    var iRepliesCount = parseInt(oBox.find('.cmt-comments:first .cmt-comments-count i').html());

	        		//--- Some number of comments already loaded ---//
	        		if(oParent.children('li.cmt:last').length) {
	        			oBox.find('.cmt-comments .cmt-comments-count i').html(iRepliesCount + 1);

	        			if(oParent.is(':visible'))
	        				oParent.append($(s).hide()).find('li.cmt:hidden').bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
		                    	$(this).find('a.bx-link').dolEmbedly();
		                    });
                        else
                        	oParent.append(s).parents('div.cmts:first').bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
		                    	$(this).find('a.bx-link').dolEmbedly();
		                    });
	        		}
	                //--- Some number of comments exists but NOT loaded ---//
	            	else if(iRepliesCount > 0) {
	            	    $this._getCmts(oForm, 0, function(){
	            	    	oBox.find('.cmt-comments').toggle().find('.cmt-comments-count i').html(iRepliesCount + 1);
	            	    });
	            	}
	            	//-- There is no comments at all ---//
	            	else {
	            	    oParent.find('.cmt-no').remove();
	            		oParent.html(s).find('a.bx-link').dolEmbedly();
	            		
	            		oBox.find('.cmt-comments').toggle().find('.cmt-comments-count i').html(iRepliesCount + 1);
	            	}
        		};

        		if($this._bAutoHideRootPostForm)
        			oBox.find('.cmt-reply').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
        				$this.removeReply($(this));

        				oProcessResults();
					});
        		else
        			oProcessResults();
            }
        	else {
        		oBox.find('#cmt' + iCmtParentId + ' > .cmt-post-reply').bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                	var iRepliesCount = parseInt(oBox.find('#cmt' + iCmtParentId + ' > .cmt-cont .cmt-replies-count').html());

                    //--- there was no comments and we added new
                    if(!oBox.find('#cmt' + iCmtParentId + ' > ul').length && !iRepliesCount)
                        $(s).wrap($('<ul />').addClass('cmts').addClass('cmts-margin')).parent().hide().appendTo(oBox.find('#cmt' + iCmtParentId)).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                        	$(this).find('a.bx-link').dolEmbedly();
                        });
                    //--- there is some number of comments but they are not loaded.
                    else if(!oBox.find('#cmt' + iCmtParentId + ' > ul').length && iRepliesCount > 0) {
                        $this._getCmts(oForm, iCmtParentId, function() {
                        	oBox.find('#cmt' + iCmtParentId + ' > .cmt-cont .cmt-replies').toggle().find('.cmt-replies-count').html(iRepliesCount + 1);
                        });
                    }
                    //--- there is some number of comments and they are loaded.
                    else if(oBox.find('#cmt' + iCmtParentId + ' > ul').length) {
                    	oBox.find('#cmt' + iCmtParentId + ' > .cmt-cont .cmt-replies .cmt-replies-count').html(iRepliesCount + 1);

                        if(oBox.find('#cmt' + iCmtParentId + ' > ul').is(':visible')) {
                        	oBox.find('#cmt' + iCmtParentId + ' > ul').append($(s).hide());
                        	oBox.find('#cmt' + iCmtParentId + ' > ul > :last').bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                            	$(this).find('a.bx-link').dolEmbedly();
                            });
                        }
                        else
                        	oBox.find('#cmt' + iCmtParentId + ' > ul').append(s).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
                            	$(this).find('a.bx-link').dolEmbedly();
                            });
                    }
                });
        	}

        	$this._runCountdown(iCmtId);
        }
    );
};

// submit comment and show it after posting
BxDolCmts.prototype.submitComment = function (f)
{    
	var eSubmit = $(f).children().find(':submit').get();
	var $this = this;
    var oData = this._getDefaultActions();

    $this._err(eSubmit, false); // hide any errors before submitting
    $this._disableButton(eSubmit, true);

    var isVideoComment = ($(f).children('[name = CmtType]').val() == 'video');

	if (!isVideoComment && !this._getCheckElements (f, oData)) {
		$this._disableButton(eSubmit, false);
		return; // get and check form elements    
	}

	// submit form

    if (isVideoComment) {

        var iParentId = parseInt($(f).children('[name = CmtParent]').val());
	    oData['module'] = 'video_comments';
        oData['action'] = 'post';
        oData['system'] = this._sSystem; 
        oData['parent'] = iParentId;
        oData['id'] = this._iObjId;
        oData['author'] = this._iAuthorId;
        oData['mood'] = $(f).find('.cmt-post-reply-mood :input:checked').val();

    } else {
        oData['action'] = 'CmtPost';
    }

	this._loading (eSubmit, true);
    jQuery.post (
        isVideoComment ? this._sVideoActionsUrl : this._sActionsUrl,
        oData,
        function (s) {                	
        	$this._loading (eSubmit, false);
        	$this._disableButton(eSubmit, false);

            var iNewCmtId = parseInt(s);            
            if (iNewCmtId > 0) {
                if (isVideoComment) {
                    $this._getCmt(f, iParentId, iNewCmtId);
	    			var o = getRecorderObject(f);
		    		if (o.removeRecord != undefined)
			    		o.removeRecord();                    
                } else {
                    $(f).find(':input:not(:button,:submit,[type = hidden],[type = radio],[type = checkbox])').val('');
                    $this._getCmt(f, oData['CmtParent'], iNewCmtId); // display just posted comment
                }                

            } 
            else if (!jQuery.trim(s).length) {
            	$this._err(eSubmit, true, $this._sDefaultErrMsg); // display error
            } 
            else {
                $this._err(eSubmit, true, s); // display error
            }
        }
    );
};

// Update comment OnEdit
BxDolCmts.prototype.updateComment = function (f, iCmtId)
{
	var eSubmit = $(f).find(':submit').get();
	var $this = this;
    var oData = this._getDefaultActions();
    
    $this._err(eSubmit, false); // hide any errors before submitting
    
	if (!this._getCheckElements (f, oData)) return; // get and check form elements
	
	this._oSavedTexts[iCmtId] = '';
	
	// submit form
	oData['action'] = 'CmtEditSubmit';	
	oData['Cmt'] = iCmtId;	
	this._loading (eSubmit, true);
    jQuery.post (
        this._sActionsUrl,        
        oData,
        function (oResponse) {                	
        	$this._loading (eSubmit, false);

            if(!jQuery.trim(oResponse.text).length) {
            	$this._err(eSubmit, true, jQuery.trim(oResponse.err).length ? oResponse.err : $this._sDefaultErrMsg); // display error
            	return;
            }

            var oBody = $('#cmt' + iCmtId + ' .cmt-body:first');
            oBody.bxdolcmtanim('hide', $this._sAnimationEffect, $this._iAnimationSpeed, function() {
    			if($this._iGlobAllowHtml == 1)
    				$this.editorDestroy(oBody.find("textarea.cmt-text-edit[name='CmtText']"));

    			oBody.find(".cmt-mood").html(' ' + oResponse.mood + ' ');
    			oBody.find(".cmt-mood-text").html(oResponse.mood_text);
    			$(this).html(oResponse.text).bxdolcmtanim('show', $this._sAnimationEffect, $this._iAnimationSpeed);
    		});
        },
        'json'
    );	
};

// toggle hidden comment
BxDolCmts.prototype._toggleHidden = function(e, iCmtId) {
	$('#cmt'+iCmtId+' > .cmt-cont').bxdolcmtanim('toggle', this._sAnimationEffect, this._iAnimationSpeed);
};

// check and get post new comment form elements
BxDolCmts.prototype._getCheckElements = function(f, oData) {
	var $this = this;
	var bSuccess = true;
	// check/get form elements
	jQuery.each( $(f).find(':input'), function () {        
		if (this.name.length && $this._oCmtElements[this.name]) {				
			var isValid = true;
			
			//--- Check form's data ---//
			if ($this._oCmtElements[this.name]['reg']) {
				try {
					if(this.type == 'textarea' && $this._iGlobAllowHtml == 1) {
                        var r = new RegExp($this._oCmtElements[this.name]['reg']);
                        isValid = r.test($this.editorGetText($(this)).replace(/(\n|\r)/g, ''));
					} else {                 
                        var r = new RegExp($this._oCmtElements[this.name]['reg']);
                        isValid = r.test(this.value.replace(/(\n|\r)/g, ''));
					}
				} catch (ex) {};
			}
			if (!isValid) {
				bSuccess = false;
				$this._err(this, true, $this._oCmtElements[this.name]['msg']);				
			}
			else {
				$this._err(this, false);
			}

			//--- Fill in data array ---//
			if(this.type == 'textarea' && $this._iGlobAllowHtml == 1) {
				oData[this.name] = $this.editorGetText($(this));
			} 
			else if(this.type == 'radio') {
                if(this.checked)
                    oData[this.name] = this.value;
			}
			else
				oData[this.name] = this.value;
		}
	});

	return bSuccess;
};

// run countdown timer for just posted comments
BxDolCmts.prototype._runCountdown = function(iCmtId) {
	if (this._isEditAllowed || this._isRemoveAllowed || 0 == this._iSecsToEdit) return;
	
	$('#cmt-jp-' + iCmtId + ' span > b').html(this._iSecsToEdit);
	
	window.setTimeout(this._sObjName + '.onCountdown(' + iCmtId + ',' + this._iSecsToEdit +');', 1000);
};

BxDolCmts.prototype.onCountdown = function(iCmtId, i) {
	var i = parseInt($('#cmt-jp-' + iCmtId + ' span > b').html());	
	if(0 == --i) {
		$('#cmt-jp-' + iCmtId).remove();
		return;
	}
	else {
		$('#cmt-jp-' + iCmtId + ' span > b').html(i);
		window.setTimeout(this._sObjName + '.onCountdown(' + iCmtId + ',' + i +');', 1000);
	}
};

BxDolCmts.prototype._disableButton = function(eButton, bDisable) {
	var oButton = $(eButton);
	if(bDisable)
		oButton.attr('disabled', 'disabled').addClass('bx-btn-disabled');
	else 
		oButton.removeClass('bx-btn-disabled').removeAttr('disabled');
};

BxDolCmts.prototype._loading = function(e, bShow) {
	$(e).parents('#cmts-box-' + this._sSystem + '-' + this._iObjId + ':first').bx_loading(bShow);
};

BxDolCmts.prototype._err = function(e, bShow, s) {
	if (bShow && !$(e).next('.cmt-err').length)
        $(e).after(' <b class="cmt-err">' + s + '</b>');
    else if (!bShow && $(e).next('.cmt-err').length)
        $(e).next('.cmt-err').remove();
};

BxDolCmts.prototype._getDefaultActions = function() {
    return { 
    	'sys': this._sSystem,
    	'id': this._iObjId
    };
};

BxDolCmts.prototype.editorGetText = function(oTextarea) {
	if(typeof tinyMCE == 'undefined' || !$(oTextarea).is('textarea'))
		return;

	return oTextarea.html();
};

BxDolCmts.prototype.editorDestroy = function(oTextarea) {
	if(typeof tinyMCE == 'undefined' || !$(oTextarea).is('textarea'))
		return;

	oTextarea.tinymce().remove();
};