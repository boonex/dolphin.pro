
	// contain all needed `ID` selectors ;
	var htmlSelectors = new Array();
	htmlSelectors[0] = 'compose_message';
	htmlSelectors[1] = 'compose_subject';
	htmlSelectors[2] = 'to_mail';
	htmlSelectors[3] = 'to_my_mail';
	htmlSelectors[4] = 'notify_mail';
	htmlSelectors[5] = 'reply_window';
	htmlSelectors[6] = 'reply_button';
	htmlSelectors[7] = 'compose_message_block';
	htmlSelectors[8] = 'message_recipient';
	htmlSelectors[9] = 'thumbnail_area';

	/**
	 * @description : constructor ;
	 */

	function MailBox()
	{
		// will need to define it ;
		this.sErrorMessage	 = '';
		this.sSurecaption	 = '';

		this.sResponceBlock  = '';
		this.sPageParameters = ''; 
		this.sPageReceiver	 = '';

		this.ExtendedParameters = '';

		this.popupWidth  = 650;
		this.popupHeight = 200;

		/**
		 * @description : function will hide all selected messages into trash ;
		 * @param		: sContainer (string) - contain name of section where jquery will find it ;
		 * @param		: sCallbackFunction (string) - callback function that will return answer from server side;
		 * @return		: Html presentation data ;
		 */
		this.hideDeletedMessages =  function(sContainer, sCallbackFunction )
		{
			var sMessagesId = '';
			var iValue		= '';
			var _this = this;

			var oCheckBoxes = $("." + sContainer + " input:checkbox:checked").each(function(){
				iValue = $(this).attr('value').replace(/[a-z]{1,}/i, '');
				if(iValue)
					sMessagesId += iValue + ',';
			});

			if(!sMessagesId) {
				alert(this.sErrorMessage);
				return;
			}

			var $this = this;
			$(document).dolPopupConfirm({
				message: this.sSurecaption, 
				onClickYes: function() {
					$('#' + $this.sResponceBlock).load(
						$this.sPageReceiver + '&messages=' + sMessagesId + '&callback_function=' + sCallbackFunction + $this.ExtendedParameters, 
						{
							'action' : 'hide_deleted'
						}
					);
				}
			});
		};

		/**
		 * @description : function will move all selected messages into trash ;
		 * @param		: sContainer (string) - contain name of section where jquery will find it ;
		 * @param		: sCallbackFunction (string) - callback function that will return answer from server side;
		 * @return		: Html presentation data ;
		 */
		this.deleteMessages =  function(sContainer, sCallbackFunction )
		{
			var sMessagesId = '';
			var iValue		= '';
			var _this = this;

			var oCheckBoxes = $("." + sContainer + " input:checkbox:checked").each(function(){
				iValue = $(this).attr('value').replace(/[a-z]{1,}/i, '');
				if ( iValue )
						sMessagesId += iValue + ',';
			});

			if(!sMessagesId) {
				alert(this.sErrorMessage);
				return;
			}

			var $this = this;
			$(document).dolPopupConfirm({
				message: this.sSurecaption, 
				onClickYes: function() {
					$('#' + $this.sResponceBlock).load(
						$this.sPageReceiver + '&messages=' + sMessagesId + '&callback_function=' + sCallbackFunction + $this.ExtendedParameters, 
						{
							'action' : 'delete'
						}
					);
				}
			});
		};

		/**
		 * @description : function will check or uncheck all checkboxes into form ;
		 * @param		: bChecked (boolean) - contain true if checkbox was checked;
		 * @param		: sContainer (string) - contain name of section where jquery will find it ;
		 */

		this.selectCheckBoxes = function( bChecked, sContainer )
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
		}

		/**
		 * @description : function will restore all deleted messages from trash ;
		 * @param		: sContainer (string) - contain name of section where jquery will find it ;
		 * @param		: sCallbackFunction (string) - callback function that will return answer from server side;
		 * @return		: Html presentation data ;
		 */

		this.restoreMessages = function(sContainer, sCallbackFunction)
		{
			var sMessagesId = '';
			var iValue		= '';
			var _this = this;

			var oCheckBoxes = $("." + sContainer + " input:checkbox:checked").each(function(){
				iValue = $(this).attr('value').replace(/[a-z]{1,}/i, '');
				if ( iValue )
						sMessagesId += iValue + ',';
			});

			if(!sMessagesId) {
				alert(this.sErrorMessage);
				return;
			}

			var $this = this;
			$(document).dolPopupConfirm({
				message: this.sSurecaption, 
				onClickYes: function() {
					$('#' + $this.sResponceBlock).load(
						$this.sPageReceiver + '&messages=' + sMessagesId + '&callback_function=' + sCallbackFunction + $this.ExtendedParameters,
						{
							'action' : 'restore' 
						}
					);
				}
			});
		};

		/**
		 * @description : function will send spam report on selected message's owner;
		 * @param		: sContainer (string) - contain name of section where jquery will find it;
		 * @param		: iMemberID (integer) - contain member ID (is the optional parameter);
		 * @return		: Html presentation data ;
		 */

		this.spamMessages = function(sContainer, iMemberID)
		{
			var sMembersId  = '';
			var iValue		= '';

            
			var oCheckBoxes = $("." + sContainer + " input:checkbox:checked").each(function(){
				if ($(this).attr('owner')) {
					iValue = $(this).attr('owner').replace(/[a-z]{1,}/i, '');
					if ( iValue )
							sMembersId += iValue + ',';
				}		
			});

			if ( typeof iMemberID != 'undefined')
				sMembersId = iMemberID + ',' ;

			if(!sMembersId) {
				alert(this.sErrorMessage);
				return;
			}

			var $this = this;
			$(document).dolPopupConfirm({
				message: this.sSurecaption, 
				onClickYes: function() {
					openWindowWithParams('list_pop.php?action=spam', 'spam_report', new Array('list_id'), new Array(sMembersId), 'width=' + $this.popupWidth + ',height=' + $this.popupHeight + ',menubar=no,status=no,resizeable=no,scrollbars=no,toolbar=no,location=no', 'post');
				}
			});
		};

		/**
		 * @description : function will get paginated page ;
		 * @param		: sPageUrl (string) Page's URL ;
		 * @return		: Html presentation data;
		 */

		this.getPaginatePage = function( sPageUrl )
		{
			var _this = this;
			sPageUrl = sPageUrl + '&ajax_mode=true&action=paginate';
			getHtmlData( this.sResponceBlock, sPageUrl,  function(){

			});
		};

		/**
		 * @description : function will send the message ;
		 * @param		: vRecipientId (variant) - recipient's ID or NickName; 
		 */

		this.sendMessage = function(vRecipientId)
		{
            var sErrorMessage = '';

			// create link on TinyMCE object ;
			var ed = tinyMCE.get(htmlSelectors[0]);

			// collect the `post` data ;
			var sComposeMessage	=  $.trim(ed.getContent());
            if(!sComposeMessage) {
                sErrorMessage = _t('_Mailbox description empty');
            }

			var sComposeSubject =  $.trim($('#' + htmlSelectors[1]).attr('value'));
            if(!sComposeSubject) {
                sErrorMessage = _t('_Mailbox title empty');
            }

			var sRecipientNick  =  $.trim($('#' + htmlSelectors[8]).attr('value'));

			// if vRecipientId 'undefined' than will try to find his nickname ;
			if (typeof vRecipientId == 'undefined' )
			{
				var oNickName = $('#' + htmlSelectors[8]);
				if (oNickName.length)
					vRecipientId = $.trim( oNickName.attr('value') );
			}

            if(!vRecipientId) {
               sErrorMessage = _t('_Mailbox recipient empty');
            }

			// collect the all needed parameters ;	
			var sPageUrl  = this.sPageReceiver + '&action=compose_mail&recipient_id=' 
											   + vRecipientId;

			// if data are correct ;
			if (!sErrorMessage)
			{
				// collect the additional parameters ;
				if ( $('#' + htmlSelectors[2]).is(':checked') ) {
					sPageUrl = sPageUrl + '&copy_message=true';
                }

				if ( $('#' + htmlSelectors[3]).is(':checked') ) {
					sPageUrl = sPageUrl + '&copy_message_to_me=true';
                }

				if ( $('#' + htmlSelectors[4] ).is(':checked') ) {
					sPageUrl = sPageUrl + '&notify=true';
                }

				// send data ;
				$.post(sPageUrl, { 'subject' : sComposeSubject, 'message' : sComposeMessage }, function(sReceivedData){

                    tinyMCE.execCommand('mceRemoveEditor', false, htmlSelectors[0]);

					$("#" + htmlSelectors[7]).html(sReceivedData);

					//set active the reply button
					var el = $('#' + htmlSelectors[6]);
					if (el.length)
					{
						el.attr('disabled', '');
					}
				})
			}
			else
			{
				alert(sErrorMessage);
			}
		}
	}
