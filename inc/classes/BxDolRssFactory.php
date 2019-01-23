<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolRssFactory
{
    // default constructor of factory pattern
    function __construct() {}

    /*
    * params is:
    * 1. $aRssData, fields:
        'UnitID'
        'OwnerID'
        'UnitTitle'
        'UnitLink'
        'UnitDesc'
        'UnitDateTimeUTS'
        'UnitIcon'
    */
    function GenRssByData($aRssData, $sUnitTitleC, $sMainLink)
    {
        return $this->GenRssByCustomData($aRssData, $sUnitTitleC, $sMainLink, array(
            'Guid' => 'UnitLink',
            'Link' => 'UnitLink',
            'Title' => 'UnitTitle',
            'DateTimeUTS' => 'UnitDateTimeUTS',
            'Desc' => 'UnitDesc',
            'Image' => 'UnitIcon'
        ));
    }

    /**
     * generate rss feed using any custom data fields
     * but you need to describe fields in $aFields array
     *
     * Required fileds:
     *  Link
     *  Title
     *  DateTimeUTS
     *  Desc
     *
     * Optional fields:
     *  Photo
     */
    function GenRssByCustomData($aRssData, $sUnitTitleC, $sMainLink, $aFields, $sImage = '', $iPID = 0)
    {
        global $site;

        $sRSSLast = '';
        if(!empty($aRssData) && is_array($aRssData)) {
            reset($aRssData);
            $aUnitFirst = current($aRssData);

            $sRSSLast = bx_time_utc($aUnitFirst[$aFields['DateTimeUTS']]);
        }

        if ($iPID > 0)
            $aPIDOwnerInfo = getProfileInfo($iPID);

        $iUnitLimitChars = 2000;//(int)getParam('max_blog_preview');
        $sUnitRSSFeed = '';
        if ($aRssData) {
            $sTxtReadMore = _t('_Read more');

            foreach ($aRssData as $aUnitInfo) {
                $sUnitUrl = $aUnitInfo[$aFields['Link']];
                $sUnitGuid = $aUnitInfo[$aFields['Guid']];

                $sUnitTitle = strip_tags($aUnitInfo[$aFields['Title']]);
                $sUnitDate = bx_time_utc($aUnitInfo[$aFields['DateTimeUTS']]);

                $sUnitDesc = '';
                if(isset($aFields['Desc']) && !empty($aUnitInfo[$aFields['Desc']])) {
                    $sLinkMore = '';
                    if ( strlen( $aUnitInfo[$aFields['Desc']]) > $iUnitLimitChars )
                        $sLinkMore = "... <a href=\"".$sUnitUrl."\">" . $sTxtReadMore . "</a>";

                    $sUnitDesc = "<p>" . mb_substr(strip_tags($aUnitInfo[$aFields['Desc']]), 0, $iUnitLimitChars) . $sLinkMore . "</p>";
                }

                if(isset($aFields['Image']) && !empty($aUnitInfo[$aFields['Image']]))
                    $sUnitDesc .= "<img src=\"" . $aUnitInfo[$aFields['Image']] . "\" />";

                $sUnitRSSFeed .= "<item><title><![CDATA[{$sUnitTitle}]]></title><link><![CDATA[{$sUnitUrl}]]></link><guid><![CDATA[{$sUnitGuid}]]></guid><description><![CDATA[{$sUnitDesc}]]></description><pubDate>{$sUnitDate}</pubDate></item>";
            }
        }

        $sRSSTitle = _t('_RSS_Feed_Title_Common', $sUnitTitleC);
        if ($iPID > 0) {
            $sRSSTitle = _t('_RSS_Feed_Title_Profile', $aPIDOwnerInfo['NickName'], $sUnitTitleC);
        }

        if(substr($sMainLink, 0, 7) != 'http://' && substr($sMainLink, 0, 8) != 'https://')
            $sMainLink = BX_DOL_URL_ROOT . $sMainLink;

        $sRSSImage = '';
        if ($sImage) {
            $sRSSImage = "<image><url>{$sImage}</url><title>{$sRSSTitle}</title><link>{$sMainLink}</link></image>";
        }

        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><rss version=\"2.0\"><channel><title>{$sRSSTitle}</title><link><![CDATA[{$sMainLink}]]></link><description>{$sRSSTitle}</description><lastBuildDate>{$sRSSLast}</lastBuildDate>{$sRSSImage}{$sUnitRSSFeed}</channel></rss>";
    }

    function SetRssHeader()
    {
        header('Content-Type: text/xml; charset=UTF-8');
    }
}
