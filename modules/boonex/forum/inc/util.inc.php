<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

// util functions

/**
 * Output XML or make XSL transformation and output ready HTML
 * @param $code		XML code
 * @param $xsl		file name
 * @param $trans	make xsl transformation or not
 */
function transCheck ($xml, $xsl, $trans, $browser_transform = 0)
{
    global $gConf;

    if (!$xml)
        return;

    if ('server' == $gConf['xsl_mode'] && $trans) {
        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        header("Expires: $now");
        header("Last-Modified: $now");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");

        $xslt = new BxXslTransform ($xml, $xsl, BXXSLTRANSFORM_SF);
        $xslt->setHeader ('Content-Type: text/html; charset=UTF-8');
        $s = $xslt->process ();
        $s = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' . $s;

        $i1 = strpos ($s, '<?xml');
        if (FALSE !== $i1) {
            $i2 = strpos ($s, '?>') + 2;
            echo substr ($s, 0, $i1);
            echo substr ($s, $i2);
        } else {
            echo $s;
        }
    } else {
        header ('Content-Type: application/xml; charset=UTF-8');
        echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
        if ('client' == $gConf['xsl_mode'] && $xsl) {
            echo '<' . '?xml-stylesheet type="text/xsl" href="'.str_replace($gConf['dir']['xsl'],$gConf['url']['xsl'],$xsl).'"?'.'>';
        }
        echo $xml;
    }
}

/**
 * Convert array to XML format
 *
 * @param $arr	array with data
 * @param $tag	main tag <main tag>XML data</main tag>
 * @return XML presentation of data
 */
function array2xml($arr, $tag = false)
{
    $res = '';
    foreach($arr as $k=>$v) {
        if(is_array($v)) {
            if(!is_numeric($k) && trim($k))//
                $res .= count($v) ? '<'.$k.'>'.array2xml($v).'</'.$k.'>' : '<'.$k.'/>';
            elseif($tag)
                $res .= '<'.$tag.'>'.array2xml($v).'</'.$tag.'>';
            else
                $res .= array2xml($v);
        } else {
            if(!is_numeric($k) && trim($k))//
                $res .= strlen(trim($v)) ? '<'.$k.'>'.$v.'</'.$k.'>' : '<'.$k.'/>';
            elseif($tag)
                $res .= '<'.$tag.'>'.$v.'</'.$tag.'>';
            else {
                echo 'Error: array without tag';
                exit;
            }
        }
    }
    return  $res;
}

/**
 * check if magick quotes is disables
 */
function checkMagicQuotes ()
{
    if (0 == get_magic_quotes_gpc()) {
        addSlashesArray ($_COOKIE);
        addSlashesArray ($_GET);
        addSlashesArray ($_POST);
    }
}

/**
 * add slashes to every value of array
 */
function addSlashesArray (&$a)
{
    foreach ($a as $k => $v) {
        if (is_array($v))
            addSlashesArray ($v);
        else
            $a[$k] = addslashes ($v);
    }
}

function prepare_to_db(&$s, $iAllowHTML = 1)
{
    if (1 == $iAllowHTML) {
        cleanPost($s);
        // if html is allowed than we will not run it through process_db_input
        // cuz are using PDO bindings and don't want to run escape on it
    } elseif (-1 == $iAllowHTML) {
        $s = process_db_input($s);
    } else {
        $s = process_db_input($s, BX_TAGS_STRIP);
    }
}

function filter_to_db($s, $iAllowHTML = 0)
{
    if ($iAllowHTML) {
        cleanPost($s);
        // if html is allowed than we will not run it through process_db_input
        // cuz are using PDO bindings and don't want to run escape on it
        return $s;
    } else {
        return process_db_input($s, BX_TAGS_STRIP);
    }
}

/**
 * check html message, remove unknown tags, chech for xhtml errors
 */
function cleanPost (&$s)
{
    if (get_magic_quotes_gpc())
        $s = stripslashes($s);

    $s = clear_xss ($s);
}

