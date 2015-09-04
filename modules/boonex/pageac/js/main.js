function BxPageACMain(oOptions) {
	this._sBaseURL = oOptions.sBaseURL;
}
BxPageACMain.prototype.loadBlock = function(sBlockID, sUrl) {
	var oDate = new Date();
	$.post(
        sUrl,
        {_t:oDate.getTime()},
        function(sData) {
        	$('#'+sBlockID).html(sData);
        },
        'html'
    );
}
BxPageACMain.prototype.addNewRule = function (oForm) {
	var $this = this;

	$(oForm).ajaxSubmit(function(sResponce) {
        try {
            if (sResponce.length) {
            	alert(sResponce)
            } else {
            	$this.loadBlock('rules_list', $this._sBaseURL + 'action_get_rules_list');
            }
        } catch(e) {}
    });
}
BxPageACMain.prototype.saveRule = function (id) {
	var $this = this;
	var mlvs = '';
	var rtext = '';
	$("input[name='memlevel["+id+"][]']:checked").each(function() {
		mlvs += $(this).val() + ',';
	});
	rtext = $('#rule_'+id).val();

	$.post($this._sBaseURL + 'action_save_rule', {rule_id:id, rule_text:rtext, rule_mlvs:mlvs}, function(sResponce) {
		$('#rules_list').html(sResponce);
	}, 'html');

}
BxPageACMain.prototype.deleteRule = function (id) {
	var $this = this;

	$.post($this._sBaseURL + 'action_delete_rule', {rule_id:id}, function(sResponce) {
		$('#rules_list').html(sResponce);
	}, 'html');
}

BxPageACMain.prototype.showMenuEditForm = function (id) {
	var $this = this;
	$this.showPopupEditForm(sParserURL+'edit/'+id)
}

BxPageACMain.prototype.showPopupEditForm = function (sUrl) {
	var $this = this;

	if (!$('#pageac_popup_edit_form').length) {
        $('<div id="pageac_popup_edit_form" style="display:none;"></div>').prependTo('body');
    }

    var oPopupOptions = {};

	var oDate = new Date();

    $.post(
        sUrl,
        {_t:oDate.getTime()},
        function(sData) {
        	$('#pageac_popup_edit_form').html(sData);
        	$('#pageac_popup_edit_form').dolPopup(oPopupOptions);
        },
        'html'
    );
}
BxPageACMain.prototype.saveItem = function(oForm) {
	$('#formItemEditLoading').bx_loading();

	var sQueryString = $(oForm).formSerialize();

	$.post($(oForm).attr('action'), sQueryString, function(oData){
        $('#formItemEditLoading').bx_loading();

        $('#item_edit').bx_message_box(oData.message, oData.timer, function(){
			$('#pageac_popup_edit_form').dolPopupHide();
        })
    }, 'json');
}
BxPageACMain.prototype.refreshPagesBuilder = function(sURL, sPage) {
	var $this = this;
	$('#page_builder_zone').html('<center><img alt="Loading..." src="' + aDolImages['loading'] + '" /></center>');
	$this.loadBlock('page_builder_zone', sURL + sPage);
}
