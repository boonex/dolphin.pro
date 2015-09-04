

function validateEditForm( eForm ) {
    if( !eForm )
        return false;
    
    hideEditFormErrors( eForm );
    
    $(eForm).ajaxSubmit({
        beforeSerialize: function() {
            if (window.tinyMCE)
                tinyMCE.triggerSave();
            return true;
        },
        success: function(sResponce) {
            //alert( sResponce );
            try {
                var aErrors = eval(sResponce);
            } catch(e) {
                return false;
            }
            
            doShowEditErrors( aErrors, eForm );
        }
    } );
    
    return false;
}

function hideEditFormErrors( eForm ) {
    $( '.error', eForm ).removeClass( 'error' );
}

function doShowEditErrors( aErrors, eForm ) {
    if( !aErrors || !eForm )
        return false;
    
    var bHaveErrors = false;
    
    for( var iInd = 0; iInd < aErrors.length; iInd ++ ) {
        var aErrorsInd = aErrors[iInd];
        for( var sField in aErrorsInd ) {
            var sError = aErrorsInd[ sField ];
            bHaveErrors = true;
            
            doShowError( eForm, sField, iInd, sError );
        }
    }

    if( bHaveErrors )
        doShowError( eForm, 'do_save', 0, _t('_Errors in join form') );
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
