
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
					sCustomValues += '<span><span id="CustomFieldCaption1">' + custFieldName1 + ' ' + sUnitValue + '</span><span><input type="text" value="" name="CustomFieldValue1" /></span></span>';
				}
				if (custFieldName2 != '' && typeof(custFieldName2) != 'undefined') {
					sCustomValues += '<span><span id="CustomFieldCaption2">' + custFieldName2 + ' ' + sUnit2Value + '</span><span><input type="text" value="" name="CustomFieldValue2" /></span></span>';
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

function AdmCreateSubcategory(sSubmitUrl) {
    
    var iCateg = parseInt($(".form_advanced_table select[name='IDClassified']").val());
    var sTitle = $(".form_advanced_table input[name='NameSub']").val();
    var sDesc = $(".form_advanced_table input[name='Description']").val();
    var sAction = $("#create_sub_cats_form input[name='action']").val();
    var sID = $("#create_sub_cats_form input[name='id']").val();
    var sButton = $("#create_sub_cats_form input[name='add_button']").val();

    if (iCateg > 0) {
    	var oRequest = {};
    	if($("#create_sub_cats_form input[name='csrf_token']").length > 0)
    		oRequest['csrf_token'] = $("#create_sub_cats_form input[name='csrf_token']").val();
    	oRequest['IDClassified'] = iCateg;
    	oRequest['NameSub'] = sTitle;
    	oRequest['Description'] = sDesc;
    	oRequest['action'] = sAction;
    	oRequest['id'] = sID;
    	oRequest['add_button'] = sButton;
    	oRequest['mode'] = 'json';

        var sReceiver = $('#ads_add_sub_category .boxContent .bx-def-bc-margin');
    	$.post(sSubmitUrl, oRequest, function(oData) {
            sReceiver.html(oData);
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

