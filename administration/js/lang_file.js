/***************************************************************************
 *                            Dolphin Web Community Software
 *                              -------------------
 *     begin                : Mon Mar 23 2006
 *     copyright            : (C) 2007 BoonEx Group
 *     website              : http://www.boonex.com
 *
 *
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This is a free software; you can modify it under the terms of BoonEx
 *   Product License Agreement published on BoonEx site at http://www.boonex.com/downloads/license.pdf
 *   You may not however distribute it for free or/and a fee.
 *   This notice may not be removed from the source code. You may not also remove any other visible
 *   reference and links to BoonEx Group as provided in source code.
 *
 ***************************************************************************/

function onCreate() {
    $('#adm-langs-add-key').dolPopup(); 
}
function onResult(sType, oResult) {
    var sContentKey = '#adm-langs-' + sType + '-key-content';
    
    if(parseInt(oResult.code) == 0) {
        parent.document.forms['adm-langs-' + sType + '-key-form'].reset();

        $(sContentKey + ' > form').bx_anim('hide', 'fade', 'slow', function() {
            $(sContentKey).prepend(oResult.message);
            setTimeout("$('" + sContentKey + " > :first').bx_anim('hide', 'fade', 'slow', function(){$(this).remove();$('" + sContentKey + " > form').bx_anim('show', 'fade', 'slow', function(){$('#adm-langs-" + sType + "-key').dolPopupHide({});});})", 3000);
        });
    }
    else {
        $(sContentKey + ' > form').bx_anim('hide', 'fade', 'slow', function() {
            $(sContentKey).prepend(oResult.message);
            setTimeout("$('" + sContentKey + " > :first').bx_anim('hide', 'fade', 'slow', function(){$(this).remove();$('" + sContentKey + " > form').bx_anim('show', 'fade', 'slow');})", 3000);
        });
    }
}
function onEditKey(iId) {
    if ($('#adm-langs-edit-key').size())
        $('#adm-langs-edit-key').remove();
    $.post(
        sAdminUrl + 'lang_file.php',
        {action: 'get_edit_form_key', id: iId},
        function(oResult) {
            $('#adm-langs-holder').html(oResult.code).show();
            $('#adm-langs-edit-key').dolPopup();
        },
        'json'
    );
}
function onChangeType(oLink) {
    var $this = this;
    var sType = $(oLink).attr('id').replace('adm-langs-btn-', '');
    var sName = '#adm-langs-cnt-' + sType;
    
    $(oLink).parent('.notActive').hide().siblings('.notActive:hidden').show().siblings('.active').hide().siblings('#' + $(oLink).attr('id') + '-act').show();
    $(sName).siblings('div:visible').bx_anim('hide', 'fade', 'slow', function(){
        $(sName).bx_anim('show', 'fade', 'slow');
    });
}
function onEditLanguage(iId) {
    if ($('#adm-langs-wnd-edit').size()) {
        $('#adm-langs-wnd-edit').dolPopup();
        return;
    } 
    $.post(
        sAdminUrl + 'lang_file.php',
        {action: 'get_edit_form_language', id: iId},
        function(oResult) {
            $('#adm-langs-holder').html(oResult.code);
            $('#adm-langs-wnd-edit').dolPopup();
        },
        'json'
    );
}
