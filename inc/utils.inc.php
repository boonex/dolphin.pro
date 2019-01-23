<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once("header.inc.php");

bx_import('BxDolModule');

define('BX_DOL_LINK_CLASS', 'bx-link');

define('BX_DOL_LOCALE_TIME', 2);
define('BX_DOL_LOCALE_DATE_SHORT', 4);
define('BX_DOL_LOCALE_DATE', 5);

define('BX_DOL_LOCALE_PHP', 1);
define('BX_DOL_LOCALE_DB', 2);

define('BX_TAGS_NO_ACTION', 0); // default
define('BX_TAGS_STRIP', 1);
define('BX_TAGS_SPECIAL_CHARS', 8);
define('BX_TAGS_VALIDATE', 16);
define('BX_TAGS_STRIP_AND_NL2BR', 32);

define('BX_SLASHES_AUTO', 0); // default
define('BX_SLASHES_ADD', 1);
define('BX_SLASHES_STRIP', 2);
define('BX_SLASHES_NO_ACTION', 3);

define('BX_ESCAPE_STR_AUTO', 0); ///< turn apostropes and quote signs into html special chars, for use in @see bx_js_string and @see bx_html_attribute
define('BX_ESCAPE_STR_APOS', 1); ///< escape apostrophes only, for js strings enclosed in apostrophes, for use in @see bx_js_string and @see bx_html_attribute
define('BX_ESCAPE_STR_QUOTE', 2); ///< escape quotes only, for js strings enclosed in quotes, for use in @see bx_js_string and @see bx_html_attribute

define('BX_URL_RE', "@\b((https?://)|(www\.))(([0-9a-zA-Z_!~*'().&=+$%-]+:)?[0-9a-zA-Z_!~*'().&=+$%-]+\@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-zA-Z_!~*'()-]+\.)*([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((/[0-9a-zA-Z_!~*'().;?:\@&=+$,%#-]+)*/?)@"); ///< regular expression to match URL

/**
 * The following two functions are needed to convert title to uri and back.
 * It usefull when titles are used in URLs, like in Categories and Tags.
 */
function title2uri($sValue)
{
    return str_replace(
        array('&', '/', '\\', '"', '+'),
        array('[and]', '[slash]', '[backslash]', '[quote]', '[plus]'),
        $sValue
    );
}

function uri2title($sValue)
{
    return str_replace(
        array('[and]', '[slash]', '[backslash]', '[quote]', '[plus]'),
        array('&', '/', '\\', '"', '+'),
        $sValue
    );
}

/**
 * Convert date(timestamp) in accordance with requested format code.
 *
 * @param string  $sTimestamp - timestamp
 * @param integer $iCode      - format code
 *                            1(4) - short date format. @see sys_options -> short_date_format_php
 *                            2 - time format. @see sys_options -> time_format_php
 *                            3(5) - long date format. @see sys_options -> date_format_php
 *                            6 - RFC 2822 date format.
 */
function getLocaleDate($sTimestamp = '', $iCode = BX_DOL_LOCALE_DATE_SHORT)
{
    $sFormat = (int)$iCode == 6 ? 'r' : getLocaleFormat($iCode);

    return date($sFormat, $sTimestamp);
}

/**
 * Get data format in accordance with requested format code and format type.
 *
 * @param integer $iCode - format code
 *                       1(4) - short date format. @see sys_options -> short_date_format_php
 *                       2 - time format. @see sys_options -> time_format_php
 *                       3(5) - long date format. @see sys_options -> date_format_php
 *                       6 - RFC 2822 date format.
 * @param integer $iType - format type
 *                       1 - for PHP code.
 *                       2 - for database.
 */
function getLocaleFormat($iCode = BX_DOL_LOCALE_DATE_SHORT, $iType = BX_DOL_LOCALE_PHP)
{
    $sPostfix = (int)$iType == BX_DOL_LOCALE_PHP ? '_php' : '';

    $sResult = '';
    switch ($iCode) {
        case 2:
            $sResult = getParam('time_format' . $sPostfix);
            break;
        case 1:
        case 4:
            $sResult = getParam('short_date_format' . $sPostfix);
            break;
        case 3:
        case 5:
            $sResult = getParam('date_format' . $sPostfix);
            break;
    }

    return $sResult;
}

/**
 * Get UTC/GMT time string in ISO8601 date format from provided unix timestamp
 * @param $iUnixTimestamp - unix timestamp
 * @return ISO8601 formatted date/time string
 */
function bx_time_utc($iUnixTimestamp)
{
    return gmdate(DATE_ISO8601, (int)$iUnixTimestamp);
}

/**
 * Function will check on blocked status;
 *
 * @param  : $iFirstProfile (integer) - first profile's id;
 * @param  : $iSecondProfile (integer) - second profile's id;
 * @return : (boolean) - true if pair will blocked;
 */
function isBlocked($iFirstProfile, $iSecondProfile)
{
    $iFirstProfile  = (int)$iFirstProfile;
    $iSecondProfile = (int)$iSecondProfile;
    $sQuery         = "SELECT COUNT(*) FROM `sys_block_list` WHERE `ID` = {$iFirstProfile} AND `Profile` = {$iSecondProfile}";

    return db_value($sQuery) ? true : false;
}

/*
 * function for work with profile
 */
function is_friends($id1, $id2)
{
    $id1 = (int)$id1;
    $id2 = (int)$id2;
    if ($id1 == 0 || $id2 == 0) {
        return;
    }
    $cnt = db_arr("SELECT SUM(`Check`) AS 'cnt' FROM `sys_friend_list` WHERE `ID`='{$id1}' AND `Profile`='{$id2}' OR `ID`='{$id2}' AND `Profile`='{$id1}'");

    return ($cnt['cnt'] > 0 ? true : false);
}

/*
 * functions for limiting maximal word length (returned from ash).
 */
function WordWrapStr($sString, $iWidth = 25, $sWrapCharacter = '&shy;')
{
    if (empty($sString) || mb_strlen($sString, 'UTF-8') <= $iWidth) {
        return $sString;
    }

    $aSpecialSymbols = array("\r", "\n");
    $aSpecialSymbolsWithSpace = array(" _SLASHR_ ", " _SLASHN_ ");
    $aSpecialSymbolsWithSpace2 = array("_SLASHR_", "_SLASHN_");
    if ($iWidth > 9) {
        $sString = str_replace($aSpecialSymbols, $aSpecialSymbolsWithSpace, $sString);
    } // preserve new line characters

    $aWords  = mb_split("\s", $sString);
    $sResult = ' ';
    foreach ($aWords as $sWord) {

        if (($iWord = mb_strlen($sWord, 'UTF-8')) <= $iWidth || preg_match(BX_URL_RE, $sWord)) {
            if ($iWord > 0) {
                $sResult .= $sWord . ' ';
            }

            continue;
        }

        $iPosition = 0;
        while ($iPosition < $iWord) {
            $sResult .= mb_substr($sWord, $iPosition, $iWidth, 'UTF-8') . $sWrapCharacter;
            $iPosition += $iWidth;
        }
        $sResult .= ' ';
    }

    if ($iWidth > 9) {
        $sResult = str_replace($aSpecialSymbolsWithSpace, $aSpecialSymbols, $sResult);
        $sResult = str_replace($aSpecialSymbolsWithSpace2, $aSpecialSymbols, $sResult);
    }

    return trim($sResult);
}

/*
 * functions for limiting maximal word length
 */
function strmaxwordlen($input, $len = 100)
{
    return $input;
}

/*
 * functions for limiting maximal text length
 */
function strmaxtextlen($sInput, $iMaxLen = 60)
{
    $sTail = '';
    $s     = trim(strip_tags($sInput));
    if (mb_strlen($s) > $iMaxLen) {
        $s     = mb_substr($s, 0, $iMaxLen);
        $sTail = '&#8230;';
    }

    return htmlspecialchars_adv($s) . $sTail;
}

