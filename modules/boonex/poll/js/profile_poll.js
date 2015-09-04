// =============================================================================
// Poll functions ======================================================
// =============================================================================

function add_question_bar( item, num, focus )
{
	var num = document.getElementById( num );
	var item = document.getElementById( item );

	var newdiv = document.createElement( "div" );
	newdiv.id = "d" + num.value;
	newdiv.className = "answer_block";

	var newinput = createNamedElement( "input", "v" + num.value );
	newinput.type = "text";
	newinput.id = "v" + num.value;
	newinput.name = "answers[]";

	var newtext = document.createTextNode( lang_delete );

	var newlink = document.createElement( "a" );
	newlink.href="#";
	newlink.onclick = function() { del_question_bar( item, newdiv ); return false; }
	newlink.style.marginLeft = '4px';
	newlink.appendChild( newtext );

	//var newbr = document.createElement( "br" );

	num.value++;

	//item.appendChild( newbr );
	newdiv.appendChild( newinput );
	newdiv.appendChild( newlink );

	item.appendChild( newdiv );

	if ( focus ) newinput.focus();
}

function del_question_bar( parent, child )
{
	parent.removeChild( child );
}

function poll_status_show( id, item, status, status_change_to, cur_status_lbl, status_change_to_lbl )
{
	var cont = document.getElementById( item );
	cont.innerHTML = '';
	
	var newtext = document.createTextNode( cur_status_lbl );
	cont.appendChild( newtext );

	newtext = document.createTextNode( ' / ' );
	cont.appendChild( newtext );
	
	newtext = document.createTextNode( status_change_to_lbl );
	var newlink = document.createElement( "a" );
	newlink.href="#";
	newlink.onclick = function() {
		send_data( '', 'status', '&param=' + status_change_to, id );
		poll_status_show( id, item, status_change_to, status, status_change_to_lbl, cur_status_lbl );
		return false;
	}
	newlink.appendChild( newtext );
	cont.appendChild( newlink );
	
	newtext = document.createTextNode( ' / ' );
	cont.appendChild( newtext );
}

function createNamedElement( type, name )
{
	var element;

	try
	{
		element = document.createElement('<'+type+' name="'+name+'">');
	}
	catch (e) { }

	if (!element || !element.name) // Cool, this is not IE !!
	{
		element = document.createElement(type)
		element.name = name;
	}

	return element;
}

/**
 * Function will send some of commands to the server side ;
 *
 * @param  : container (string)  - recepient block's Id;
 * @param  : action (string)     - action name;
 * @param  : param (string)      - extended parameters;
 * @param  : id (integer)        - pool's Id;
 * @return : (text) Html presentation data ;
 */
