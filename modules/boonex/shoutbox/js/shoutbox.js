
    function BxShoutBox(o)
    {
        this.aOptions = $.extend({}, o),

        this.oMessages = new Object(),
        this.oMessages.empty_message = o.message_empty_message,

        // shoutbox object
        this.sObject = o.object,

        // shoutbox chat handler
        this.iHandler = o.handler,

        // contain page's URL for queries;
        this.sPageReceiver = o.module_path,

        // will check new messages;
        this.iUpdateTime = o.update_time,

        this.updateTimeNotifyHandler = '',

        // last message's Id;
        this.iLastMessageId = o.last_message_id,

        this.sMessagesContainer = 'bx_shoutbox_' + o.object + '_' + o.handler,

        this.sMessage = '',
        this.sWaitMessage = o.wait_cpt,

        this.oInputField = '',

        this._bUpdateIsFinished = true,
        this._stopUpdate = false,

        /**
         * Send block request
         * 
         * @param iMessage integer
         * @return void
         */
        this._sendBlockRequest = function(iMessage)
        {
            var _this = this;
            var _sRandom = Math.random();  

            $.get(this.sPageReceiver + 'block_message/' + _this.sObject + '/' + _this.iHandler + '/' + parseInt(iMessage) + '&_r=' + _sRandom, function(sResult){
        		if(sResult) {
        			alert(sResult);
        		}
        		else {
        			$('.' + _this.sMessagesContainer).find('.message_block').slideUp('slow', function($oContainer){
        				$(this).remove();

        				if(_this._stopUpdate == true && !$('.' + _this.sMessagesContainer).html() ) {
                			//reload shoutbox
                			_this.iLastMessageId = 0;
                			_this.getMessages();
                			_this._stopUpdate = false;
        				}
            		});
        		}
        	});
        },
        /**
         * Start sleep process for block function
         * 
         * @param iMessage integer
         * @return void
         */
        this._sleepBlockProcess = function(iMessage)
        {
        	var _this = this;

        	if(this._bUpdateIsFinished == true) {
        		//content was updated
        		this._sendBlockRequest(iMessage);
        	}
        	else {
        		setTimeout(function(){
        			_this._sleepBlockProcess(iMessage);
        		}, 1);
        	}
        },
        /**
         * Block message
         * 
         * @param iMessage integer
         * @return void
         */
        this.blockMessage = function(iMessage)
        {
            // stop the deserted notify procces;
            clearTimeout(this.updateTimeNotifyHandler);
        	this._stopUpdate = true;

        	//update not finished yet
        	if(this._bUpdateIsFinished == false) {
        		//start sleep procces
        		this._sleepBlockProcess(iMessage);
        	}
        	else {
        		//send message
        		this._sendBlockRequest(iMessage);
        	}
        },
        /**
         * Delete message
         * 
         * @param iMessageId integer
         * @retutn text
         */
        this.deleteMessage = function(iMessage, oLinkObject)
        {
            var _this = this;
            var _sRandom = Math.random();  

        	$.get(this.sPageReceiver + 'delete_message/' + _this.sObject + '/' + _this.iHandler + '/' + parseInt(iMessage) + '&_r=' + _sRandom, function(sResult){
        		if(sResult) {
        			alert(sResult);
        		}
        		else {
        			//hide message
        			var oMessage = $(oLinkObject).parents('.message_block:first');
        			oMessage.slideUp('slow', function(){
        				$(this).remove();
        			});

        		}
        	});
        },
        /**
         * Send message internal function only!
         */
        this._sendMessage = function()
        {
            var _sRandom = Math.random();  
            var self = this;

            $.post(this.sPageReceiver + 'write_message/' + self.sObject + '/' + self.iHandler + '/' + '&_r=' + _sRandom, { 'message': this.sMessage }, function(sData){
            	self._stopUpdate = false;

            	if(!sData && self._bUpdateIsFinished == true) {
                   self.getMessages();
                }
                else {
                    alert(sData);
                }                        
            });

            this.oInputField.value = '';
        },

        /**
         * Start sleep procces
         */
        this._startSleepProcces = function()
        {
        	var _this = this;

        	if(this._bUpdateIsFinished == true) {
        		//content was updated
        		this._sendMessage();
        	}
        	else {
        		setTimeout(function(){
        			_this._startSleepProcces();
        		}, 1);
        	}
        },

        /**
         * Function will send message from logged member ;
         *
         * @param  : e (system event) ;
         * @param  : evElement (object) (link on current field);
         */
        this.sendMessage = function(e, evElement)
        {
            var self = this;

            if(!e) {
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

            	//check sent message status
            	if(this._stopUpdate == true) {
            		alert(this.sWaitMessage);
            		return false;
            	}

                var sMessage = $.trim(evElement.value);
                // clear all tags;
                sMessage = sMessage.replace(/<\/?[^>]+>/gi, '');

                // send message
                if(sMessage) {
                	
                    // stop the deserted notify procces;
                    clearTimeout(this.updateTimeNotifyHandler);

                	//define message	
                	this.sMessage = sMessage;
                	this.oInputField = evElement;

                	this._stopUpdate = true;

                	//update not finished yet
                	if(this._bUpdateIsFinished == false) {
                		//start sleep procces
                		this._startSleepProcces();
                	}
                	else {
                		//send message
                		this._sendMessage();
                	}
                }
                else {
                    alert(this.oMessages.empty_message);
                    evElement.value = '';
                }

                return false;
            }

            return true;
        },

        /**
         * Function will return all latest messages list;
         *
         */
        this.getMessages = function()
        {
            var self = this;
            var _sRandom = Math.random();  
            this._bUpdateIsFinished = false;

            $.getJSON(this.sPageReceiver + 'get_messages/' + self.sObject + '/' + self.iHandler + '/' + this.iLastMessageId + '&_r=' + _sRandom, function(data){
                self.iLastMessageId = parseInt(data.last_message_id);
                self._bUpdateIsFinished = true;

                if(data.messages){

                    var $el = $('.' + self.sMessagesContainer);
                    // need for scroll content;
                    var iOldContentHeight = self.getContentHeight($el);
                    var iChatBoxHeight    =  $el.innerHeight();

                    $el.append(data.messages);

                    var iNewContentHeight = self.getContentHeight($el);

                    // scroll content;
                    iOldContentHeight -= (iChatBoxHeight + iNewContentHeight - iOldContentHeight);

                    if( iOldContentHeight <= $el.scrollTop() ) {
                        $el.scrollTop(iNewContentHeight);
                    }
                }

                // start update procces again;
                if(self._stopUpdate == false) {
	                self.updateTimeNotifyHandler = setTimeout(function(){
	                   self.getMessages(self.getMessages);
	                }, self.iUpdateTime);
                }
            });
        },

        /**
         * Function will get the chat box's sub content height;
         *
         * @param  : $oChatBox (object) - chat box;
         * @return : (integer) - sub content height;
         */
        this.getContentHeight = function($el)
        {
            var iHeight = 0;

            // define the chat boxe's childrens height;
            $el.children().each(function(){
                iHeight += $(this).outerHeight(true);
            });

            return iHeight;
        },

        /**
         * Function will scroll content to the end of container;
         */
        this.scrollContent = function($el)
        {
            // define container's height;
            if(typeof $el == 'undefined') {
                var $el = $('.' + this.sMessagesContainer);
            }

            $el.scrollTop( this.getContentHeight($el) );
        }
    }
