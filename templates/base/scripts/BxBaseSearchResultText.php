<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSearchResult.php');

class BxBaseSearchResultText extends BxBaseSearchResult
{
    function __construct ()
    {
        $this->aPseud = $this->_getPseud();
        parent::__construct();
        bx_import('BxTemplVotingView');

        $this->aConstants['linksTempl'] = $this->isPermalinkEnabled() ? $this->aPermalinks['enabled'] : $this->aPermalinks['disabled'];
    }

    function displaySearchUnit ($aData)
    {
        $sFileLink = $this->getCurrentUrl('file', $aData['id'], $aData['uri']);
        $sCategoryLink = $this->getCurrentUrl('category', $aData['categoryId'], $aData['categoryUri'], array('ownerId'=>$aData['ownerId'], 'ownerName'=>$aData['ownerName']));

        // ownerPic
        $aUnit['ownerPic'] = get_member_icon($aData['ownerId'], 'left');

        // category
        $aUnit['category'] = isset($aData['categoryName']) ? _t('_In') . ' <a href="'.$sCategoryLink.'">'.$aData['categoryName'].'</a>' : '';

        // comment(s)
        $aUnit['comment'] = isset($aData['countComment']) ? '<a href="'.$sFileLink.'">'.$aData['countComment'].' '._t('_comments').'</a>' : '';

        // tag
        if (isset($aData['tag'])) {
            $aTags = explode(',', $aData['tag']);
            foreach ($aTags as $sValue) {
                $sLink = $this->getCurrentUrl('tag', 0, $sValue);
                $aUnit['tag'] .= '<a href="'.$sLink.'">'.$sValue.'</a>, ';
            }
        }
        $aUnit['tag'] .= trim($aUnit['tag'], ', ');

        // rate
        if (!is_null($this->oRate) && $this->oRate->isEnabled())
            $aUnit['rate'] = $this->oRate->getJustVotingElement(0, 0, $aData['voting_rate']);
        else
            $aUnit['rate'] = '';

        // title
        $aUnit['title'] = isset($aData['title']) ? '<a href="'.$sFileLink.'">'.$aData['title'].'</a>': '';

        // when
        $aUnit['when'] = defineTimeInterval($aData['date']);

        // from
        $aUnit['from'] = $aData['ownerId'] !=0 ? _t('_By').': <a href="'.getProfileLink($aData['ownerId']).'">'.$aData['ownerName'].'</a>': _t('_By').': '._t('_Admin');

        // view
        $aUnit['view'] = isset($aData['view']) ? _t("_Views").': '.$aData['view'] : '';

        // body
        $aUnit['body'] = isset($aData['bodyText']) ? process_html_output( strmaxtextlen( strip_tags($aData['bodyText']), 200 ) ) : '';

        return $GLOBALS['oSysTemplate']->parseHtmlByName('browseTextUnit.html', $aUnit, array('{','}'));
    }
}