function html2txt($content, $tags = "")
{
    while ($content != strip_tags($content, $tags)) {
        $content = strip_tags($content, $tags);
    }

    return $content;
}

function html_encode($text)
{
    $searcharray = array(
        "'([-_\w\d.]+@[-_\w\d.]+)'",
        "'((?:(?!://).{3}|^.{0,2}))(www\.[-\d\w\.\/]+)'",
        "'(http[s]?:\/\/[-_~\w\d\.\/]+)'"
    );

    $replacearray = array(
        "<a href=\"mailto:\\1\">\\1</a>",
        "\\1http://\\2",
        "<a href=\"\\1\" target=_blank>\\1</a>"
    );

    return preg_replace($searcharray, $replacearray, stripslashes($text));
}

/**
 * functions for input data into database
 *
 * @param array|string $sText
 * @param int          $iStripTags tags parameter:
 *                                 BX_TAGS_STRIP - strip tags
 *                                 BX_TAGS_SPECIAL_CHARS - translate to special html chars (not good to use this, it is better to do such thing during output to browser)
 *                                 BX_TAGS_VALIDATE - validate HTML
 *                                 BX_TAGS_NO_ACTION - do not perform any action with tags
 * @return string
 */
function process_db_input($sText, $iStripTags = 0)
{
    if (is_array($sText)) {
        foreach ($sText as $k => $v) {
            $sText[$k] = process_db_input($v, $iStripTags);
        }

        return $sText;
    }

    $oDb = BxDolDb::getInstance();
    switch ($iStripTags) {
        case BX_TAGS_STRIP_AND_NL2BR:
            return $oDb->escape(nl2br(strip_tags($sText)), false);
        case BX_TAGS_STRIP:
            return $oDb->escape(strip_tags($sText), false);
        case BX_TAGS_SPECIAL_CHARS:
            return $oDb->escape(htmlspecialchars($sText, ENT_QUOTES, 'UTF-8'), false);
        case BX_TAGS_VALIDATE:
            return $oDb->escape(clear_xss($sText), false);
        case BX_TAGS_NO_ACTION:
        default:
            return $oDb->escape($sText, false);
    }
}

/**
 * @deprecated no gpc anymore, so no need for this function
 *
 * function for processing pass data
 *
 * This function cleans the GET/POST/COOKIE data if magic_quotes_gpc() is on
 * for data which should be outputed immediately after submit
 */
function process_pass_data($text, $strip_tags = 0)
{
    if ($strip_tags) {
        $text = strip_tags($text);
    }

    return $text;
}

/*
 * function for output data from database into html
 */
function htmlspecialchars_adv($string)
{
    return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);

    /*
    $patterns = array( "/(?!&#\d{2,};)&/m", "/>/m", "/</m", "/\"/m", "/'/m" );
    $replaces = array( "&amp;", "&gt;", "&lt;", "&quot;", "&#039;" );
    return preg_replace( $patterns, $replaces, $string );
    */
}

function process_text_output($text, $maxwordlen = 100)
{
    return (htmlspecialchars_adv(strmaxwordlen($text, $maxwordlen)));
}

function process_textarea_output($text, $maxwordlen = 100)
{
    return htmlspecialchars_adv(strmaxwordlen($text, $maxwordlen));
}

function process_text_withlinks_output($text, $maxwordlen = 100)
{
    return nl2br(html_encode(htmlspecialchars_adv(strmaxwordlen($text, $maxwordlen))));
}

function process_line_output($text, $maxwordlen = 100)
{
    return htmlspecialchars_adv(strmaxwordlen($text, $maxwordlen));
}

function process_html_output($text, $maxwordlen = 100)
{
    return strmaxwordlen($text, $maxwordlen);
}

/**
 *    Used to construct sturctured arrays in GET or POST data. Supports multidimensional arrays.
 *
 * @param array $Values Specifies values and values names, that should be submitted. Can be multidimensional.
 *
 * @return string    HTML code, which contains <input type="hidden"...> tags with names and values, specified in $Values array.
 */
function ConstructHiddenValues($Values)
{
    /**
     *    Recursive function, processes multidimensional arrays
     *
     * @param string $Name  Full name of array, including all subarrays' names
     *
     * @param array  $Value Array of values, can be multidimensional
     *
     * @return string    Properly consctructed <input type="hidden"...> tags
     */
    function ConstructHiddenSubValues($Name, $Value)
    {
        if (is_array($Value)) {
            $Result = "";
            foreach ($Value as $KeyName => $SubValue) {
                $Result .= ConstructHiddenSubValues("{$Name}[{$KeyName}]", $SubValue);
            }
        } else // Exit recurse
        {
            $Result = "<input type=\"hidden\" name=\"" . htmlspecialchars($Name) . "\" value=\"" . htmlspecialchars($Value) . "\" />\n";
        }

        return $Result;
    }

    /* End of ConstructHiddenSubValues function */

    $Result = '';
    if (is_array($Values)) {
        foreach ($Values as $KeyName => $Value) {
            $Result .= ConstructHiddenSubValues($KeyName, $Value);
        }
    }

    return $Result;
}

/**
 *    Returns HTML/javascript code, which redirects to another URL with passing specified data (through specified method)
 *
 * @param string $ActionURL destination URL
 *
 * @param array  $Params    Parameters to be passed (through GET or POST)
 *
 * @param string $Method    Submit mode. Only two values are valid: 'get' and 'post'
 *
 * @return mixed    Correspondent HTML/javascript code or false, if input data is wrong
 */
function RedirectCode($ActionURL, $Params = null, $Method = "get", $Title = 'Redirect')
{
    if ((strcasecmp(trim($Method), "get") && strcasecmp(trim($Method), "post")) || (trim($ActionURL) == "")) {
        return false;
    }

    ob_start();

    ?>
    <html>
    <head>
        <title><?= $Title ?></title>
    </head>
    <body>
    <form name="RedirectForm" action="<?= htmlspecialchars($ActionURL) ?>" method="<?= $Method ?>">

        <?= ConstructHiddenValues($Params) ?>

    </form>
    <script type="text/javascript">
        <!--
        document.forms['RedirectForm'].submit();
        -->
    </script>
    </body>
    </html>
    <?php

    $Result = ob_get_contents();
    ob_end_clean();

    return $Result;
}

/**
 *    Redirects browser to another URL, passing parameters through POST or GET
 *    Actually just prints code, returned by RedirectCode (see RedirectCode)
 */
function Redirect($ActionURL, $Params = null, $Method = "get", $Title = 'Redirect')
{
    $RedirectCodeValue = RedirectCode($ActionURL, $Params, $Method, $Title);
    if ($RedirectCodeValue !== false) {
        echo $RedirectCodeValue;
    }
}

function isRWAccessible($sFileName)
{
    clearstatcache();
    $perms = fileperms($sFileName);

    return ($perms & 0x0004 && $perms & 0x0002) ? true : false;
}

/**
 * Send email function
 *
 * @param string  $sRecipientEmail - Email where email should be send
 * @param string  $sMailSubject    - subject of the message
 * @param string  $sMailBody       - Body of the message
 * @param integer $iRecipientID    - ID of recipient profile
 * @param array   $aPlus           - Array of additional information
 *
 *
 * @return boolean                        - trie if message was send
 *                                        - false if not
 */
