
function onChangeType(oLink) {
    var $this = this;

    var sType = $(oLink).attr('id').replace('adm-pvc-btn-', '');
    var sName = '#adm-pvc-cnt-' + sType;

    $(oLink).parent('.notActive').hide().siblings('.notActive:hidden').show().siblings('.active').hide().siblings('#' + $(oLink).attr('id') + '-act').show();
    $(sName).siblings('div:visible').bx_anim('hide', 'fade', 'slow', function(){
        $(sName).bx_anim('show', 'fade', 'slow');
    });
}
