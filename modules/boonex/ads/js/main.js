
function AddFilesFields(sDeleteCapt) {
	if ($("#browse_file_div").children('[class="file_field"]').length >= 5) {
		alert('5 files maximum');
		return;
	}

	var el = $('<div style="margin-top:10px;" class="file_field"><input name="userfile[]" type="file" style="" />&nbsp;&nbsp;&nbsp;<a href="#">' + sDeleteCapt + '</a></div>');
	$("#browse_file_div").append(el);

	el.children('a').click( function(){
	  $(this).parent().remove();
	  return false;
	} );
}

function AjaxyAskForSubcatsWithInfo(sSubSelectID, iCatID, sCustomElementID) {
	var oSubSelector = document.getElementById(sSubSelectID);
	var oCustomElement = $('#' + sCustomElementID);
	var oSubSelectorJQ = $('#' + sSubSelectID);

	if (oSubSelector) {
		for (var i = oSubSelector.options.length - 1; i >= 0; i--) {
			oSubSelector.options[i] = null;
		}
		optionObject = new Option('Loading...', 'null', false, false);
		oSubSelector.options[0] = optionObject;

		oSubSelectorJQ.show();
	}

	var bAdminMan = false;
	if (oAdminManager = document.getElementById('admin_managing')) {
		bAdminMan = true;
		$('#CustomName1').val('');
		$('#CustomName2').val('');
	}

	if (oCustomElement)
		oCustomElement.hide();

	var oRequest = {};
	oRequest['action'] = 'get_subcat_info';
	oRequest['cat_id'] = iCatID;

	$.post(sAdsSiteUrl + "ads_get_list.php", oRequest, function(oData) {        
        if (null == oData || 'object' != typeof(oData)) {
            oSubSelector.options[0] = null;
            return;
        }
		var custFieldName1 = oData.CustomFieldName1;
		var custFieldName2 = oData.CustomFieldName2;
		var sUnitValue = oData.Unit;
		var sUnit2Value = oData.Unit2;

		oSubSelector.options[0] = null;

		for (var optionIndex = 0; optionIndex <= oData.SubCats.length - 1; optionIndex++) {
			optionID = oData.SubCats[optionIndex]['id'];
			optionValue = oData.SubCats[optionIndex]['value'];
			optionObject = new Option(optionValue, optionID, false, false);
			oSubSelector.options[oSubSelector.length] = optionObject;
		}

		if (bAdminMan) {
			if (custFieldName1 != undefined) {
				$('#CustomName1').val(custFieldName1);
			}
			if (custFieldName2 != undefined) {
				$('#CustomName2').val(custFieldName2);
			}
			$('#unit').val(sUnitValue);
			$('#unit2').val(sUnit2Value);
		}

		if (bAdminMan==false) {
			if (sCustomElementID && sUnitValue != '' && typeof(sUnitValue) != 'undefined') {

				sCustomValues = '';

				if (custFieldName1 != '' && typeof(custFieldName1) != 'undefined') {
					sCustomValues += '<div class="ordered_block_select bx-def-margin-sec-right"><span id="CustomFieldCaption1">' + custFieldName1 + ' ' + sUnitValue + '</span><div class="input_wrapper input_wrapper_text bx-def-margin-sec-left clearfix"><input class="form_input_text bx-def-font-inputs" type="text" value="" name="CustomFieldValue1" /></div></div>';
				}
				if (custFieldName2 != '' && typeof(custFieldName2) != 'undefined') {
					sCustomValues += '<div class="ordered_block_select bx-def-margin-sec-right"><span id="CustomFieldCaption2">' + custFieldName2 + ' ' + sUnit2Value + '</span><div class="input_wrapper input_wrapper_text bx-def-margin-sec-left clearfix"><input class="form_input_text bx-def-font-inputs" type="text" value="" name="CustomFieldValue2" /></div></div>';
				}
		
				if (oCustomElement) {
					oCustomElement.html(sCustomValues);
					oCustomElement.show();
				}
			}
		}
	} , 'json');
}

function PerformCollectFilterCustomActions() {
	$('#CustomFieldCaption_1').val($('#CustomFieldCaption1').attr('action'));
	$('#CustomFieldCaption_2').val($('#CustomFieldCaption2').attr('action'));
}

function AdmCreateSubcategory(oElement, sSubmitUrl) {
    var oForm = $(oElement).parents('form:first');

    var sID = oForm.find("input[name='id']").val();
    var sAction = oForm.find("input[name='action']").val();
    var iCateg = parseInt(oForm.find("select[name='IDClassified']").val());
    var sTitle = oForm.find("input[name='NameSub']").val();
    var sDesc = oForm.find("input[name='Description']").val();
    var sButton = oForm.find("input[name='add_button']").val();

    if (iCateg > 0) {
    	var oRequest = {
    		'id': sID,
    		'IDClassified': iCateg,
    		'action': sAction,
    		'NameSub': sTitle,
    		'Description': sDesc,
    		'add_button': sButton,
    		'mode': 'json'
    	};

    	if(oForm.find("input[name='csrf_token']").length > 0)
    		oRequest['csrf_token'] = oForm.find("input[name='csrf_token']").val();

    	$.post(sSubmitUrl, oRequest, function(oData) {
            oForm.parents('.boxContent:first').find('.bx-def-bc-margin:first').html(oData);
    	}, 'json');
    }
}

function AdmAction2Category(sSubmitUrl, sSA, sID) {
    
    var iID = parseInt(sID);

    if (iID > 0) {
    	var oRequest = {};
    	oRequest['id'] = iID;
    	oRequest['action'] = 'category_manager';
    	oRequest['sa'] = sSA;
    	oRequest['mode'] = 'json';

        var sReceiver = $('#ads_category_manager .boxContent .bx-def-bc-margin');
    	$.post(sSubmitUrl, oRequest, function(oData) {
            sReceiver.html(oData);
    	}, 'json');
    }
}

