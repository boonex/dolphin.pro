
	// contain all needed `ID` selectors ;
	var htmlSelectors = new Array();
    htmlSelectors[0] = 'rows_content';

	/**
	 * @description : constructor ;
	 */

	function CommunicatorPage()
	{
		// will need to define this ;
		this.sErrorMessage	    = '';
		this.sSureCaption	    = '';

		// URL for page receiver ;
        this.sPageReceiver	    = '';

        // current communicator mode ;
        this.sCommunicatorMode  = '';

        // ID of block that will be draw ajax's return result ;
        this.sResponceBlock  = '';
	}

    /**
     * Function will return the communicator page with person mode consideration ;
     *
     * @param  : sPersonMode (string) - person's mode (from, to);
     * @return : (text) Html presentation data ;
     */
    CommunicatorPage.prototype.getTypifiedPage = function( sPersonMode )
    {
        $('#'+htmlSelectors[0]).parent().load(this.sPageReceiver, {'action' : 'get_page'
        	, 'person_switcher' : sPersonMode
        	, 'communicator_mode' : this.sCommunicatorMode});
    }

    /**
     * Function will return the communicator page, consideration the page's parameters ;
     *
     * @param  : sPageUrl (string) - page's URL;
     * @return : (text) Html presentation data ;
     */
    CommunicatorPage.prototype.getPaginatePage = function(sPageUrl)
    {
       $('#'+htmlSelectors[0]).parent().load(sPageUrl); 
    }

    /**
     * Function will return the communicator page, consideration the per page parameter ;
     *
     * @param  : iPerPage (integer) - number elements for per page;
     * @param  : sPageUrl (string)  - page's URL;
     * @return : (text) Html presentation data ;
     */
    CommunicatorPage.prototype.getPage = function(iPerPage, sPageUrl)
    {
        $('#'+htmlSelectors[0]).parent().load(sPageUrl, {'per_page':iPerPage}); 
    }

    /**
     * Function will return page consideration the page sort parameter ;
     *
     * @param  : sPageUrl (string) - page's URL;
     * @param  : sSortType (string) - sort parameter ;
     * @return : (text) Html presentation data ;
     */
    CommunicatorPage.prototype.getSortedPage = function(sPageUrl, sSortType)
    {
        $('#'+htmlSelectors[0]).parent().load(sPageUrl, { 'sorting' : sSortType});
    }
    
    /**
     * Function will check or uncheck all checkboxes into wrapper ;
     *
     * @param		: bChecked (boolean) - contain true if checkbox was checked;
     * @param		: sContainer (string) - contain name of section where jquery will find it ;
     */
    CommunicatorPage.prototype.selectCheckBoxes = function( bChecked, sContainer )
    {
        var oCheckBoxes = $("." + sContainer + " input:checkbox:enabled");

        if ( bChecked )
        {
            oCheckBoxes.attr('checked', 'checked');
        }
        else
        {
            oCheckBoxes.removeAttr('checked');	
        }
    };

    /**
     * Function will send action  ;
     *
     * @param		: oButton (object)  - contain an object of a button(link) which called the action ;
     * @param		: sContainer (string)  - contain name of section where jquery will find it ;
     * @param		: sActionName (string) - contain name of needed action  ;
     * @param		: sCallbackFunction (string) - callback function that will return answer from server side;
     * @return      : (text) - html data from server side ;
     */
    CommunicatorPage.prototype.sendAction = function(oButton, sContainer, sActionName, sCallbackFunction, sItemsIds, bShowConfirm) {
        var iValue = '';
        if(bShowConfirm == undefined)
        	bShowConfirm = true;

        if(!sItemsIds) {
        	sItemsIds = '';
	        var oCheckBoxes = $("." + sContainer + " input:checkbox:checked").each(function(){
	            iValue = $(this).attr('value').replace(/[a-z]{1,}/i, '');
	            if ( iValue )
	                    sItemsIds += iValue + ',';
	        });
        }

        if(!sItemsIds) {
        	alert(this.sErrorMessage);
        	return;
        }

        var $this = this;
        var fPerform = function(oButton) {
        	$this._enableButton(oButton, false);

        	//--- send data to the web server
            $('.' + sContainer).parents('.boxContent:first').load($this.sPageReceiver, { 
            	'action': sActionName, 
            	'rows': sItemsIds, 
            	'callback_function': sCallbackFunction, 
            	'communicator_mode': $this.sCommunicatorMode, 
            	'person_switcher': $this.sPersonMode
            });
        };

        if(bShowConfirm) {
        	$(document).dolPopupConfirm({
        		message: this.sSureCaption, 
        		onClickYes: function() {
        			fPerform(oButton);
        		}
        	});

        	return;
        }

        fPerform(oButton);
    };

    CommunicatorPage.prototype._enableButton = function(oButton, bEnable) {
    	oButton = $(oButton);

    	if(bEnable) {
    		oButton.removeClass('bx-btn-disabled');
	        if(oButton.is(':button'))
	        	oButton.removeAttr('disabled');
    	}
    	else {
    		oButton.addClass('bx-btn-disabled');
	        if(oButton.is(':button'))
	        	oButton.attr('disabled', 'disabled');
    	}
    };