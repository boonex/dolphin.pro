function validateLoginForm(eForm) {
	if (! eForm)
		return false;
    
    $(eForm).ajaxSubmit({
        success: function(sResponce) {
            if(sResponce == 'OK')
                eForm.submit();
            else
                alert(_t('_PROFILE_ERR'));
        }
    });
}