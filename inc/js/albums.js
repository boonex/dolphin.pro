function check_album_name_for_fields(oSelect) {
	var oForm = $(oSelect).parents('form:first');
    var oTitle = oForm.find('.bx-form-element:has(input[name=title])');
    var oPrivacy = oForm.find('.bx-form-element:has(select[name=AllowAlbumView])');

    if ($(oSelect).val() != 0) {
        oTitle.hide();
        oPrivacy.hide();
    }
    else {
        oTitle.show();
        oPrivacy.show();
    }
}

function redirect_with_closing(sUrl, iTime) {
    window.setTimeout(function () {
        window.parent.opener.location = sUrl;
        window.parent.close(); 
    }, iTime * 1000);
}

function submit_quick_upload_form(sUrl, sFields) {
    sUrlReq = sUrl + 'upload_submit/?' + sFields;
    $.getJSON(sUrlReq, function(oJson) {
        if (oJson.status == 'OK')
            window.location.href = sUrl + 'albums/my/add_objects/' + oJson.album_uri + '/owner/' + oJson.owner_name;
        else
            alert(oJson.error_msg);
    });
    return false;
}