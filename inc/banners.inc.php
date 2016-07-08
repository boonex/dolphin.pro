<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once("header.inc.php");
$bann_click_url = BX_DOL_URL_ROOT . "click.php";

function banner_put_nv($Position, $Track = 1)
{
    global $bann_click_url;

    $out = "";

    $query = "SELECT * FROM `sys_banners` WHERE `Active` <> 0 AND `campaign_start` <= NOW() AND `campaign_end` >= NOW() ";

    switch($Position) {
        case 1:
        case 2:
        case 3:
        case 4:
            $query .= " AND `Position` LIKE '%{$Position}%' ";
            break;
        default:
            return '';
    }

    $query .= "ORDER BY RAND() LIMIT 1";

    $arr = db_arr( $query );

    if ( empty($arr[0]) )
        return '';

    switch ($Position) {
        case 2:
            $hshift = $arr['lhshift'];
            $vshift = $arr['lvshift'];
            break;
        case 3:
            $hshift = $arr['rhshift'];
            $vshift = $arr['rvshift'];
            break;
    }

    $arr['Text'] = html_entity_decode($arr['Text']);

    $sLinkWrapper = $arr['Url'] ? "<a target=\"_blank\" href=\"{$bann_click_url}?{$arr['ID']}\" onmouseout=\"ce()\" onfocus=\"ss('{$arr['Url']}')\" onmouseover=\"return ss('{$arr['Url']}')\">{$arr['Text']}</a><br />" : $arr['Text'];

    if( $Position == 2 || $Position == 3 ) {
        $sPosition = ($Position == 2) ? "left:" : "right:";

$out .= <<<EOF
<div style="position:relative; margin:0; padding:0; width:1px; height:1px">
    <div style="position:absolute; {$sPosition}{$hshift}px; top:{$vshift}px; z-index:60">
        {$sLinkWrapper}
    </div>
</div>
EOF;

    } else {
        $out .= '<table width="100%" style="padding: 10px 0px 10px 0px;" align="center">' . "\n";
        $out .= <<<EOF
    <tr>
        <td align="center">
            {$sLinkWrapper}
        </td>
    </tr>
</table>
EOF;
    }

    if ( $Track ) {
        db_res("INSERT INTO `sys_banners_shows` SET `ID` = {$arr['ID']}, `Date` = '".time()."', `IP` = '". $_SERVER['REMOTE_ADDR'] ."'", 0);
    }

    switch($Position) {
        /*case 1:
            $out = '' . $out . '';
            break;*/
        case 2:
            $out = '<div style="position:absolute;top:0px;left:0px;width:1px;height:1px;">' . $out . '</div>';
            break;
        case 3:
            $out = '<div style="position:absolute;top:0px;right:0px;width:1px;height:1px;">' . $out . '</div>';
            break;
        /*case 4:
            $out = '' . $out . '';
            break;*/
    }
    return $out;
}

function banner_put($ID = 0, $Track = 1)
{
    global $bann_click_url;

    if ( !$ID ) {
        // Get only banners that are active and for which promotion period has not expired.
        $bann_arr = db_arr("SELECT `ID`, `Url`, `Text` FROM `sys_banners` WHERE `Active` <> 0 AND `campaign_start` <= NOW() AND `campaign_end` >= NOW() ORDER BY RAND() LIMIT 1");
    } else {
        $bann_arr = db_arr("SELECT `ID`, `Url`, `Text` FROM `sys_banners` WHERE `ID` = $ID LIMIT 1");
    }
    if ( !$bann_arr )
        return "";

    if ( $Track ) {
        db_res("INSERT INTO `sys_banners_shows` SET `ID` = {$bann_arr['ID']}, `Date` = '".time()."', `IP` = '". $_SERVER['REMOTE_ADDR']. "'", 0);
    }

    $bann_arr['Text'] = html_entity_decode($bann_arr['Text']);
    $sOutputCode = $bann_arr['Url'] ? "<a target=\"_blank\" href=\"{$bann_click_url}?{$bann_arr['ID']}\" onmouseout=\"ce()\" onfocus=\"ss('{$bann_arr['Url']}')\" onmouseover=\"return ss('{$bann_arr['Url']}')\">{$bann_arr['Text']}</a>" : $bann_arr['Text'];

    return $sOutputCode;
}
