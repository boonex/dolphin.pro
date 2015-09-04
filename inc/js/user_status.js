function BxUserStatus()
{
    // contain path to the site home URL ;
    this.HomeUrl;
    
    // contain true if current profile page ;
    this.bIsProfilePage;

    this.sCurrentStatusElementClass = 'sys-online-offline-changer';
    this.sCurrentStatusElementSelector = '.' + this.sCurrentStatusElementClass;

    this.userStatusInit = function(sHomeUrl, bProfilepage) 
    {
        this.HomeUrl = sHomeUrl;
        this.bIsProfilePage = (bProfilepage) ? true : false;
    };

    /**
     * Function will set member's status 
     *      (are possible values : online, offline, away, busy) ;
     *
     * @param : sStatus   (string)  - member's current status;
     * @param : oObject (object) - current html object ;
     */
    this.setUserStatus = function( sStatus, oObject, isForcePageReload ) 
    {
    	var self = this;
        var sStatus = encodeURIComponent(sStatus);
        var _sRandom = Math.random();

    	$.post(this.HomeUrl + 'list_pop.php?action=change_status' + '&_r=' + _sRandom, { 'status' : sStatus }, function(sData) {
            if ( self.bIsProfilePage || ('undefined' != typeof(isForcePageReload) && isForcePageReload)) {
                document.location.reload();
            } else {
                $(self.sCurrentStatusElementSelector).attr( { 'alt' : sStatus} ).removeClass().addClass(self.sCurrentStatusElementClass + ' sys-icon ' + sData);
                self.closeSubMenu(oObject);
            }
    	});
    };

    /**
     * Function will send new member's status message;
     *
     * @param : e (object) - keyboard event;
     * @param : oObject (object) - current html object (that contain new status message);
     */
    this.sendStatusMessage = function(e, oObject)
    {
        var self = this;

        if( !e ) {
            if( window.event ) { //Internet Explorer
              e = window.event;
            } 
            else { //total failure, we have no way of referencing the event
              return false;
            }
        }

        var n = e.keyCode ? e.keyCode : e.charCode; 

        if (n == 13) { //Enter
			if (e.preventDefault)  
				e.preventDefault();  
			else 
				e.returnValue = false;

            self.PerformSendingStatusMess(oObject);
            return false;
        }

        return true;
    };

    this.PerformSendingStatusMess = function(oObject) {
        var self = this;
        var _sRandom = Math.random();

        $.post( this.HomeUrl + 'list_pop.php?action=change_status_message' + '&_r=' + _sRandom, { status_message : oObject.value }, 
            function(sData) {
                $('#StatusMessage > .sys_sm_text').html(oObject.value).parent().show();
                $('#inloadedStatusMess').html('');
                
                //window.location.href = window.location.href; // Ticket #1359
                if ( self.bIsProfilePage ) {
                    document.location.reload();
                }
                else {
                    // try to close self window;
                    self.closeSubMenu( $(oObject).parents('ul:first') );
                }
            }
        );
    };

    /**
     * Function  will close the current opened member's sub menu;
     *
     * @param : oObject (object) - current object;
     */
    this.closeSubMenu = function (oObject)
    {
        if ( typeof membermenu != 'undefined' ) {
            membermenu.close_popup( $(oObject).attr('id') );
        }
    };
}