function sendMail(
    $sRecipientEmail,
    $sMailSubject,
    $sMailBody,
    $iRecipientID = 0,
    $aPlus = array(),
    $sEmailFlag = 'html',
    $isDisableAlert = false,
    $bForceSend = false
) {
    global $site;

    if (!$sRecipientEmail || preg_match('/\(2\)$/', $sRecipientEmail)) {
        return false;
    }

    $aRecipientInfo = $iRecipientID ? getProfileInfo($iRecipientID) : array();

    // don't send mail to the user if he/she decided to not receive any site's notifications, unless it is critical emails (like email confirmation)
    if (!$bForceSend) {
        $aRealRecipient = $GLOBALS['MySQL']->getRow("SELECT * FROM `Profiles` WHERE `Email`= ? LIMIT 1",
            [$sRecipientEmail]);
        if ($aRealRecipient && 1 != $aRealRecipient['EmailNotify']) {
            return true;
        }
    }

    $sEmailNotify    = isset($GLOBALS['site']['email_notify']) ? $GLOBALS['site']['email_notify'] : getParam('site_email_notify');
    $sSiteTitle      = isset($GLOBALS['site']['title']) ? $GLOBALS['site']['title'] : getParam('site_title');
    $sMailHeader     = "From: =?UTF-8?B?" . base64_encode($sSiteTitle) . "?= <{$sEmailNotify}>";
    $sMailParameters = "-f{$sEmailNotify}";

    if ($aPlus || $iRecipientID) {
        if (!is_array($aPlus)) {
            $aPlus = array();
        }
        bx_import('BxDolEmailTemplates');
        $oEmailTemplates = new BxDolEmailTemplates();
        $sMailSubject    = $oEmailTemplates->parseContent($sMailSubject, $aPlus, $iRecipientID);
        $sMailBody       = $oEmailTemplates->parseContent($sMailBody, $aPlus, $iRecipientID);
    }

    $sMailSubject = '=?UTF-8?B?' . base64_encode($sMailSubject) . '?=';

    $sMailHeader = "MIME-Version: 1.0\r\n" . $sMailHeader;

    if (!$isDisableAlert && 'on' == getParam('bx_smtp_on')) {
        return BxDolService::call('smtpmailer', 'send', array(
            $sRecipientEmail,
            $sMailSubject,
            $sMailBody,
            $sMailHeader,
            $sMailParameters,
            'html' == $sEmailFlag,
            $aRecipientInfo
        ));
    }

    if ('html' == $sEmailFlag) {
        $sMailHeader    = "Content-type: text/html; charset=UTF-8\r\n" . $sMailHeader;
        $iSendingResult = mail($sRecipientEmail, $sMailSubject, $sMailBody, $sMailHeader, $sMailParameters);
    } else {
        $sMailHeader    = "Content-type: text/plain; charset=UTF-8\r\n" . $sMailHeader;
        $sMailBody      = html2txt($sMailBody);
        $iSendingResult = mail($sRecipientEmail, $sMailSubject, html2txt($sMailBody), $sMailHeader, $sMailParameters);
    }

    if (!$isDisableAlert) {
        //--- create system event
        bx_import('BxDolAlerts');
        $aAlertData = array(
            'email'   => $sRecipientEmail,
            'subject' => $sMailSubject,
            'body'    => $sMailBody,
            'header'  => $sMailHeader,
            'params'  => $sMailParameters,
            'html'    => 'html' == $sEmailFlag ? true : false,
        );

        $oZ = new BxDolAlerts('profile', 'send_mail', $iRecipientID, '', $aAlertData);
        $oZ->alert();
    }

    return $iSendingResult;
}

/*
 * Getting Array with Templates Names
*/

function get_templates_array($isAllParams = false)
{
    $aTempls = array();
    $sPath   = BX_DIRECTORY_PATH_ROOT . 'templates/';
    $sUrl    = BX_DOL_URL_ROOT . 'templates/';

    if (!($handle = opendir($sPath))) {
        return array();
    }

    while (false !== ($sFileName = readdir($handle))) {

        if (!is_dir($sPath . $sFileName) || 0 !== strncmp($sFileName, 'tmpl_', 5)) {
            continue;
        }

        $sTemplName    = substr($sFileName, 5);
        $sTemplVer     = _t('_undefined');
        $sTemplVendor  = _t('_undefined');
        $sTemplDesc    = '';
        $sTemplPreview = 'preview.jpg';
        $sPreviewImg   = false;

        if (file_exists($sPath . $sFileName . '/scripts/BxTemplName.php')) {
            @include($sPath . $sFileName . '/scripts/BxTemplName.php');
        }
        if ($isAllParams && $sTemplPreview && file_exists($sPath . $sFileName . '/images/' . $sTemplPreview)) {
            $sPreviewImg = $sUrl . $sFileName . '/images/' . $sTemplPreview;
        }

        $aTempls[substr($sFileName, 5)] = $isAllParams ? array(
            'name'    => $sTemplName,
            'ver'     => $sTemplVer,
            'vendor'  => $sTemplVendor,
            'desc'    => $sTemplDesc,
            'preview' => $sPreviewImg
        ) : $sTemplName;
    }

    closedir($handle);

    return $aTempls;
}

/*
 * The Function Show a Line with Templates Names
 */

function templates_select_txt()
{
    $templ_choices    = get_templates_array();
    $current_template = (strlen($_GET['skin'])) ? $_GET['skin'] : $_COOKIE['skin'];

    foreach ($templ_choices as $tmpl_key => $tmpl_value) {
        if ($current_template == $tmpl_key) {
            $ReturnResult .= $tmpl_value . ' | ';
        } else {
            $sGetTransfer = bx_encode_url_params($_GET, array('skin'));
            $ReturnResult .= '<a href="' . bx_html_attribute($_SERVER['PHP_SELF']) . '?' . $sGetTransfer . 'skin=' . $tmpl_key . '">' . $tmpl_value . '</a> | ';
        }
    }

    return $ReturnResult;
}

function extFileExists($sFileSrc)
{
    return (file_exists($sFileSrc) && is_file($sFileSrc)) ? true : false;
}

