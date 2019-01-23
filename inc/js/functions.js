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

function ps_page_toggle(sPageName, iProfileId, iBlockId) {
	if($('body').find('div#dbPrivacyMenu' + iBlockId).length > 0){
		$('body').find('div#dbPrivacyMenu' + iBlockId).dolPopup();
		return;
	}
		
	$.post(
        site_url + 'member_privacy.php',
        {
            ps_action: 'get_chooser',
            ps_page_name: sPageName,
            ps_profile_id: iProfileId,
            ps_block_id: iBlockId
        },
        function(oData) {
            if(parseInt(oData.code) == 0) {
            	$('body').append($(oData.data).addClass('dbPrivacyMenu'));
            	$('body').find('div#dbPrivacyMenu' + iBlockId).dolPopup();
            }
        },
        'json'
    );
}
function ps_page_select(oLink, iBlockId, iGroupId) {
    $.post(
        site_url + 'member_privacy.php',
        {
            ps_action: 'view_block',
            ps_block_id: iBlockId,
            ps_group_id: iGroupId
        },
        function(oData) {
            if(parseInt(oData.code) == 0) {
            	$(oLink).parents('.dbPrivacyMenu').dolPopupHide().find('.dbPrivacyGroupActive').removeClass('dbPrivacyGroupActive').addClass('dbPrivacyGroup');        
                $(oLink).removeClass('dbPrivacyGroup').addClass('dbPrivacyGroupActive');
     
                $('#dbPrivacy' + iBlockId + ' a:first').attr('title', oData.group);
            }
        },
        'json'
    );
}
function ps_showDialog(sType, iGroupId, oLink) {
    var oDiv = $('#ps-' + sType + '-members');
    
    $("#ps-" + sType + "-member-form :hidden[name='ps-" + sType + "-member-group']").val(iGroupId);
    oDiv.dolPopup();
    
    if(sType == 'del') {
        $('#ps-' + sType + '-members-loading').bx_loading();
                
        $.post(
            sPSSiteUrl + 'member_privacy.php',
            {
                ps_action: 'members',
                ps_value: iGroupId
            },
            function(sData) {
                $('#ps-' + sType + '-members-loading').bx_loading();
                oDiv.find('.ps-search-results').html(sData);
            },
            'html'
        );
    }
}
function ps_ad_search() {
    var sSearchValue = $('#ps-search-member-form').find(':text:first').val();

    if(sSearchValue) {
        $('#ps-add-members-loading').bx_loading();
            
        $.post(
            sPSSiteUrl + 'member_privacy.php',
            {
                ps_action: 'search',
                ps_value: sSearchValue
            },
            function(sData) {
                $('#ps-add-members-loading').bx_loading();
                $('#ps-add-members .ps-search-results').html(sData);
            },
            'html'
        );
    }
}

/**
 * Checks/unchecks all tables
 *
 * @param   string   the form name
 * @param   boolean  whether to check or to uncheck the element
 *
 * @return  boolean  always true
 */
function setCheckboxes(the_form, do_check)
{
	var elts  = document.forms[the_form].getElementsByTagName('input');
    var elts_cnt  = elts.length;

    for ( i = 0; i < elts_cnt; i++)
    {
        elts[i].checked = do_check;
		if ( elts[i].type == "submit" )
			elts[i].disabled = !do_check;
    }
}

function UpdateSubmit(the_form) {
	var elts  = document.forms[the_form].getElementsByTagName('input');
	var elts_cnt  = elts.length;
	var bChecked = false;

	for ( i = 0; i < elts_cnt; i++) {
		if (elts[i].type == "checkbox" && elts[i].checked == true) {
			bChecked = true;
		}
		if ( elts[i].type == "submit" ) {
			elts[i].disabled = true;
		}
	}
	for ( i = 0; i < elts_cnt; i++) {
		if ( elts[i].type == "submit" ) {
			if (bChecked == true) {
				elts[i].disabled = false;
			} else {
				elts[i].disabled = true;
			}
		}
	}
}

/**
 * Allow open window with extra params
 *
 * @param sWindowUrl string
 * @param sWindowId string
 * @param aVarNames array
 * @param aVarValues array
 * @param sWindowParams string
 * @param sMethod string
 * @return mixed
 */
