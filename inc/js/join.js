function doShowHideSecondProfile( sShow, eForm ) {
    if( sShow == 'yes' ) {
        $( '.hidable').show();
        tinyMCE.execCommand('mceRemoveEditor', false, 'DescriptionMe[1]');
        tinyMCE.execCommand('mceAddEditor', false, 'DescriptionMe[1]');
    } else {
        $( '.hidable').hide();
    }
}

function validateJoinForm( eForm ) {
    if( !eForm )
        return false;
    
    hideJoinFormErrors( eForm );
    
    var eJoinBtn = $(eForm).find('input[name=do_submit]');
    var sJoinTitle = eJoinBtn.val();
    eJoinBtn.val(_t('_sys_txt_btn_loading')).prop('disabled', true);

    $(eForm).ajaxSubmit( {
        iframe: false, // force no iframe mode
        data: {join_page_validate: 1},
        beforeSerialize: function() {
            if (window.tinyMCE)
                tinyMCE.triggerSave();
            return true;
        },
        success: function(sResponce) {
            eJoinBtn.val(sJoinTitle).prop('disabled', false);

            try {
                var aErrors = eval(sResponce);
            } catch(e) {
                return false;
            }

            doShowJoinErrors( aErrors, eForm );
        }
    } );
    
    return false;
}

function hideJoinFormErrors( eForm ) {
    $( '.bx-form-element-error', eForm ).removeClass( 'bx-form-element-error' );
}

function doShowJoinErrors( aErrors, eForm ) {
    if( !aErrors || !eForm )
        return false;
    
    var bHaveErrors = false;
    
    for( var iInd = 0; iInd < aErrors.length; iInd ++ ) {
        var aErrorsInd = aErrors[iInd];
        $.each(aErrorsInd, function( sField, sError ) {
            bHaveErrors = true;

            doShowError( eForm, sField, iInd, sError );
        });
    }

    if( bHaveErrors )
        doShowError( eForm, 'do_submit', 0, _t('_Errors in join form') );
    else
        eForm.submit();
}

function doShowError( eForm, sField, iInd, sError ) {
    var $Field = $( "[name='" + sField + "']", eForm ); // single (system) field
    if( !$Field.length ) // couple field
        $Field = $( "[name='" + sField + '[' + iInd + ']' + "']", eForm );
    if( !$Field.length ) // couple multi-select
        $Field = $( "[name='" + sField + '[' + iInd + '][]' + "']", eForm );
    if( !$Field.length ) // couple range (two fields)
        $Field = $( "[name='" + sField + '[' + iInd + '][0]' + "'],[name='" + sField + '[' + iInd + '][1]' + "']", eForm );

    $Field.parents('.bx-form-element:first').addClass('bx-form-element-error').find('.bx-form-error > [float_info]').attr('float_info', sError);
}
