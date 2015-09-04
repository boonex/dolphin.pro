var BxMsgInvitationHeight = 300;
var BxMsgInviteInterval;
var sBxMsgTemplate = "";
var aBxMsgMessages = new Array();
var aBxMsgInvitations = new Object();

function BxMsgUpdate() {
    var oDate = new Date();
    $.get (
        sBxMsgUpdateUrl,
        {_t: oDate.getTime()},
        function(xXml) {
			var aMessages = xXml.getElementsByTagName("msg");
			for(var i=0; i<aMessages.length; i++)
				aBxMsgMessages.push({
					'sender': aMessages[i].getAttribute("sender"),
					'nick': aMessages[i].getAttribute("nick"),
					'profile': aMessages[i].getAttribute("profile"),
					'text': aMessages[i].firstChild.nodeValue
				});
			if(aBxMsgMessages.length) BxMsgShowInvitations();
        },
		'xml'
    );
	BxMsgInviteInterval = setTimeout('BxMsgUpdate();', BxMsgUpdateInterval);
}

function BxMsgShowInvitations() {
    if (sBxMsgTemplate.length) {
		for(var i=0; i<aBxMsgMessages.length; i++)
			BxMsgShowInvitation(aBxMsgMessages[i]);
		aBxMsgMessages.length = 0;
    } else {
        $.get(
            sBxMsgGetUrl + "get_invitation",
            {},
            function(data) {
                // trim needed for Safari. LOL
				sBxMsgTemplate = $.trim(data);
				BxMsgShowInvitations();
            },
            'html'
        );
    }
}

function BxMsgShowInvitation(oMessage) {
	if(aBxMsgInvitations[oMessage["sender"]]) return;
	
	var sContents = sBxMsgTemplate.split("__sender_id__").join(oMessage["sender"]);
	sContents = sContents.split("__sender_nickname__").join(oMessage["nick"]);
	sContents = sContents.split("__sender_profile__").join(oMessage["profile"]);
	sContents = sContents.split("__invitation_text__").join(oMessage["text"]);

	$.get(
		sBxMsgGetUrl + "get_thumbnail/" + oMessage["sender"],
		{},
		function(data) {
			// trim needed for Safari. LOL
			sContents = sContents.split("__sender_thumbnail__").join($.trim(data));
			aBxMsgInvitations[oMessage["sender"]] = $(sContents).prependTo('body');
			BxMsgRefreshPositions();
		},
		'html'
	);
}

function BxMsgRemoveInvitation(iSender) {
	aBxMsgInvitations[iSender].remove();
	aBxMsgInvitations[iSender] = null;
	BxMsgRefreshPositions();
}

function BxMsgRefreshPositions() {
	var iTopCount = 0;
	for(var i in aBxMsgInvitations)
	{
		if(aBxMsgInvitations[i] == null) continue;
		aBxMsgInvitations[i].attr('style', "top:" + (BxMsgTopMargin + iTopCount * BxMsgInvitationHeight) + "px");
		iTopCount++;
	}
}

function BxMsgPerformAction(iSender, sAction) {
	switch(sAction) {
		case "accept":
			openRayWidget("im", "user", sBxMsgMemberId, sBxMsgMemberPassword, iSender);
			break;
		case "spam":
		case "block":
            $.post(sBxMsgSiteUrl + 'list_pop.php?action=' + sAction, { ID: iSender } );
			break;
		case "decline":
		default:
			break;
	}
	BxMsgRemoveInvitation(iSender);
}