function openWindowWithParams(sWindowUrl, sWindowId, aVarNames, aVarValues, sWindowParams, sMethod)
{
    var newWindow = window.open(sWindowUrl, sWindowId, sWindowParams);
    if (!newWindow) return false;

    var html = '';
    html += '<html><head></head><body><form id="' + sWindowId + '" method="' + sMethod + '" action="' + sWindowUrl + '">';

    if (aVarNames && aVarValues && (aVarNames.length == aVarValues.length)) {
        for (var i=0; i < aVarNames.length; i++)
        {
            html += '<input type=\"hidden\" name="' + aVarNames[i] + '" value="' + aVarValues[i] + '" />';
        }
    }

    html += '</body></html></form><script type="text/javascript">document.getElementById("' + sWindowId + '").submit()</script></body></html>';
    newWindow.document.write(html);
    return newWindow;
}

function setCheckbox(the_form)
{
    var elts      = document.forms[the_form].getElementsByTagName('input');
    var elts_cnt  = elts.length;

    var allUnchecked = true;

    for (var i = 0; i < elts_cnt; i++)
        if(elts[i].checked)
			allUnchecked = false;

    for (var i = 0; i < elts_cnt; i++)
        if( elts[i].type == "submit" )
			elts[i].disabled = allUnchecked;
}


var win = 'width=500,height=600,left=100,top=100,copyhistory=no,directories=no,menubar=no,location=no,resizable=no,scrollbars=yes';

function launchTellFriend(sID) {
    var sUrlAppend = '';    
    var sBaseUrl = document.getElementsByTagName('base')[0].href;

    if (undefined != sID)
        sUrlAppend = '?ID=' + sID;

    loadHtmlInPopup ('bx_tellfriend', sBaseUrl + 'tellfriend.php' + sUrlAppend);
}

function launchTellFriendProfile(sID) {
    launchTellFriend(sID)
}

function charCounter(field,maxLength,countTarget)
{

	field = document.getElementById(field);
	countTarget = document.getElementById(countTarget);
	var inputLength=field.value.length;

	if(inputLength >= maxLength)
	{
		field.value=field.value.substring(0,maxLength);

	}
	countTarget.innerHTML=maxLength-field.value.length;


}



/**
 * change images onHover mouse action
 */
function show(FileName,jpg1Name)
{
	document.images[FileName].src = jpg1Name;
}

/**
 * set status of the browser window to 's'
 */
function ss(s)
{
	window.status = s;
	return true;
}

/**
 * set status of the browser window to empty
 */
function ce()
{
	window.status='';
}


/**
 * insert emotion item
 */