function getVisitorIP($isProxyCheck = true)
{
    if (!$isProxyCheck) {
        return $_SERVER['REMOTE_ADDR'];
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    if ((isset($_SERVER['HTTP_X_FORWARDED_FOR'])) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif ((isset($_SERVER['HTTP_X_REAL_IP'])) && !empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif ((isset($_SERVER['HTTP_CLIENT_IP'])) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!preg_match("/^\d+\.\d+\.\d+\.\d+$/", $ip)) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function genFlag($country)
{
    return '<img src="' . genFlagUrl($country) . '" />';
}

function genFlagUrl($country)
{
    return $GLOBALS['site']['flags'] . strtolower($country) . '.gif';
}

// print debug information ( e.g. arrays )
function echoDbg($what, $desc = '')
{
    if ($desc) {
        echo "<b>$desc:</b> ";
    }
    echo "<pre>";
    print_r($what);
    echo "</pre>\n";
}

function echoDbgLog($mWhat, $sDesc = '', $sFileName = 'debug.log')
{
    global $dir;

    $sCont =
        '--- ' . date('r') . ' (' . BX_DOL_START_TIME . ") ---\n" .
        $sDesc . "\n" .
        print_r($mWhat, true) . "\n\n\n";

    $rFile = fopen($dir['tmp'] . $sFileName, 'a');
    fwrite($rFile, $sCont);
    fclose($rFile);
}

function clear_xss($val)
{
    // HTML Purifier plugin
    global $oHtmlPurifier;
    if (!isset($oHtmlPurifier) && !$GLOBALS['logged']['admin']) {

        require_once(BX_DIRECTORY_PATH_PLUGINS . 'htmlpurifier/HTMLPurifier.standalone.php');

        HTMLPurifier_Bootstrap::registerAutoload();

        $oConfig = HTMLPurifier_Config::createDefault();

        $oConfig->set('Cache.SerializerPath', rtrim(BX_DIRECTORY_PATH_CACHE, '/'));
        $oConfig->set('Cache.SerializerPermissions', 0777);

        $oConfig->set('HTML.SafeObject', 'true');
        $oConfig->set('Output.FlashCompat', 'true');
        $oConfig->set('HTML.FlashAllowFullScreen', 'true');

        if (getParam('sys_antispam_add_nofollow')) {
            $sHost = parse_url(BX_DOL_URL_ROOT, PHP_URL_HOST);
            $oConfig->set('URI.Host', $sHost);
            $oConfig->set('HTML.Nofollow', 'true');
        }

        if ($sSafeIframeRegexp = getParam('sys_safe_iframe_regexp')) {
            $oConfig->set('HTML.SafeIframe', 'true');
            $oConfig->set('URI.SafeIframeRegexp', $sSafeIframeRegexp);
        }

        $oConfig->set('Filter.Custom', array(
            new HTMLPurifier_Filter_LocalMovie(),
            new HTMLPurifier_Filter_YouTube(),
            new HTMLPurifier_Filter_YoutubeIframe(),
            new HTMLPurifier_Filter_AddBxLinksClass()
        ));

        $oConfig->set('HTML.DefinitionID', 'html5-definitions');
        $oConfig->set('HTML.DefinitionRev', 1);
        if ($def = $oConfig->maybeGetRawHTMLDefinition()) {
		    $def->addElement('section', 'Block', 'Flow', 'Common');
		    $def->addElement('nav',     'Block', 'Flow', 'Common');
		    $def->addElement('article', 'Block', 'Flow', 'Common');
		    $def->addElement('aside',   'Block', 'Flow', 'Common');
		    $def->addElement('header',  'Block', 'Flow', 'Common');
		    $def->addElement('footer',  'Block', 'Flow', 'Common');
		    $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
		        'src' => 'URI',
		        'type' => 'Text',
		        'width' => 'Length',
		        'height' => 'Length',
		        'poster' => 'URI',
		        'preload' => 'Enum#auto,metadata,none',
		        'controls' => 'Bool',
		    ));
		    $def->addElement('source', 'Block', 'Flow', 'Common', array(
		        'src' => 'URI',
		        'type' => 'Text',
            ));
            $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		}

        $oHtmlPurifier = new HTMLPurifier($oConfig);
    }

    if (!$GLOBALS['logged']['admin']) {
        $val = $oHtmlPurifier->purify($val);
    }

    $oZ = new BxDolAlerts('system', 'clear_xss', 0, 0,
        array('oHtmlPurifier' => $oHtmlPurifier, 'return_data' => &$val));
    $oZ->alert();

    return $val;
}

function _format_when($iSec, $bShort = false)
{
    $s       = '';
    $sSuffix = $bShort ? '_short' : '';
    if ($iSec >= 0) {
        if ($iSec < 3600) {
            $i = round($iSec / 60);
            $s .= (0 == $i || 1 == $i) ? _t('_just_now') : _t('_x_minutes_ago' . $sSuffix, $i);
        } else {
            if ($iSec < 86400) {
                $i = round($iSec / 60 / 60);
                $s .= ((0 == $i || 1 == $i) && !$bShort) ? _t('_x_hour_ago') : _t('_x_hours_ago' . $sSuffix, $i);
            } else {
                $i = round($iSec / 60 / 60 / 24);
                $s .= (0 == $i || 1 == $i) ? _t('_yesterday') : _t('_x_days_ago' . $sSuffix, $i);
            }
        }
    } else {
        if ($iSec > -3600) {
            $i = round($iSec / 60);
            $s .= (0 == $i || 1 == $i) ? _t('_just_now') : _t('_in_x_minutes' . $sSuffix, -$i);
        } else {
            if ($iSec > -86400) {
                $i = round($iSec / 60 / 60);
                $s .= ((0 == $i || 1 == $i) && !$bShort) ? _t('_in_x_hour') : _t('_in_x_hours' . $sSuffix, -$i);
            } elseif ($iSec < -86400) {
                $i = round($iSec / 60 / 60 / 24);
                $s .= (0 == $i || 1 == $i) ? _t('_tomorrow') : _t('_in_x_days' . $sSuffix, -$i);
            }
        }
    }

    return $s;
}

function _format_time($iSec, $aParams = array())
{
    $sDivider = isset($aParams['divider']) ? $aParams['divider'] : ':';

    $iSec    = (int)$iSec;
    $sFormat = $iSec > 3600 ? 'H' . $sDivider . 'i' . $sDivider . 's' : 'i' . $sDivider . 's';

    return gmdate($sFormat, $iSec);
}

/**
 * Convert timestamp to "ago" date format
 *
 * @param $iTime            date/time stamp in seconds
 * @param $bAutoDateConvert automatically convert dates to full date format instead of "ago" format for old dates (older than 14 days)
 * @param $bShort           use short format for relative time
 * @return formatted date string
 */
function defineTimeInterval($iTime, $bAutoDateConvert = true, $bShort = false)
{
    $iTimeDiff = time() - (int)$iTime;

    if ($bAutoDateConvert && $iTimeDiff > 14 * 24 * 60 * 60) // don't show "ago" dates for more than 14 days
    {
        return getLocaleDate((int)$iTime);
    }

    return _format_when($iTimeDiff, $bShort);
}

function execSqlFile($sFileName)
{
    if (!$f = fopen($sFileName, "r")) {
        return false;
    }

    db_res("SET NAMES 'utf8'");

    $s_sql = "";
    while ($s = fgets($f, 10240)) {
        $s = trim($s); //Utf with BOM only

        if (!strlen($s)) {
            continue;
        }
        if (mb_substr($s, 0, 1) == '#') {
            continue;
        } //pass comments
        if (mb_substr($s, 0, 2) == '--') {
            continue;
        }

        $s_sql .= $s;

        if (mb_substr($s, -1) != ';') {
            continue;
        }

        db_res($s_sql);
        $s_sql = "";
    }

    fclose($f);

    return true;
}

function replace_full_uris($text)
{
    $text = preg_replace_callback('/([\s\n\r]src\=")([^"]+)(")/', 'replace_full_uri', $text);

    return $text;
}

function replace_full_uri($matches)
{
    if (substr($matches[2], 0, 7) != 'http://' and substr($matches[2], 0, 8) != 'https://' and substr($matches[2], 0,
            6) != 'ftp://'
    ) {
        $matches[2] = BX_DOL_URL_ROOT . $matches[2];
    }

    return $matches[1] . $matches[2] . $matches[3];
}

//--------------------------------------- friendly permalinks --------------------------------------//
//------------------------------------------- main functions ---------------------------------------//
function uriGenerate($s, $sTable, $sField, $iMaxLen = 255)
{
    $s = uriFilter($s);

    if (uriCheckUniq($s, $sTable, $sField)) {
        return $s;
    }

    // try to add date

    if (get_mb_len($s) > 240) {
        $s = get_mb_substr($s, 0, 240);
    }

    $s .= '-' . date('Y-m-d');

    if (uriCheckUniq($s, $sTable, $sField)) {
        return $s;
    }

    // try to add number

    for ($i = 0; $i < 999; ++$i) {
        if (uriCheckUniq($s . '-' . $i, $sTable, $sField)) {
            return ($s . '-' . $i);
        }
    }

    return rand(0, 999999999);
}

function uriFilter($s)
{
    if ($GLOBALS['oTemplConfig']->bAllowUnicodeInPreg) {
        $s = get_mb_replace('/[^\pL^\pN]+/u', '-', $s);
    } // unicode characters
    else {
        $s = get_mb_replace('/([^\d^\w]+)/u', '-', $s);
    } // latin characters only

    $s = get_mb_replace('/([-^]+)/', '-', $s);
    $s = get_mb_replace('/([-]+)$/', '', $s); // remove trailing dash
    if (!$s) {
        $s = '-';
    }

    return $s;
}

function uriCheckUniq($s, $sTable, $sField)
{
    return !db_arr("SELECT 1 FROM $sTable WHERE $sField = '$s' LIMIT 1");
}

function get_mb_replace($sPattern, $sReplace, $s)
{
    return preg_replace($sPattern, $sReplace, $s);
}

function get_mb_len($s)
{
    return (function_exists('mb_strlen')) ? mb_strlen($s) : strlen($s);
}

function get_mb_substr($s, $iStart, $iLen)
{
    return (function_exists('mb_substr')) ? mb_substr($s, $iStart, $iLen) : substr($s, $iStart, $iLen);
}

/**
 * Block user IP
 *
 * @param $sIP              mixed
 * @param $iExpirationInSec integer
 * @param $sComment         string
 * @return void
 */
function bx_block_ip($mixedIP, $iExpirationInSec = 86400, $sComment = '')
{
    if (preg_match('/^[0-9]+$/', $mixedIP)) {
        $iIP = $mixedIP;
    } else {
        $iIP = sprintf("%u", ip2long($sIP));
    }

    $iExpirationInSec = time() + (int)$iExpirationInSec;
    $sComment         = process_db_input($sComment, BX_TAGS_STRIP);

    if (!db_value("SELECT ID FROM `sys_ip_list` WHERE `From` = {$iIP} AND `To` = {$iIP} LIMIT 1")) {
        return db_res("INSERT INTO `sys_ip_list` SET `From` = {$iIP}, `To` = {$iIP}, `Type` = 'deny', `LastDT` = {$iExpirationInSec}, `Desc` = '{$sComment}'");
    }

    return false;
}

function bx_is_ip_dns_blacklisted($sCurIP = '', $sType = '')
{
    if (defined('BX_DOL_CRON_EXECUTE')) {
        return false;
    }

    if (!$sCurIP) {
        $sCurIP = getVisitorIP(false);
    }

    if (bx_is_ip_whitelisted()) {
        return false;
    }

    $o = bx_instance('BxDolDNSBlacklists');
    if (BX_DOL_DNSBL_POSITIVE == $o->dnsbl_lookup_ip(BX_DOL_DNSBL_CHAIN_SPAMMERS,
            $sCurIP) && BX_DOL_DNSBL_POSITIVE != $o->dnsbl_lookup_ip(BX_DOL_DNSBL_CHAIN_WHITELIST, $sCurIP)
    ) {
        $o->onPositiveDetection($sCurIP, $sType);

        return true;
    }

    return false;
}

function bx_is_ip_whitelisted($sCurIP = '')
{
    if (defined('BX_DOL_CRON_EXECUTE')) {
        return true;
    }

    $iIPGlobalType = (int)getParam('ipListGlobalType');
    if ($iIPGlobalType != 1 && $iIPGlobalType != 2) // 0 - disabled
    {
        return false;
    }

    if (!$sCurIP) {
        $sCurIP = getVisitorIP();
    }
    $iCurIP    = sprintf("%u", ip2long($sCurIP));
    $iCurrTume = time();

    return db_value("SELECT `ID` FROM `sys_ip_list` WHERE `Type` = 'allow' AND `LastDT` > $iCurrTume AND `From` <= '$iCurIP' AND `To` >= '$iCurIP' LIMIT 1") ? true : false;
}

function bx_is_ip_blocked($sCurIP = '')
{
    if (defined('BX_DOL_CRON_EXECUTE')) {
        return false;
    }

    $iIPGlobalType = (int)getParam('ipListGlobalType');
    if ($iIPGlobalType != 1 && $iIPGlobalType != 2) // 0 - disabled
    {
        return false;
    }

    if (!$sCurIP) {
        $sCurIP = getVisitorIP();
    }
    $iCurIP    = sprintf("%u", ip2long($sCurIP));
    $iCurrTume = time();

    if (bx_is_ip_whitelisted($sCurIP)) {
        return false;
    }

    $isBlocked = db_value("SELECT `ID` FROM `sys_ip_list` WHERE `Type` = 'deny' AND `LastDT` > $iCurrTume AND `From` <= '$iCurIP' AND `To` >= '$iCurIP' LIMIT 1");
    if ($isBlocked) {
        return true;
    }

    // 1 - all allowed except listed
    // 2 - all blocked except listed
    return $iIPGlobalType == 2 ? true : false;
}

/**
 *  spam checking function
 *
 * @param $val
 * @return true if spam detected
 */
function bx_is_spam($val)
{
    if (defined('BX_DOL_CRON_EXECUTE')) {
        return false;
    }

    if (isAdmin()) {
        return false;
    }

    if (bx_is_ip_whitelisted()) {
        return false;
    }

    $bRet = false;
    if ('on' == getParam('sys_uridnsbl_enable')) {
        $oBxDolDNSURIBlacklists = bx_instance('BxDolDNSURIBlacklists');
        if ($oBxDolDNSURIBlacklists->isSpam($val)) {
            $oBxDolDNSURIBlacklists->onPositiveDetection($val);
            $bRet = true;
        }
    }

    if ('on' == getParam('sys_akismet_enable')) {
        $oBxDolAkismet = bx_instance('BxDolAkismet');
        if ($oBxDolAkismet->isSpam($val)) {
            $oBxDolAkismet->onPositiveDetection($val);
            $bRet = true;
        }
    }

    if ($bRet && 'on' == getParam('sys_antispam_report')) {
        bx_import('BxDolEmailTemplates');
        $oEmailTemplates = new BxDolEmailTemplates();
        $aTemplate       = $oEmailTemplates->getTemplate('t_SpamReportAuto', 0);

        $iProfileId = getLoggedId();
        $aPlus      = array(
            'SpammerUrl'      => getProfileLink($iProfileId),
            'SpammerNickName' => getNickName($iProfileId),
            'Page'            => htmlspecialchars_adv($_SERVER['PHP_SELF']),
            'Get'             => print_r($_GET, true),
            'SpamContent'     => htmlspecialchars_adv($val),
        );

        sendMail($GLOBALS['site']['email'], $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus);
    }

    if ($bRet && 'on' == getParam('sys_antispam_block')) {
        return true;
    }

    return false;
}

function getmicrotime()
{
    list($usec, $sec) = explode(" ", microtime());

    return ((float)$usec + (float)$sec);
}

/**
 ** @description : function will create cache file with all SQL queries ;
 ** @return        :
 */
function genSiteStatCache()
{
    $sqlQuery = "SELECT `Name` as `name`,
                        `Title` as `capt`,
                        `UserQuery` as `query`,
                        `UserLink` as `link`,
                        `IconName` as `icon`,
                        `AdminQuery` as `adm_query`,
                           `AdminLink` as `adm_link`
                        FROM `sys_stat_site`
                        ORDER BY `StatOrder` ASC, `ID` ASC";

    $rData = db_res($sqlQuery);

    $sLine = "return array( \n";
    while ($aVal = $rData->fetch()) {
        $sLine .= genSiteStatFile($aVal);
    }
    $sLine = rtrim($sLine, ",\n") . "\n);";

    $aResult = eval($sLine);

    $oCache = $GLOBALS['MySQL']->getDbCacheObject();

    return $oCache->setData($GLOBALS['MySQL']->genDbCacheKey('sys_stat_site'), $aResult);
}

function genSiteStatFile($aVal)
{
    $oMenu = new BxDolMenu();

    $sLink    = $oMenu->getCurrLink($aVal['link']);
    $sAdmLink = $oMenu->getCurrLink($aVal['adm_link']);
    $sLine    = "'{$aVal['name']}'=>array('capt'=>'{$aVal['capt']}', 'query'=>'" . addslashes($aVal['query']) . "', 'link'=>'$sLink', 'icon'=>'{$aVal['icon']}', 'adm_query'=>'" . addslashes($aVal['adm_query']) . "', 'adm_link'=>'$sAdmLink', ),\n";

    return $sLine;
}

function getSiteStatArray()
{
    $oCache = $GLOBALS['MySQL']->getDbCacheObject();
    $aStats = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_stat_site'));
    if ($aStats === null) {
        genSiteStatCache();
        $aStats = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_stat_site'));
    }

    if (!$aStats) {
        $aStats = array();
    }

    return $aStats;
}

/**
 * Function will cute the parameter from received string;
 * remove received parameter from 'GET' query ;
 *
 * @param        : $aExceptNames (string) - name of unnecessary parameter;
 * @return       : cleared string;
 */
function getClearedParam($sExceptParam, $sString)
{
    return preg_replace("/(&amp;|&){$sExceptParam}=([a-z0-9\_\-]{1,})/i", '', $sString);
}

/**
 * import class file, it detect class path by its prefix or module array
 *
 * @param $sClassName - full class name or class postfix in a case of module class
 * @param $aModule    - module array or true to get module array from global variable
 */
function bx_import($sClassName, $aModule = array())
{
    if (class_exists($sClassName)) {
        return;
    }

    if ($aModule) {
        $a = (true === $aModule) ? $GLOBALS['aModule'] : $aModule;
        if (class_exists($a['class_prefix'] . $sClassName)) {
            return;
        }
        require_once(BX_DIRECTORY_PATH_MODULES . $a['path'] . 'classes/' . $a['class_prefix'] . $sClassName . '.php');
    }

    if (0 === strncmp($sClassName, 'BxDol', 5)) {
        require_once(BX_DIRECTORY_PATH_CLASSES . $sClassName . '.php');

        return;
    }
    if (0 === strncmp($sClassName, 'BxBase', 6)) {
        require_once(BX_DIRECTORY_PATH_BASE . 'scripts/' . $sClassName . '.php');

        return;
    }
    if (0 === strncmp($sClassName, 'BxTempl', 7) && !class_exists($sClassName)) {
        if (isset($GLOBALS['iAdminPage']) && (int)$GLOBALS['iAdminPage'] == 1) {
            if (!defined('BX_DOL_TEMPLATE_DEFAULT_CODE')) {
                require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolTemplate.php');
            }
            require_once(BX_DIRECTORY_PATH_ROOT . "templates/tmpl_" . BX_DOL_TEMPLATE_DEFAULT_CODE . "/scripts/" . $sClassName . '.php');
        } else {
            require_once(BX_DIRECTORY_PATH_ROOT . "templates/tmpl_{$GLOBALS['tmpl']}/scripts/" . $sClassName . '.php');
        }

        return;
    }
}

/**
 * Gets an instance of class pathing necessary parameters if it's necessary.
 *
 * @param string $sClassName class name.
 * @param array  $aParams    an array of parameters to be pathed to the constructor of the class.
 * @param array  $aModule    an array with module description. Is used when the requested class is located in some module.
 * @return unknown
 */
function bx_instance($sClassName, $aParams = array(), $aModule = array())
{
    if (isset($GLOBALS['bxDolClasses'][$sClassName])) {
        return $GLOBALS['bxDolClasses'][$sClassName];
    } else {
        bx_import((empty($aModule) ? $sClassName : str_replace($aModule['class_prefix'], '', $sClassName)), $aModule);

        if (empty($aParams)) {
            $GLOBALS['bxDolClasses'][$sClassName] = new $sClassName();
        } else {
            $sParams = "";
            foreach ($aParams as $mixedKey => $mixedValue) {
                $sParams .= "\$aParams[" . $mixedKey . "], ";
            }
            $sParams = substr($sParams, 0, -2);

            $GLOBALS['bxDolClasses'][$sClassName] = eval("return new " . $sClassName . "(" . $sParams . ");");
        }

        return $GLOBALS['bxDolClasses'][$sClassName];
    }
}

/**
 * Escapes string/array ready to pass to js script with filtered symbols like ', " etc
 *
 * @param $mixedInput - string/array which should be filtered
 * @param $iQuoteType - string escaping method: BX_ESCAPE_STR_AUTO(default), BX_ESCAPE_STR_APOS or BX_ESCAPE_STR_QUOTE
 * @return converted string / array
 */
function bx_js_string($mixedInput, $iQuoteType = BX_ESCAPE_STR_AUTO)
{
    $aUnits = array(
        "\n" => "\\n",
        "\r" => "",
    );
    if (BX_ESCAPE_STR_APOS == $iQuoteType) {
        $aUnits["'"]         = "\\'";
        $aUnits['<script']   = "<scr' + 'ipt";
        $aUnits['</script>'] = "</scr' + 'ipt>";
    } elseif (BX_ESCAPE_STR_QUOTE == $iQuoteType) {
        $aUnits['"']         = '\\"';
        $aUnits['<script']   = '<scr" + "ipt';
        $aUnits['</script>'] = '</scr" + "ipt>';
    } else {
        $aUnits['"'] = '&quote;';
        $aUnits["'"] = '&apos;';
        $aUnits["<"] = '&lt;';
        $aUnits[">"] = '&gt;';
    }

    return str_replace(array_keys($aUnits), array_values($aUnits), $mixedInput);
}

/**
 * Return input string/array ready to pass to html attribute with filtered symbols like ', " etc
 *
 * @param mixed $mixedInput - string/array which should be filtered
 * @return converted string / array
 */
function bx_html_attribute($mixedInput)
{
    $aUnits = array(
        "\"" => "&quot;",
        "'"  => "&apos;",
    );

    return str_replace(array_keys($aUnits), array_values($aUnits), $mixedInput);
}

/**
 * Escapes string/array ready to pass to php script with filtered symbols like ', " etc
 *
 * @param mixed $mixedInput - string/array which should be filtered
 * @return converted string / array
 */
function bx_php_string_apos($mixedInput)
{
    return str_replace("'", "\\'", $mixedInput);
}

function bx_php_string_quot($mixedInput)
{
    return str_replace('"', '\\"', $mixedInput);
}

/**
 * Gets file contents by URL.
 *
 * @param string $sFileUrl - file URL to be read.
 * @param array  $aParams  - an array of parameters to be pathed with URL.
 * @return string the file's contents.
 */
function bx_file_get_contents($sFileUrl, $aParams = array(), $sMethod = 'get', $aHeaders = array(), &$sHttpCode = null)
{
    if ('post' != $sMethod) {
        $sFileUrl = bx_append_url_params($sFileUrl, $aParams);
    }

    $sResult = '';
    if (function_exists('curl_init')) {
        $rConnect = curl_init();

        curl_setopt($rConnect, CURLOPT_TIMEOUT, 10);
        curl_setopt($rConnect, CURLOPT_URL, $sFileUrl);
        curl_setopt($rConnect, CURLOPT_HEADER, null === $sHttpCode ? false : true);
        curl_setopt($rConnect, CURLOPT_RETURNTRANSFER, 1);

        if (!ini_get('open_basedir')) {
            curl_setopt($rConnect, CURLOPT_FOLLOWLOCATION, 1);
        }

        if ($aHeaders) {
            curl_setopt($rConnect, CURLOPT_HTTPHEADER, $aHeaders);
        }

        if ('post' == $sMethod) {
            curl_setopt($rConnect, CURLOPT_POST, true);
            curl_setopt($rConnect, CURLOPT_POSTFIELDS, $aParams);
        }

        $sAllCookies = '';
        foreach ($_COOKIE as $sKey => $sValue) {
            $sAllCookies .= $sKey . '=' . $sValue . ';';
        }
        curl_setopt($rConnect, CURLOPT_COOKIE, $sAllCookies);

        $sResult = curl_exec($rConnect);

        if (curl_errno($rConnect) == 60) { // CURLE_SSL_CACERT
            curl_setopt($rConnect, CURLOPT_CAINFO, BX_DIRECTORY_PATH_PLUGINS . 'curl/cacert.pem');
            $sResult = curl_exec($rConnect);
        }

        if (null !== $sHttpCode) {
            $sHttpCode = curl_getinfo($rConnect, CURLINFO_HTTP_CODE);
        }

        curl_close($rConnect);
    } else {
        $sResult = @file_get_contents($sFileUrl);
    }

    return $sResult;
}

/**
 * perform write log into 'tmp/log.txt' (for any debug development)
 *
 * @param $sNewLineText - New line debug text
 */
function writeLog($sNewLineText = 'test')
{
    $sFileName = BX_DIRECTORY_PATH_ROOT . 'tmp/log.txt';

    if (is_writable($sFileName)) {
        if (!$vHandle = fopen($sFileName, 'a')) {
            echo "Unable to open ({$sFileName})";
        }
        if (fwrite($vHandle, $sNewLineText . "\r\n") === false) {
            echo "Unable write to ({$sFileName})";
        }
        fclose($vHandle);

    } else {
        echo "{$sFileName} is not writeable";
    }
}

function getLink($sString, $sUrl)
{
    return '<a href="' . $sUrl . '">' . $sString . '</a> ';
}

function getLinkSet($sLinkString, $sUrlPrefix, $sDivider = ';,', $bUriConvert = false)
{
    $aSet      = preg_split('/[' . $sDivider . ']/', $sLinkString, 0, PREG_SPLIT_NO_EMPTY);
    $sFinalSet = '';

    foreach ($aSet as $sKey) {
        $sLink = $sUrlPrefix . urlencode($bUriConvert ? title2uri($sKey) : $sKey);
        $sFinalSet .= '<a href="' . $sUrlPrefix . urlencode(title2uri(trim($sKey))) . '">' . $sKey . '</a> ';
    }

    return trim($sFinalSet, ' ');
}

function getRelatedWords(&$aInfo)
{
    $sString = implode(' ', $aInfo);
    $aRes    = array_unique(explode(' ', $sString));
    $sString = implode(' ', $aRes);

    return addslashes($sString);
}

function getSiteInfo($sSourceUrl, $aProcessAdditionalTags = array())
{
    $aResult  = array();
    $sContent = bx_file_get_contents($sSourceUrl);

    if ($sContent) {
        $sCharset = '';
        preg_match("/<meta.+charset=([A-Za-z0-9-]+).+>/i", $sContent, $aMatch);
        if (isset($aMatch[1])) {
            $sCharset = $aMatch[1];
        }

        if (preg_match("/<title[^>]*>(.*)<\/title>/i", $sContent, $aMatch)) {
            $aResult['title'] = $aMatch[1];
        } else {
            $aResult['title'] = parse_url($sSourceUrl, PHP_URL_HOST);
        }

        $aResult['description'] = bx_parse_html_tag($sContent, 'meta', 'name', 'description', 'content', $sCharset);
        $aResult['keywords']    = bx_parse_html_tag($sContent, 'meta', 'name', 'keywords', 'content', $sCharset);

        if ($aProcessAdditionalTags) {

            foreach ($aProcessAdditionalTags as $k => $a) {
                $aResult[$k] = bx_parse_html_tag(
                    $sContent,
                    isset($a['tag']) ? $a['tag'] : 'meta',
                    isset($a['name_attr']) ? $a['name_attr'] : 'itemprop',
                    isset($a['name']) ? $a['name'] : $k,
                    isset($a['content_attr']) ? $a['content_attr'] : 'content',
                    $sCharset);
            }

        }
    }

    return $aResult;
}

function bx_parse_html_tag($sContent, $sTag, $sAttrNameName, $sAttrNameValue, $sAttrContentName, $sCharset = false)
{
    if (!preg_match("/<{$sTag}\s+{$sAttrNameName}[='\" ]+{$sAttrNameValue}['\"]\s+{$sAttrContentName}[='\" ]+([^'>\"]*)['\"][^>]*>/i",
            $sContent, $aMatch) || !isset($aMatch[1])
    ) {
        preg_match("/<{$sTag}\s+{$sAttrContentName}[='\" ]+([^'>\"]*)['\"]\s+{$sAttrNameName}[='\" ]+{$sAttrNameValue}['\"][^>]*>/i",
            $sContent, $aMatch);
    }

    $s = isset($aMatch[1]) ? $aMatch[1] : '';

    if ($s && $sCharset) {
        $s = mb_convert_encoding($s, 'UTF-8', $sCharset);
    }

    return $s;
}

/**
 * Parse time duration according to ISO 8601
 *
 * @return number of seconds
 */
function bx_parse_time_duration($sContent)
{
    if (!$sContent || !is_string($sContent) || 'P' != strtoupper($sContent[0])) {
        return false;
    }

    $a      = array('D' => 86400, 'H' => 3600, 'M' => '60', 'S' => 1);
    $iTotal = 0;
    foreach ($a as $sLetter => $iSec) {
        if (preg_match('/(\d+)[' . $sLetter . ']{1}/i', $sContent, $aMatch) && $aMatch[1]) {
            $iTotal += (int)$aMatch[1] * $iSec;
        }
    }

    return $iTotal;
}

// simple comparator for strings etc
function simple_cmp($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
}

/**
 * @param int  $bytes
 * @param bool $shorter
 * @return string
 */
function format_bytes($bytes, $shorter = false)
{
    $units = [
        true  => [
            'GB'    => 'G',
            'MB'    => 'M',
            'KB'    => 'K',
            'bytes' => 'B',
            'byte'  => 'B'
        ],
        false => [
            'GB'    => ' GB',
            'MB'    => ' MB',
            'KB'    => ' KB',
            'bytes' => ' bytes',
            'byte'  => ' byte'
        ]
    ];

    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . $units[$shorter]['GB'];
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . $units[$shorter]['MB'];
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . $units[$shorter]['KB'];
    } elseif ($bytes > 1) {
        $bytes = $bytes . $units[$shorter]['bytes'];
    } elseif ($bytes == 1) {
        $bytes = $bytes . $units[$shorter]['byte'];
    } else {
        $bytes = '0' . $units[$shorter]['bytes'];
    }

    return $bytes;
}

// calculation ini_get('upload_max_filesize') in bytes as example
function return_bytes($val)
{
    $val = trim($val);
    if (strlen($val) < 2) {
        return $val;
    }

    $last = strtolower($val{strlen($val) - 1});

    $val = (int)$val;
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'k':
            $val *= 1024;
            break;
        case 'm':
            $val *= 1024 * 1024;
            break;
        case 'g':
            $val *= 1024 * 1024 * 1024;
            break;
    }

    return $val;
}

