function rayVideoReady(bMode, sExtra) {
	var aExtra = sExtra.split('_');
	var iLastIndex = parseInt(aExtra[aExtra.length-1]);
	aExtra.pop();
	sExtra = aExtra.join('_');
	var oButton = iLastIndex != 0 ? $('#cmts-box-' + sExtra + ' #cmt' + iLastIndex + ' > .cmt-post-reply button[type=submit]') : $('button[type=submit]', '#cmts-box-' + sExtra);

	if (bMode)
		oButton.removeAttr('disabled');
	else
		oButton.attr('disabled', true);
}
function getRecorderObject(oForm) {
	return swfobject.getObjectById("ray_flash_video_comments_recorder_object");
}
