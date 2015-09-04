// jQuery plugin - Dolphin Profile Promo Images

var bProfPromoEnabled = true;
var bEnableSwitching = false;

function PlayPausePromo() {
	if (bEnableSwitching == false) {
		bProfPromoEnabled = false;
		return;
	}
	bProfPromoEnabled = !bProfPromoEnabled;

	if (bProfPromoEnabled) {
		//$("#paused_div").remove();
		$("#pp_hidden_tabs").css("backgroundColor", "transparent");
		$("#pp_hidden_tabs").children().each( function() {
			this.style.display = 'none';
		});
	} else {
		//var el = $('<div style="padding-top:10px;margin-left:10px;position:absolute;bottom:0px;" class="file_field" id="paused_div">PAUSED</div>');
		//$("#pp_hidden_tabs").append(el);
		$("#pp_hidden_tabs").css("backgroundColor", "#FFEEEE");
	}
}

(function($){
	$.fn.dolPromo = function( iInterval, sEnableSwitching ) {
		subTabInitialize = function(menu) {
			var getEls = document.getElementById(menu).getElementsByTagName("LI");
			var getAgn = getEls;

			for (var i=0; i<getEls.length; i++) {
				getEls[i].onclick=function() {
					$("#pp_hidden_tabs").children().each( function() {
						this.style.display = 'none';
					});

					var iCurPos = this.className.match(/([0-9]+)/);
					var hiddenEl = document.getElementById("pp_hidden" + iCurPos[0]);
					if (hiddenEl != undefined)
						hiddenEl.style.display = "block";

					//this.className+=" click";
					//$("#iconsTabsContBottom .tab_lvl1 .outer").removeClass('clicked').attr('id', '');
					//$("#iconsTabsContBottom .tab_lvl1 .outer[name=" + this.attributes['name'].value + "]").attr('id','clicked');;

					SetBottomPaginNum(iCurPos[0]);
				}
			}
		}

		function initializeTabs() {
			subTabInitialize('iconsTabsCont');
			subTabInitialize('iconsTabsContBottom');
			hideScrollPP();
			hideScrollPPBottom();
			runFlashing();
		}

		//hide scrollers if needed
		function hideScrollPP() {
			b = document.getElementById( "iconsTabsCont" );
			s = document.getElementById( "scrollCont" );

			if( !b || !s )
				return false;

			if( b.parentNode.clientWidth >= b.clientWidth ) {
				s.style.display = "none";
				$( '#iconsTabsCont' ).css( { float: 'none', position: 'static', marginRight: 'auto', marginLeft: 'auto' } );
			}
			else
				s.style.display = "block";
		}

		//hide scrollers for bottom if needed
		function hideScrollPPBottom() {
			b = document.getElementById( "iconsTabsContBottom" );
			s = document.getElementById( "scrollContBottom" );

			if( !b || !s )
				return false;

			if( b.parentNode.clientWidth >= b.clientWidth ) {
				s.style.display = "none";
				$( '#iconsTabsContBottom' ).css( { float: 'none', position: 'static', marginRight: 'auto', marginLeft: 'auto' } );
			}
			else
				s.style.display = "block";
		}


		///////////////////////////////////////////////////////////////////////////

		var iInterval = iInterval || 3000; //switching interval in milliseconds
		//var bEnableSwitching = (sEnableSwitching == 'true') ? true : false;
		bEnableSwitching = (sEnableSwitching == 'true') ? true : false;

		//if (bEnableSwitching == false) bProfPromoEnabled = false;

		initializeTabs();

		function runFlashing() {

			function switchThem() {
				if (bProfPromoEnabled) {
					$("#pp_hidden_tabs").children().each( function() {
						this.style.display = 'none';
					});

					if( typeof ePrev != 'undefined' )
						ePrev.fadeOut( 1000 );

					eNext.fadeIn( 1000 );

					sID = eNext.attr("id");
					iID = sID.match(/([0-9]+)/);
					//iID = parseInt(sID);
					SetBottomPaginNum(iID[0]);

					ePrev = eNext;
					eNext = eNext.next( '#pp_hidden_tabs .pp_hidden_el' );

					if( !eNext.length )
						eNext = $( '#pp_hidden_tabs .pp_hidden_el:first' );

				}

				if (bEnableSwitching) {
					setTimeout( switchThem, iInterval );
				}
			}

			var eNext = $( '#pp_hidden_tabs .pp_hidden_el:first' );
			var ePrev;

			switchThem();
		}

		function SetBottomPaginNum(iNum) {

			$("#iconsTabsContBottom .tab_lvl1").children().each( function() {
				$(this).attr('id', '');
			});
			$("#iconsTabsContBottom .tab_lvl1 [name=b_elem" + iNum + "]").attr('id', 'clicked');
			
		}
		
		function trim_jsp( s ) {
			return s.replace( /^\s+/g, '' ).replace( /\s+$/g, '' );
		}

	};
})(jQuery);