function encode_post_text (&$s, $bEncodeSpecialChars = false, $bAutohyperlink = false)
{
    global $gConf;

    if ('server' == $gConf['xsl_mode']) {

    } elseif ('client' == $gConf['xsl_mode']) {

        $s = str_replace (array('&amp;','&gt;','&lt;'), array('&','>','<'), $s);
    }

    if ($bEncodeSpecialChars) {
        $s = htmlspecialchars($s, ENT_COMPAT, 'UTF-8', false);
    }

    if ($bAutohyperlink) {
        //$s = preg_replace('@([\s\n\.,\!\?]{1})(https?://([-\w\.]+)+(:\d+)?([\w/_\-\.]*(\?[^<\s]+)?(#[^<\s]+)?)?)@', '$1<a target="_blank" href="$2">$2</a>', $s);
        //$s = preg_replace('@(\w>|<br />|<br/>)(https?://([-\w\.]+)+(:\d+)?([\w/_\-\.]*(\?[^<\s]+)?(#[^<\s]+)?)?)@', '$1<a target="_blank" href="$2">$2</a>', $s);
        $s = bx_linkify_html($s, 'class="' . BX_DOL_LINK_CLASS . '"');
    }

    $s = "<![CDATA[{$s}]]>";
}

function unicode_urldecode($url)
{
    preg_match_all('/%u([[:alnum:]]{4})/', $url, $a);

    foreach ($a[1] as $uniord) {
        $dec = hexdec($uniord);
        $utf = '';

        if ($dec < 128) {
            $utf = chr($dec);
        } else if ($dec < 2048) {
            $utf = chr(192 + (($dec - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        } else {
            $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
            $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
            $utf .= chr(128 + ($dec % 64));
        }

        $url = str_replace('%u'.$uniord, $utf, $url);
    }

    return urldecode($url);
}

function validate_unicode (&$s)
{
if (function_exists('iconv'))
    $s = iconv("UTF-8","UTF-8//IGNORE",$s);
}

function getConfigParam ($sName)
{
    global $gConf;

    if (!$gConf['params'])
        getConfig ();

    if (!isset($gConf['params']) || !$gConf['params'][$sName])
        return false;

    return $gConf['params'][$sName];
}

function setConfigParam ($sName, $sValue)
{
    global $gConf;

    if (!$gConf['params'])
        getConfig ();

    $gConf['params'][$sName] = $sValue;

    $s = base64_encode(@serialize($gConf['params']));

    $f = fopen($gConf['dir']['config'], 'w');
    if (!$f) return false;
    if (!fwrite($f, $s)) {
        fclose ($f);
        return false;
    }
    fclose ($f);

    return true;
}

function getConfig ()
{
    global $gConf;

    $s = @file_get_contents($gConf['dir']['config']);
    if (!$s) return false;

    $aParams = @unserialize(base64_decode($s));

    if ($aParams && is_array($aParams)) {
        $gConf['params'] = $aParams;
        return true;
    }
    return false;
}

function echo_utf8 ($s)
{
    header ('Content-Type: text/html; charset=UTF-8');
    echo $s;
}

function orca_mkdir_r($dirName, $rights=0755)
{
    bx_mkdir_r($dirName, $rights);
}

function orca_format_bytes ($i)
{
    if ($i > 1024*1024)
        return round($i/1024/1024, 1) . 'M';
    elseif ($i > 1024)
        return round($i/1024, 1) . 'K';
    else
        return $i . 'B';
}

function orca_format_date ($iTimestamp)
{
    return defineTimeInterval($iTimestamp);
}

function orca_build_path ($s)
{
    return substr($s, 0, 1) . '/' . substr($s, 0, 2) . '/' . substr($s, 0, 3) . '/';
}

function orca_mb_replace ($sPattern, $sReplace, $s)
{
    return preg_replace ($sPattern, $sReplace, $s);
}

function orca_mb_len ($s)
{
    if (function_exists('mb_strlen'))
        return mb_strlen ($s);
    else
        return strlen ($s);
}

function orca_mb_substr ($s, $iStart, $iLen)
{
    if (function_exists('mb_substr'))
        return mb_substr ($s, $iStart, $iLen);
    else
        return substr ($s, $iStart, $iLen);
}