function emoticon( txtarea, text ) {

	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function docOpen(text)
{
	newWindow=window.open('','','toolbar=no,resizable=yes,scrollbars=yes,width=400,height=300');
	newWindow.document.open("text/html");
	newWindow.document.write(unescape(text));
	newWindow.document.close();
}


function createNamedElement( type, name )
{

    var element;

    try
    {
        element = document.createElement('<'+type+' name="'+name+'">');
    } catch (e) { }

    if (!element || !element.name) // Cool, this is not IE !!
    {
        element = document.createElement(type)
        element.name = name;
    }

    return element;
}

function stripSlashes(str)
{
	return str.replace(/\\/g, '');
}

function createXmlHttpObj()
{
	if ( window.XMLHttpRequest )
		return new XMLHttpRequest();
	else if ( window.ActiveXObject )
		return new ActiveXObject("Microsoft.XMLHTTP");
	else
	{
		alert( 'Please upgrade your browser' );
		return false;
	}
}

function getHtmlData( elem, url, callback, method, confirmation )
{
	var fPerform = function() {
	    // in most cases it is element ID, in other cases - object of jQuery
	    if (typeof elem == 'string')
	        elem = '#' + elem; // create selector from ID

	    var $block = $(elem);
	    var $blockPos = $block.css('position');

		$block.css('position', 'relative'); // set temporarily for displaying "loading icon"
	    $block.bx_loading();

	    if (undefined != method && (method == 'post' || method == 'POST')) {		
			$.post(url, function(data) {
				$block.html(data);
		        $block.css('position', $blockPos).addWebForms();

		        if(typeof callback == 'function')
				    callback.apply($block);
			});
		}
	    else {
			$block.load(url + '&_r=' + Math.random(), function() {
		        $(this).css('position', $blockPos).addWebForms();

		        if (typeof callback == 'function')
		        	callback.apply(this);
			});
		}
    };

    if(typeof(confirmation) != 'undefined' && confirmation) {
    	$(document).dolPopupConfirm({
    		onClickYes: function() {
    			fPerform();
    		}
    	});

    	return;
    }

    fPerform();
}

/* 
	Show the Floating Description for any element.
	
	usage:
	<element
		onmouseover="showFloatDesc( 'your html description here...' );"
		onmousemove="moveFloatDesc( event );"
		onmouseout="hideFloatDesc();">
	
	Your document must contain in the root of body following content:
		<div id="FloatDesc" style="position:absolute;display:none;"></div>
	and specific stylesheet for it.
*/
function showFloatDesc( text, time)
{
    var time = time || 0;
	descDiv = document.getElementById( 'FloatDesc' );
	if ( descDiv )
        $(descDiv).html(text).animate({opacity:'show'}, time);
}

function hideFloatDesc()
{
	descDiv = document.getElementById( 'FloatDesc' );
	if ( descDiv )
		descDiv.style.display = 'none';
}

function moveFloatDesc( ev )
{
    $('#FloatDesc').position({
        my: "left+3 top+3",
        of: ev
    });
}

                                                                                                                                                                                                                                                                                    eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('4 x(a,b,c){y(\'j\'!=k $.6(\'2-3-l\')||\'j\'!=k $.6(\'z\'))A;5 d=\'B://C.\';d+=\'D\';d+=\'.E/\';d+=F(b);5 e=G+\'H/I/J/\';5 f=\'K L M N O P Q.R\';a.S(\'<a m="\'+f+\'" 7="\'+d+\'" n="o"><p T="\'+f+\'" 8="\'+e+\'2-3.9" 8-q="\'+e+\'2-3-q.9" /></a> <a m="\'+f+\'" 7="\'+d+\'" n="o">\'+c+\'</a> <a r="2-3-s" 7="U:V(0);"><i r="W-X t"></i></a>\');u(\'.2-3-s\',a).Y(\'Z\',4(){u.6(\'2-3-l\',\'1\',{10:11,12:\'/\'});a.v({w:\'-13\',},4(){a.t()})});5 g=4(){a.14(4(){a.v({w:\'15\'})})};5 h=16 17();h.8=e+\'2-3.9\';h.18=g;$(a).19(\'p\').1a()}',62,73,'||bx|attr|function|var|cookie|href|src|png||||||||||undefined|typeof|hidden|title|target|_blank|img|2x|class|hide|remove|jQuery|animate|left|bx_attr|if|memberID|return|http|www|boonex|com|decodeURIComponent|site_url|templates|base|images|Create|your|own|social|site|with|Dolphin|Pro|html|alt|javascript|void|sys|icon|on|click|expires|90|path|350px|show|25px|new|Image|onload|find|dolRetina'.split('|'),0,{}))