function send_data( container, action, param, id ) {
	var ID = id;

	var sPrefix = 'dpol';
	if(typeof(container)=='object' && (container instanceof Array)) {
		sPrefix = container[0];
		container = container[1];
	}

	if(container) {
		var container = document.getElementById( container );
		container.innerHTML = lang_loading;
	}

	var XMLHttpRequestObject = false;

	if ( window.XMLHttpRequest )
		XMLHttpRequestObject = new XMLHttpRequest();
	else if ( window.ActiveXObject )
		XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");

	if( XMLHttpRequestObject )
	{
        var _sRandom = Math.random();
		var data_source = sPageReceiver + '/' + action + '/' + ID + param + '&_r=' + _sRandom;
		XMLHttpRequestObject.open( "GET", data_source );
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if ( XMLHttpRequestObject.readyState == 4 && XMLHttpRequestObject.status == 200 ) {
				var xmlDocument = XMLHttpRequestObject.responseXML;

				if ( 'delete_poll' == action ) {
                    answer = xmlDocument.getElementsByTagName("answer");
					if (answer[0].firstChild.data == 'ok' ){
                        window.location.reload();
                    }
                    else {
                        // return error code ;
                        alert(answer[0].firstChild.data);
                    }
				}
				else if ( 'set_answer' == action ) {
					container.innerHTML = '';

					answers_points = xmlDocument.getElementsByTagName("answer_point");
					answers_num    = xmlDocument.getElementsByTagName("answer_num");
					answers_names  = xmlDocument.getElementsByTagName("answer_name");

					list_results(sPrefix);
				}
				else if ( 'get_questions' == action ) {
					container.innerHTML = '';
					answers = xmlDocument.getElementsByTagName("answer");

                    list_answers(sPrefix);

                    question = xmlDocument.getElementsByTagName("question");
					list_question(sPrefix, sPrefix + '_caption_' + ID );
				}
				else if( 'get_poll_block' == action ) {
                   container.innerHTML = 'loading....';
                }

				delete XMLHttpRequestObject;
				XMLHttpRequestObject = null;
			}
		}

		XMLHttpRequestObject.send( null );
	}


	function scrollers_display()
	{
		//return;
		if ( ( container.offsetTop + container.offsetHeight ) < container.parentNode.offsetHeight )
		{
			var oArrUp   =  document.getElementById( 'dpol_arr_up_' + ID );
            if(oArrUp) {
                oArrUp.style.display='none';
			}

            var oArrDown =  document.getElementById( 'dpol_arr_down_' + ID );
            if(oArrDown) {
                oArrDown.style.display='none';
			}
		}
		else
		{
            var oArrUp   =  document.getElementById( 'dpol_arr_up_' + ID );
            if(oArrUp) {
                oArrUp.style.display='block';
			}

            var oArrDown =  document.getElementById( 'dpol_arr_down_' + ID );
            if(oArrDown) {
                oArrDown.style.display='block';
			}
		}
	}

	function list_answers(sPrefix) {
		var loopIndex;

		$(container).append('<input type="hidden" id="' + sPrefix + '_current_vote_' + ID + '" value="" />');

		for ( loopIndex = 0; loopIndex < answers.length; loopIndex++ ) {
			var oDivAnswer = $("<div></div>");
			oDivAnswer.attr('id', 'q_' + ID + '_' + loopIndex);
			oDivAnswer.addClass('pollAnswerContent');
			oDivAnswer.html(answers[loopIndex].firstChild.data);

			var oDivRow = $("<div></div>");
			oDivRow.addClass('pollAnswerRow');
            oDivRow.append('<input type="radio" name="vote_' + ID + '" value="' + loopIndex + '" onclick="PerformSubmit(' + ID + ', ' + loopIndex + ', \'' + sPrefix + '\');"/>');
			oDivRow.append(oDivAnswer);

			$(container).append(oDivRow);
		}

		scrollers_display();
	}

	function list_question(sPrefix, sCont) {
        $('#' + sCont).find('a:first').text(question[0].firstChild.data);
    }

	function list_results(sPrefix) {
		var loopIndex;
        var iAnswersCount = answers_points.length;

        if(iAnswersCount) {
            for ( loopIndex = 0; loopIndex < iAnswersCount; loopIndex++ ) {
                draw_bar( answers_points[loopIndex].firstChild.data, answers_names[loopIndex].firstChild.data, answers_num[loopIndex].firstChild.data, loopIndex );
            }
        }

		scrollers_display();
	}

	function draw_bar( num, comment, votes, id ) {
		var newtext = document.createTextNode( comment );

		// will contain number of votes ;
        var oSpanObject = document.createElement( "span" );
        oSpanObject.setAttribute("class", 'votes_number' );
        var oVoteNumber = document.createTextNode( ' (' + votes + ') ' );
        oSpanObject.appendChild( oVoteNumber );

        var oDivAnswer = document.createElement( "div" );
		oDivAnswer.setAttribute("id", 'r_' + ID + "_" + id );
		oDivAnswer.setAttribute("class", 'pollResultAnswerRow');
		oDivAnswer.appendChild( newtext );
		oDivAnswer.appendChild( oSpanObject );

		var oDivResult = document.createElement( "div" );
		oDivResult.setAttribute("id", 'p_' + ID + '_' + id );
		oDivResult.setAttribute("class", 'pollResultStatsRow');
		oDivResult.style.width = "25px";

		if ( "string" != typeof(dpoll_progress_bar_color) )
			dpoll_progress_bar_color = '#D7E4E5';

        newtext = document.createTextNode( num + '%' );
		oDivResult.appendChild(newtext);

		container.appendChild( oDivAnswer );
		container.appendChild( oDivResult );		

        var sBarId = 'p_' + ID + '_' + id;
		enlargePollBar(sBarId, num );
        $('#' + sBarId).addClass('pollResultStatsRow');
	}
}

/**
 * Function will send vote's result ;
 *
 * @param   : ID (integer) - pool's Id;
 * @param   : loopIndex (integer) - poll's value ;
 */
