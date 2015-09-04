function PushEditAtBlogOverview(iBlogID, sDescription, iMemberID) {
	$('#edited_blog_div #EditBlogID').val(iBlogID);
	$('#edited_blog_div #Description').val(sDescription);
	$('#edited_blog_div #EOwnerID').val(iMemberID);
	$('#edited_blog_div').slideToggle('slow');
}

function BlogpostImageDelete(sUrl, sUnitID) {
	$.post(sUrl, function(data) {
		if (data==1) {
			$('#'+sUnitID).remove();
		} else {
			$('#'+sUnitID).html(data);
		}
	});
}