// Generate Random Password
function genRndPwd($iLength = 8, $bSpecialCharacters = true)
{
    $sPassword = '';
    $sChars    = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";

    if ($bSpecialCharacters === true) {
        $sChars .= "!?=/&+,.";
    }

    srand((double)microtime() * 1000000);
    for ($i = 0; $i < $iLength; $i++) {
        $x = mt_rand(0, strlen($sChars) - 1);
        $sPassword .= $sChars{$x};
    }

    return $sPassword;
}

// Generate Random Salt for Password encryption
function genRndSalt()
{
    return genRndPwd(8, true);
}

// Encrypt User Password
function encryptUserPwd($sPwd, $sSalt)
{
    return sha1(md5($sPwd) . $sSalt);
}

// Advanced stripslashes. Strips strings and arrays
function stripslashes_adv($s)
{
    if (is_string($s)) {
        return stripslashes($s);
    } elseif (is_array($s)) {
        foreach ($s as $k => $v) {
            $s[$k] = stripslashes($v);
        }

        return $s;
    } else {
        return $s;
    }
}

function bx_get($sName)
{
    if (isset($_GET[$sName])) {
        return $_GET[$sName];
    } elseif (isset($_POST[$sName])) {
        return $_POST[$sName];
    } else {
        return false;
    }
}

