<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxBaseConfig
{
    var	$PageCompThird_db_num = 10;

    var	$PageExplanation_db_num = 1;

    var	$PageVkiss_db_num = 1; // greet.php

    var	$PageListPop_db_num = 1; // list-pop.php

    var $PageComposeColumnCalculation = 'px'; // calculate page with in: px - pixels, % - percentages

    var	$iProfileViewProgressBar = 67; // width of Votes scale at profilr view page

    var	$iPageGap = 20; // 2 * 10

    var	$popUpWindowWidth = 660;
    var	$popUpWindowHeight = 200;

    var $iQSearchWindowWidth = 400;
    var $iQSearchWindowHeight = 400;

    var $iTagsMinFontSize = 10; // minimal font size of tag
    var $iTagsMaxFontSize = 30; // maximal font size of tag

    var $bAnonymousMode;

    var $bAllowUnicodeInPreg = true; // allow unicode in regular expressions

    var $sPaginateButtonActiveTmpl = '<div class="paginate_btn bx-def-border bx-def-round-corners"><a href="__lnk_url__" title="__lnk_title__" __lnk_on_click__><i class="sys-icon __icon__"></i></a></div>';
    var $sPaginateButtonActiveTmplMobile = ' <span class="bx-sys-mobile-paginate-div sys-bullet"></span> <a href="__lnk_url__" title="__lnk_title__" __lnk_on_click__>__lnk_title__</a> ';
    var $sPaginateButtonInactiveTmpl = '<div class="paginate_btn bx-def-border bx-def-round-corners"><span><i class="sys-icon __icon__"></i></span></div>';
    var $sPaginateLinkActiveTmpl = '<div class="paginate_page not_active bx-def-border bx-def-round-corners"><a href="__lnk_url__" title="__lnk_title__" __lnk_on_click__>__lnk_content__</a></div>';
    var $sPaginateLinkInactiveTmpl = '<div class="paginate_page active bx-def-border bx-def-round-corners"><span>__lnk_content__</span></div>';
    var $sPaginateSortingTmpl = '<div class="ordered_block_select paginate_sorting"><span>__title__</span>&nbsp;<div class="input_wrapper input_wrapper_select bx-def-margin-sec-left clearfix"><select class="form_input_select bx-def-font-inputs" __on_click__>__content__</select></div></div>';

    var $bForceSortCountries = true;

    function __construct($site)
    {
        $this -> bAnonymousMode = getParam('anon_mode');
    }
}