function PerformSubmit(ID, loopIndex, sPrefix) {
    set_vote( sPrefix + "_current_vote_" + ID , loopIndex );
    send_data( sPrefix + "_question_text_" + ID , 'set_answer',  '/' + loopIndex, ID );

    $('#' + sPrefix + '_result_block' + ID).hide();
    $('#' + sPrefix + '_back_poll' + ID).show();

    return false;
}

function enlargePollBar( sBarID, iSize ) {
	var eBar = document.getElementById(sBarID);
    if(eBar) {
        var iWidthLimit = Math.floor(iSize * (eBar.parentNode.offsetWidth / 100));
        var iParentWrapWidth = parseInt($(eBar).parent().width());

    	if(iWidthLimit > eBar.offsetWidth && parseInt(eBar.style.width) < iParentWrapWidth) {
    		eBar.style.width = eBar.offsetWidth + 2 + 'px';
    		setTimeout("enlargePollBar('" + sBarID + "', " + iSize + ")", 5);
    	}
    }
}


// =============================================================================
// End of Server interact part =================================================
// =============================================================================

// +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

// =============================================================================
// Local part ==================================================================
// =============================================================================


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



    function move_left()
    {
	if (c_item.offsetLeft + c_middle > 0)
	{
	    c_item.style.left = (c_item.offsetLeft-1) + 'px';
	}
	else
	{
	    c_item.style.left = '0px';	
	}
	    
    }

function move_up( iCurElement )
{
	if ( (c_item.offsetTop + c_item.offsetHeight) > c_item.parentNode.offsetHeight )
	{
		c_item.style.top = (c_item.offsetTop-2) + 'px';
	}
    else {
        document.getElementById( 'dpol_arr_down_' + iCurElement ).style.display='none';
    } 
}



function move_down( iCurElement )
{
	if ( c_item.offsetTop < 0 )
	{
		c_item.style.top = (c_item.offsetTop+2) + 'px';
	}
    else {
        document.getElementById( 'dpol_arr_up_' + iCurElement ).style.display='none';
    }    
}



function scroll_start( item, dir )
{
	c_item = item;
    
	if ( 'horizontal' == dir )
	{

		if ( c_item.offsetWidth <= c_item.parentNode.offsetWidth ) {
            return false;
        }

		if ( 1 != double_sized_items[c_item.id] )
		{
			c_item.innerHTML = c_item.innerHTML + "  " +  c_item.innerHTML;
			double_sized_items[c_item.id] = 1;
		}
		
		c_middle = c_item.offsetWidth / 2;	
		scroll_stop();
		iter = window.setInterval( 'move_left()', 20 );
	}

	if ( 'up' == dir )
	{
		var iCurElement = $(item).attr('id').replace(/[^0-9]{1,}/i, '');

        scroll_stop();
		iter = window.setInterval( function() { move_up(iCurElement) }, 20 );
        document.getElementById( 'dpol_arr_up_' + iCurElement ).style.display='block';
	}

	if ( 'down' == dir )
	{
		var iCurElement = $(item).attr('id').replace(/[^0-9]{1,}/i, '');

        scroll_stop();
		iter = window.setInterval( function() { move_down(iCurElement) }, 20 );
        document.getElementById( 'dpol_arr_down_' + iCurElement ).style.display='block';
	}
}


function scroll_stop()
{
	if ( undefined != window.iter ) {
	    window.clearInterval(iter);
    }    
}

function set_vote( item, val )
{
	var oObject = document.getElementById( item );
    if ( oObject )
        oObject.value = val;
}

function getPoll(sContainer, sAction, sParam, iId) {
	var sPrefix = 'dpol';
	if(typeof(sContainer)=='object' && (sContainer instanceof Array))
		sPrefix = sContainer[0];

	$('#' + sPrefix + '_result_block' + iId).show();
    $('#' + sPrefix + '_back_poll' + iId).hide();

    send_data(sContainer, sAction, sParam, iId);
}

function getPollBlock(sContainer, iBlockId, bViewMode)
{
    var data_source = sPageReceiver + '/get_poll_block/' + iBlockId ;
    if(typeof bViewMode != 'undefined') {
        data_source += '/true';
    }

    getHtmlData(sContainer, data_source);
} 

// array with elements witch we increased to scroll
    double_sized_items = new Array();


// =============================================================================
// End of local part ===========================================================
// =============================================================================