function bx_encode_url_params($a, $aExcludeKeys = array(), $aOnlyKeys = false)
{
    $s = '';
    foreach ($a as $sKey => $sVal) {
        if (in_array($sKey, $aExcludeKeys)) {
            continue;
        }
        if (false !== $aOnlyKeys && !in_array($sKey, $aOnlyKeys)) {
            continue;
        }
        if (is_array($sVal)) {
            foreach ($sVal as $sSubVal) {
                $s .= rawurlencode($sKey) . '[]=' . rawurlencode(is_array($sSubVal) ? 'array' : $sSubVal) . '&';
            }
        } else {
            $s .= rawurlencode($sKey) . '=' . rawurlencode($sVal) . '&';
        }
    }

    return $s;
}

function bx_append_url_params($sUrl, $mixedParams)
{
    $sParams = false == strpos($sUrl, '?') ? '?' : '&';

    if (is_array($mixedParams)) {
        foreach ($mixedParams as $sKey => $sValue) {
            $sParams .= $sKey . '=' . $sValue . '&';
        }
        $sParams = substr($sParams, 0, -1);
    } else {
        $sParams .= $mixedParams;
    }

    return $sUrl . $sParams;
}

function bx_rrmdir($directory)
{
    if (substr($directory, -1) == "/") {
        $directory = substr($directory, 0, -1);
    }

    if (!file_exists($directory) || !is_dir($directory)) {
        return false;
    } elseif (!is_readable($directory)) {
        return false;
    }

    if (!($directoryHandle = opendir($directory))) {
        return false;
    }

    while ($contents = readdir($directoryHandle)) {
        if ($contents != '.' && $contents != '..') {
            $path = $directory . "/" . $contents;

            if (is_dir($path)) {
                bx_rrmdir($path);
            } else {
                unlink($path);
            }
        }
    }

    closedir($directoryHandle);

    if (!rmdir($directory)) {
        return false;
    }

    return true;
}