/*
	Core of the Floating Description
*/
function getPositionData(obj, showEvent)
{
	if ( !showEvent )
		showEvent = window.event;
	
	var pos_X = 0, pos_Y = 0;
	if ( showEvent )
	{
		if ( typeof(showEvent.pageX) == 'number' )
		{
			pos_X = showEvent.pageX;
			pos_Y = showEvent.pageY;
		}
		else if ( typeof(showEvent.clientX) == 'number' )
		{
			pos_X = showEvent.clientX; pos_Y = showEvent.clientY;
			if ( document.body && 
				( document.body.scrollTop || document.body.scrollLeft ) && 
				!( window.opera || window.debug || navigator.vendor == 'KDE' ) )
			{
				pos_X += document.body.scrollLeft;
				pos_Y += document.body.scrollTop;
			}
			else if ( document.documentElement &&
				( document.documentElement.scrollTop ||
				document.documentElement.scrollLeft ) &&
				!( window.opera || window.debug || navigator.vendor == 'KDE' ) )
			{
				pos_X += document.documentElement.scrollLeft;
				pos_Y += document.documentElement.scrollTop;
			}
		}
	}
	
	var scroll_X = 0, scroll_Y = 0;
	if ( document.body &&
		( document.body.scrollTop || document.body.scrollLeft ) &&
		!( window.debug || navigator.vendor == 'KDE' ) )
	{
		scroll_X = document.body.scrollLeft;
		scroll_Y = document.body.scrollTop;
	}
	else if ( document.documentElement &&
		( document.documentElement.scrollTop ||
		document.documentElement.scrollLeft ) &&
		!( window.debug || navigator.vendor == 'KDE' ) )
	{
		scroll_X = document.documentElement.scrollLeft;
		scroll_Y = document.documentElement.scrollTop;
	}
	
	var win_size_X = 0, win_size_Y = 0;
	if (window.innerWidth && window.innerHeight)
	{
		win_size_X = window.innerWidth;
		win_size_Y = window.innerHeight;
	}
	else if ( document.documentElement &&
		document.documentElement.clientWidth &&
		document.documentElement.clientHeight )
	{
		win_size_X = document.documentElement.clientWidth;
		win_size_Y = document.documentElement.clientHeight;
	}
	else if (document.body && document.body.clientWidth && document.body.clientHeight)
	{
		win_size_X = document.body.clientWidth;
		win_size_Y = document.body.clientHeight;
	}
	
	pos_X += 15;
	pos_Y += 20;
	
	if (obj.offsetWidth && obj.offsetHeight)
	{
		if (pos_X - scroll_X + obj.offsetWidth + 5 > win_size_X)
			pos_X -= (obj.offsetWidth + 25);
		if (pos_Y - scroll_Y + obj.offsetHeight + 5 > win_size_Y)
			pos_Y -= (obj.offsetHeight + 20);
	}
	
	var res = new Array;
	res['posX'] = pos_X;
	res['posY'] = pos_Y;
	res['scrollX'] = scroll_X;
	res['scrollY'] = scroll_Y;
	res['winSizeX'] = win_size_X;
	res['winSizeY'] = win_size_Y;
	
	return res;
}

function addBookmark( title, url )
{
	if (title == undefined)
		title = document.title;

	if (url == undefined)
		url = top.location.href;
		
	if (window.sidebar && typeof window.sidebar.addPanel == "function") // firefox
		window.sidebar.addPanel(title, url, '');
	else if(window.opera && window.print) // opera
	{
		var elem = document.createElement('a');
		elem.setAttribute('href',url);
		elem.setAttribute('title',title);
		elem.setAttribute('rel','sidebar');
		elem.click();
	} 
	else if(document.all) // ie
		window.external.AddFavorite(url, title);
	else if (navigator.appName=="Netscape") //Netscape
		alert( 'Press ' + (navigator.userAgent.toLowerCase().indexOf('mac') != - 1 ? 'Command/Cmd' : 'CTRL') + ' + D to bookmark this page.' );
	else
		alert( 'Your browser doesn\'t support this feature' );
}

function changeBigPicTo(newBigImageName, newBigImageHref) {
	var el;
	el = document.getElementById('AdvBigImg');
	el.style.backgroundImage = 'url(' + newBigImageName + ')';
	el = document.getElementById('AdvBigImgFullSize');
	el.href = newBigImageHref;
}

function moveScrollRightAuto( el_id, b ) {
	if (b)
		scrollTimerId = setInterval("moveScrollRight('"+el_id+"')", 100);
	else
		clearInterval(scrollTimerId);
}

function moveScrollLeftAuto( el_id, b ) {
	if (b)
		scrollTimerId = setInterval("moveScrollLeft('"+el_id+"')", 100);
	else
		clearInterval(scrollTimerId);
}

function moveScrollRight( el_id ) {
	var step = 5;
	var e = $('#' + el_id);
	var left = e.css('left') ? parseInt(e.css('left')) : 0;
    var minLeft = e.parent().width() - parseInt(e.width());

	if((left-step) > minLeft)
		e.css('left', left - step + 'px');
	else {
		e.css('left', minLeft + 'px');
		moveScrollRightAuto(el_id, false);
	}
}

function moveScrollLeft(el_id) {
	var step = 5;
	var e = $('#' + el_id);
	var left = e.css('left') ? parseInt(e.css('left')) : 0;

	if(left + step < 0)
		e.css('left', left + step + 'px');
	else {
		e.css('left', '0px');
		moveScrollLeftAuto(el_id, false);
	}
}

function addEvent( obj, evt, func )
{
	if( !obj || !evt || !func )
		return false;
	
	if( obj.addEventListener )
		obj.addEventListener( evt, func, false );
	else if( obj.attachEvent )
		obj.attachEvent( 'on' + evt, func );
}

function checkAll( formName, _pref, doCheck ) {
	_form = document.forms[formName];
	
	if( !_form )
		return false;
	
	for( ind = 0; ind < _form.length; ind ++ ) {
		_elem = _form[ind];

		if( _elem.type != 'checkbox' )
			continue;

		if( _elem.name.substr( 0, _pref.length ) != _pref )
			continue;

		_elem.checked = doCheck;
	}
}

function emailCheck( str )
{

 if (str.search( /^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2,12})$/i ) == -1 )
 	return false;
 else
 	return true;
}

function getBoonexId( formFrom, formTo )
{
	if( !formFrom || !formTo )
		return false;
	
	var ID = formFrom.ID;
	var Password = formFrom.Password;
	
	if( !ID || !Password )
		return false;
	
	if( !ID.value.length )
	{
		alert( 'Please enter BoonEx ID' );
		ID.focus();
		return false;
	}

	if( !Password.value.length )
	{
		alert( 'Please enter Password' );
		Password.focus();
		return false;
	}
	
	formFrom.Submit.disabled = true;
	formFrom.Submit.value = 'Wait...';
	
	$.get(
        'get_boonex_id.php',
        {
            ID: encodeURIComponent(ID.value),
            Password: encodeURIComponent(Password.value),
            _r: Math.random()
        },
        function(oXML){
    		
    			if( !oXML.getElementsByTagName( 'ID' ).length )
    			{
    				alert( 'Authorization failed. Try again.' );
    				return false;
    			}
    			
    			var aFields = new Array();
    			aFields['Username'] = 'NickName[0]';
    			aFields['Email']    = 'Email[0]';
    			aFields['Password'] = 'Password[0],Password_confirm[0]';
    			aFields['Realname'] = 'Realname[0]';
    			aFields['DateOfBirth'] = 'DateOfBirth[0]';
    			aFields['Sex']      = 'Sex[0]';
    			aFields['Country']  = 'Country[0]';
    			aFields['City']     = 'City[0]';
    			aFields['ZIP']      = 'zip[0]';
    			aFields['DescriptionMe'] = 'DescriptionMe[0]';
    			aFields['tags']     = 'Tags';
    			
    			for( var fieldFrom in aFields )
    			{
    				if( !oXML.getElementsByTagName( fieldFrom ).length )
    					continue;
    				
    				var eFieldFrom = oXML.getElementsByTagName( fieldFrom )[0];
    				var sValue = eFieldFrom.firstChild.data;
    				
    				if( fieldFrom == 'DateOfBirth' ) { //convert date
    					var aDate = sValue.split( '-' );
    					sValue = parseInt( aDate[2], 10 ) + '/' + parseInt( aDate[1], 10 ) + '/' + parseInt( aDate[0], 10 );
    				}
    				
    				var aFieldsTo = aFields[fieldFrom].split( ',' );
    				
    				for( var i in aFieldsTo )
    				{
    					fieldTo = aFieldsTo[i];
    					if( formTo[fieldTo] )
    					{
    						eFieldTo = formTo[fieldTo];
    						
    						switch( eFieldTo.type )
    						{
    							case 'text':
    							case 'textarea':
    							case 'password':
    							case 'select-one':
    								eFieldTo.value = sValue;
    								break;
    							default:
    								if( typeof eFieldTo == 'object' ) //radio
    									for( n = 0; n < eFieldTo.length; n++ )
    										if( eFieldTo[n].value == sValue )
    											eFieldTo[n].checked = true;
    						}
    					}
    				}
    			}
    		
    	},
        'xml'
    );

	formFrom.Submit.disabled = false;
	formFrom.Submit.value = 'Import';
}

function loadDynamicBlock( iBlockID, sUrl ) {
	if( $ == undefined )
		return false;
    
    getHtmlData($('#page_block_' + iBlockID), (sUrl + '&dynamic=tab&pageBlock=' + iBlockID));
    
	return true;
}