function bx_clear_folder($sPath, $aExts = array())
{
    if (substr($$sPath, -1) == "/") {
        $sPath = substr($sPath, 0, -1);
    }

    if (!file_exists($sPath) || !is_dir($sPath)) {
        return false;
    } elseif (!is_readable($sPath)) {
        return false;
    }

    if (!($h = opendir($sPath))) {
        return false;
    }

    while ($sFile = readdir($h)) {
        if ('.' == $sFile || '..' == $sFile) {
            continue;
        }

        $sFullPath = $sPath . '/' . $sFile;

        if (is_dir($sFullPath)) {
            continue;
        }

        if (!$aExts || (($sExt = pathinfo($sFullPath, PATHINFO_EXTENSION)) && in_array($sExt, $aExts))) {
            @unlink($sFullPath);
        }
    }

    closedir($h);

    return true;
}

function bx_ltrim_str($sString, $sPrefix, $sReplace = '')
{
    if ($sReplace && substr($sString, 0, strlen($sReplace)) == $sReplace) {
        return $sString;
    }
    if (substr($sString, 0, strlen($sPrefix)) == $sPrefix) {
        return $sReplace . substr($sString, strlen($sPrefix));
    }

    return $sString;
}

function bx_member_ip_store($iMemberId, $sIP = false)
{
    if (getParam('enable_member_store_ip') != 'on') {
        return false;
    }

    $sCurLongIP = sprintf("%u", ip2long($sIP ? $sIP : getVisitorIP()));

    return db_res("INSERT INTO `sys_ip_members_visits` SET `MemberID` = " . (int)$iMemberId . ", `From` = '" . $sCurLongIP . "', `DateTime` = NOW()");
}