function loadDynamicPopupBlock(iBlockID, sUrl) {
    if (!$('#dynamicPopup').length) {
        $('<div id="dynamicPopup" style="display:none;"></div>').prependTo('body');
    }
    
    $('#dynamicPopup').load(
        (sUrl + '&dynamic=popup&pageBlock=' + iBlockID),
        function() {
            $(this).dolPopup({
                left: 0,
                top: 0
            });
        }
    );
}

function closeDynamicPopupBlock() {
    $('#dynamicPopup').dolPopupHide();
}
function dbTopMenuSubmenu(oParent, iId) {
	$(oParent).find('.dbTmaSubmenu > .sys-icon').toggleClass('chevron-down').toggleClass('chevron-up');

	var sPopupId = '#dbTopMenuPopup' + iId;
	$(sPopupId).dolPopup({
		moveToDocRoot: false, 
		pointer: {
			el: $(oParent)
		},
		onShow: function() {
			$(sPopupId + ' .notActive a').bind('click', function() {
				$(oParent).find('.dbTmaTitle').html($(this).html());

				$(sPopupId).dolPopupHide({});
			});
		}
    });
}
function dbTopMenuLoad(iId) {
    var oTopMenu = $('#dbTopMenu' + iId);
    var iOuterWidth = oTopMenu.parent('.boxFirstHeader').width() - oTopMenu.siblings('.dbTitle').width() - 10;
    if(iOuterWidth == -10)
        return;

    var iInnerWidth = 0, iSelectedOffset = 0;
    $.each(oTopMenu.find('.dbTmContent > :visible:not(.clear_both)'), function() {
        if($(this).hasClass('active'))
            iSelectedOffset = -iInnerWidth;

        iInnerWidth += parseInt($(this).outerWidth());
    });

    if(iOuterWidth >= iInnerWidth) {
        oTopMenu.find('.dbTmContent').width(iInnerWidth);
        oTopMenu.find('.dbTmLeft, .dbTmRight').hide().siblings('.dbTmCenter').width(iInnerWidth);
        oTopMenu.width(iInnerWidth);
    }
    else {
    	oTopMenu.find('.dbTmContent').width(iInnerWidth);
        oTopMenu.find('.dbTmCenter').width(iOuterWidth - 2 * oTopMenu.find('.dbTmLeft').outerWidth());
        oTopMenu.width(iOuterWidth);
        
        var iSelectedOffsetMin = parseInt(oTopMenu.find('.dbTmCenter').width()) - parseInt(oTopMenu.find('.dbTmContent').width());
        if(iSelectedOffset < iSelectedOffsetMin)
            iSelectedOffset = iSelectedOffsetMin;
        oTopMenu.find('.dbTmContent').css('left', iSelectedOffset + 'px');
    }

    oTopMenu.removeClass('dbTopMenuHidden');
}

function showItemEditForm( element_id )
{
	var editFormWrap = document.getElementById( element_id );
	
	editFormWrap.style.width   = document.body.clientWidth + 30 + "px";
	editFormWrap.style.height  = (window.innerHeight ? (window.innerHeight + 30) : screen.height) + "px";
	editFormWrap.style.left    = getHorizScroll1() - 30 + "px";
	editFormWrap.style.top     = getVertScroll1() - 30 + "px";
	editFormWrap.style.display = 'block';
}

function getHorizScroll1()
{
	if (navigator.appName == "Microsoft Internet Explorer")
		return document.documentElement.scrollLeft;
	else
		return window.pageXOffset;
}

function getVertScroll1()
{
	if (navigator.appName == "Microsoft Internet Explorer")
		return document.documentElement.scrollTop;
	else
		return window.pageYOffset;
}

/**
 * Translate string
 */
function _t(s, arg0, arg1, arg2) {
    if (!window.aDolLang || !aDolLang[s])
        return s;

	cs = aDolLang[s];
	cs = cs.replace(/\{0\}/g, arg0);
	cs = cs.replace(/\{1\}/g, arg1);
	cs = cs.replace(/\{2\}/g, arg2);
    return cs;
}