function bx_member_ip_get_last($iMemberId)
{
    $sLongIP = db_value("SELECT `From` FROM `sys_ip_members_visits` WHERE `MemberID` = " . (int)$iMemberId . " ORDER BY `DateTime` DESC");

    return long2ip($sLongIP);
}

/**
 * Show HTTP 503 service unavailable error and exit
 */
function bx_show_service_unavailable_error_and_exit($sMsg = false, $iRetryAfter = 86400)
{
    header('HTTP/1.0 503 Service Unavailable', true, 503);
    header('Retry-After: 600');
    echo $sMsg ? $sMsg : 'Service temporarily unavailable';
    exit;
}

function bx_mkdir_r($sDirName, $rights = 0777)
{
    $sDirName = bx_ltrim_str($sDirName, BX_DIRECTORY_PATH_ROOT);
    $aDirs    = explode('/', $sDirName);
    $sDir     = '';
    foreach ($aDirs as $sPart) {
        $sDir .= $sPart . '/';
        if (!is_dir(BX_DIRECTORY_PATH_ROOT . $sDir) && strlen(BX_DIRECTORY_PATH_ROOT . $sDir) > 0 && !file_exists(BX_DIRECTORY_PATH_ROOT . $sDir)) {
            if (!mkdir(BX_DIRECTORY_PATH_ROOT . $sDir, $rights)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Returns current site protocol http:// or https://
 */
function bx_proto($sUrl = BX_DOL_URL_ROOT)
{
    return 0 === strncmp('https', $sUrl, 5) ? 'https' : 'http';
}

/**
 * Wrap in A tag links in TEXT string
 *
 * @param $sHtmlOrig - text string without tags
 * @param $sAttrs    - attributes string to add to the added A tag
 * @return string where all links are wrapped in A tag
 */
function bx_linkify($text, $sAttrs = '', $bHtmlSpecialChars = false)
{
    if ($bHtmlSpecialChars) {
        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
    }

    preg_match_all(BX_URL_RE, $text, $matches, PREG_OFFSET_CAPTURE);

    $matches = $matches[0];

    if ($i = count($matches)) {
        $bAddNofollow = getParam('sys_antispam_add_nofollow') == 'on';
    }

    while ($i--) {
        $url = $matches[$i][0];
        if (!preg_match('@^https?://@', $url)) {
            $url = 'http://' . $url;
        }

        if (strncmp(BX_DOL_URL_ROOT, $url, strlen(BX_DOL_URL_ROOT)) !== 0) {
            if (false === stripos($sAttrs, 'target="_blank"'))
                $sAttrs .= ' target="_blank" ';
            if ($bAddNofollow && false === stripos($sAttrs, 'rel="nofollow"'))
                $sAttrs .= ' rel="nofollow" ';
        }

        $text = substr_replace($text, '<a ' . $sAttrs . ' href="' . $url . '">' . $matches[$i][0] . '</a>',
            $matches[$i][1], strlen($matches[$i][0]));
    }

    return $text;
}

/**
 * Wrap in A tag links in HTML string, which aren't wrapped in A tag yet
 *
 * @param $sHtmlOrig - HTML string
 * @param $sAttrs    - attributes string to add to the added A tag
 * @return modified HTML string, in case of errror original string is returned
 */
function bx_linkify_html($sHtmlOrig, $sAttrs = '')
{
    if (!trim($sHtmlOrig)) {
        return $sHtmlOrig;
    }

    $sId = 'bx-linkify-' . md5(microtime());
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8"><div id="' . $sId . '">' . $sHtmlOrig . '</div>');
    $xpath = new DOMXpath($dom);

    foreach ($xpath->query('//text()') as $text) {
        $frag = $dom->createDocumentFragment();
        $frag->appendXML(bx_linkify($text->nodeValue, $sAttrs, true));
        $text->parentNode->replaceChild($frag, $text);
    }

    if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
        $s = $dom->saveHTML($dom->getElementById($sId));
    } else {
        $s = $dom->saveXML($dom->getElementById($sId), LIBXML_NOEMPTYTAG);
    }

    if (false === $s) // in case of error return original string
    {
        return $sHtmlOrig;
    }

    if (false !== ($iPos = mb_strpos($s, '<html><body>')) && $iPos < mb_strpos($s, $sId)) {
        $s = mb_substr($s, $iPos + 12, -15);
    } // strip <html><body> tags and everything before them

    return mb_substr($s, 54, -6); // strip added tags
}

/**
 * Transform string to method name string, for example it changes 'some_method' string to 'SomeMethod' string
 *
 * @param string where words are separated with underscore
 * @return string where every word begins with capital letter
 */
function bx_gen_method_name($s, $sWordsDelimiter = '_')
{
    return str_replace(' ', '', ucwords(str_replace($sWordsDelimiter, ' ', $s)));
}

/**
 * Returns a field from $_POST array if it exists
 * To avoid having to do "if(isset($_POST['field']) && $_POST['field'])" multiple times
 *
 * @param $sField
 * @return string
 */
function getPostFieldIfSet($sField)
{
    return (!isset($_POST[$sField])) ? null : $_POST[$sField];
}

/**
 * Returns a field from $_GET array if it exists
 * To avoid having to do "if(isset($_GET['field']) && $_GET['field'])" multiple times
 *
 * @param $sField
 * @return string
 */
function getGetFieldIfSet($sField)
{
    return (!isset($_GET[$sField])) ? null : $_GET[$sField];
}