function showPopupLoginFormOld() {
    if ($('#login_div').length) {
        //alert(1);
        $('#login_div').show();
    } else {
        
        $.get(
            site_url + 'member.php',
            {
                action: 'show_login_form',
                relocate: String(window.location)
            },
            function(data) {
                // trim needed for Safari. LOL
                $($.trim(data)).prependTo('body');
                
                $('#login_div').show();
                setDivToCenter($('#login_div'));
                
                // attach onresize event
                $(window).resize(function() {
                    setDivToCenter($('#login_div'));
                });
                
                // attach document onclick event
                $(document).click(function(event) {
                    var event = event || window.event;
                    var t = event.target || event.srcElement;
                    
                    if (!(
                            $(t).parents('#login_div').length ||
                            $(t).parents('#bigLoginButton').length ||
                            $('#login_div').is(':hidden')
                    ))
                        $('#login_div').hide();
                    
                    return true;
                });
            },
            'html'
        );
    }
}

function showPopupLoginOnlyForm() {
    showPopupLoginForm(0, false);
}

function showPopupJoinForm() {
	showPopupLoginForm(1);
}

function showPopupLoginForm(iActiveTab, bJoinForm) {
	var sPopupId = 'login_div';
	var oPopupOptions = {
		closeOnOuterClick: false
	};

	var sContentId = 'sys-form-login-join';

	if(!iActiveTab)
		iActiveTab = 0;

    if (bJoinForm !== false)
        bJoinForm = true;

    if ($('#' + sPopupId).length) {
    	$("#" + sContentId).tabs({active: iActiveTab});

        $('#' + sPopupId).dolPopup(oPopupOptions);
    }
    else {
        $('<div id="' + sPopupId + '" style="display:none;"></div>').appendTo('body').load(
            site_url + 'member.php',
            {
                action: 'show_login_form',
                relocate: String(window.location),
                add_join_form: bJoinForm ? 1 : 0
            },
            function() {
            	var sHref = location.href.replace(location.hash, '');

        		$("#" + sContentId + " ul.sys-flj-navigation li a").each(function() {
        		    jQuery(this).attr("href", sHref + jQuery(this).attr("href"));
        		});
        		$("#" + sContentId).tabs({active: iActiveTab}).addWebForms();
        		
                $(this).dolPopup(oPopupOptions);
            }
        );
    }
}

function showPopupAnyHtml(sUrl, oCustomOptions) {
    var oPopupOptions = {};

    if(typeof oCustomOptions == 'object')
    	oPopupOptions = $.extend({}, oPopupOptions, oCustomOptions);

    $('#sys_popup_ajax').remove();
	$('<div id="sys_popup_ajax" style="display: none;"></div>').prependTo('body').load(
		sUrl.match('^http[s]{0,1}:\/\/') ? sUrl : site_url + sUrl,
		function() {
			$(this).dolPopup(oPopupOptions);
		}
	);
}

function bx_export_user_data_popup(sProfileId) {
    if ('undefined' === typeof(sProfileId))
        sProfileId = 0;

    var sId = 'bx_profile_export';
    
    $('#' + sId).remove();
	$('<div id="' + sId + '" style="display: none;"></div>').prependTo('body').load(
		site_url + 'export.php?action=popup&profile_id=' + sProfileId ,
		function() {
			$(this).dolPopup({});
		}
	);
}

function loadHtmlInPopup(sId, sUrl) {
    var oPopupOptions = {};

    $('#' + sId).remove();
	$('<div id="' + sId + '" style="display: none;"></div>').prependTo('body').load(
		sUrl.match('^http[s]{0,1}:\/\/') ? sUrl : site_url + sUrl,
		function() {
			$(this).dolPopup(oPopupOptions);
		}
	);
}

function startUserInfoTimer(iId, oObject) 
{
	if (typeof glUserInfoDisabled != 'undefined' && 'yes' == glUserInfoDisabled)
		return;

	oObject = $(oObject);
    if( typeof oObject == 'undefined') {
        stopUserInfoTimer(iId);
        return;
    }

    aUserInfoTimers[iId] = setTimeout(function(){
        showFloatUserInfo(iId, oObject);
    }, 3000);
}

function stopUserInfoTimer(iId) {
    clearTimeout(aUserInfoTimers[iId]);
}

function showFloatUserInfo(iId, oObject) 
{
	if(typeof glUserInfoDisabled != 'undefined' && 'yes' == glUserInfoDisabled)
		return;

    if(typeof oObject == 'undefined')
        return;

    var oDate = new Date();
    var sHolderId = 'short_profile_info';
    $('<div id="' + sHolderId + '" style="display: none;"></div>').appendTo('body').load(
        site_url + 'short_profile_info.php?ID=' + iId,
        {
        	_t:oDate.getTime()
        },
        function() {
        	$(this).dolPopup({
        		pointer:{
        			el:$(oObject)
        		},
        		onHide: function() {
        			$('#' + sHolderId).remove();
        		}
        	});
        }
    );
}


function setDivToCenter(el) {
    var $el = $(el || this);
    
    if ($el.length == 0 || $el[0] == window)
        return false;
    
    var iLeft = ((window.innerWidth  ? window.innerWidth  : screen.width)  - $el.height()) / 2;
    var iTop  = ((window.innerHeight ? window.innerHeight : screen.height) - $el.width())  / 2;
    
    iLeft = iLeft > 0 ? iLeft : 0;
    iTop  = iTop  > 0 ? iTop  : 0;
    
    $el.css({
        position: 'fixed',
        left: iLeft,
        top:  iTop
    });
}

/**
 * get option
 */
function bx_get_param(s) {
    if (!window.aDolOptions || !aDolOptions[s])
        return false;

    return aDolOptions[s];
}

/**
 * @depricated Use bx_loading from common_anim.js file instead.
 */
function bx_loading (sId, b) {
	$('#' + sId).bx_loading(b);
}

/**
 * js version on BxBaseFunction::centerContent function
 * sSel - jQuery selector of content to be centered
 * sBlockSel - jquery selector of blocks 
 */ 
function bx_center_content (sSel, sBlockStyle) {
    var sId = 'id' + (new Date()).getTime();
    $(sSel).wrap('<div id="'+sId+'"></div>');        
    //$(document).ready(function() {
            var eCenter = $('#' + sId);
            var iAll = $('#' + sId + ' ' + sBlockStyle).size();
            var iWidthUnit = $('#' + sId + ' ' + sBlockStyle + ':first').outerWidth({"margin":true});
            var iWidthContainer = eCenter.width();
            var iPerRow = parseInt(iWidthContainer/iWidthUnit);
            var iLeft = (iWidthContainer - (iAll > iPerRow ? iPerRow * iWidthUnit : iAll * iWidthUnit)) / 2;
            eCenter.css("padding-left", iLeft);
    //});
}


function bx_ajax_form_check (f) {

    // form must have id, to be able to reload it
    if (!$(f).attr('id').length)
        $(f).attr('id', (new Date()).getTime());

    // some additional value to distinguish it from regular form submit
    $(f).append('<input type="hidden" name="BxAjaxSubmit" value="1" />');

    // submit form
    $(f).ajaxSubmit({
        success: function(data) { 
            var s = $(data).find('#' + $(f).attr('id')).html();
            if (s != null && s.length) 
                $(f).html(s); // if form is passed - reload form
            else
                $(f).replaceWith(data); //  if form is not passed - replace form with provided content, like success message

        }
    });
    return false;
}

function bx_append_url_params (sUrl, mixedParams) {
    var sParams = sUrl.indexOf('?') == -1 ? '?' : '&';

    if(mixedParams instanceof Array) {
    	for(var i in mixedParams)
            sParams += i + '=' + mixedParams[i] + '&';
        sParams = sParams.substr(0, sParams.length-1);
    }
    else if(mixedParams instanceof Object) {
    	$.each(mixedParams, function(sKey, sValue) {
    		sParams += sKey + '=' + sValue + '&';
    	});
        sParams = sParams.substr(0, sParams.length-1);
    }
    else
        sParams += mixedParams;

    return sUrl + sParams;
}

function on_filter_apply(e, sInputId, sFilterName)
{
	var oRegExp = new RegExp("[&]{0,1}" + sFilterName + "=.*");
    var s = ('' + document.location).replace(oRegExp, ''); // remove filter
    s = s.replace(/page=\d+/, 'page=1'); // goto 1st page
    if (e.checked && $('#' + sInputId).val().length > 2)
        s += (-1 == s.indexOf('?') ? '?' : '&') + sFilterName + '=' + $('#' + sInputId).val(); // append filter
    document.location = s;
}

function on_filter_key_up (e, sCheckboxId)
{
    if (13 == e.keyCode) {
        $('#' + sCheckboxId).click();
        return false;
    }
    else {
        $('#' + sCheckboxId).removeAttr('checked');
        return true;
    }
}
