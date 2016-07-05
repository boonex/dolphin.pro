<?php
require_once(BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseFunctions.php');

class BxTemplFunctions extends BxBaseFunctions
{
    /**
     * class constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    function genSiteSearch($sText = '')
    {
        $sContent = parent::genSiteSearch($sText);

        return $GLOBALS['oSysTemplate']->parseHtmlByContent($sContent, array(
            ''
        ));
    }

    function genSiteServiceMenu()
    {
        $bLogged = isLogged();

        $aMenuItem         = array();
        $sMenuPopupId      = '';
        $sMenuPopupContent = '';
        if ($bLogged) {
            bx_import('BxTemplMenuService');
            $oMenu = new BxTemplMenuService();

            if ($oMenu->aMenuInfo['memberID'] != 0) {
                $aProfile = getProfileInfo($oMenu->aMenuInfo['memberID']);
            }

            $sThumbSetting = getParam('sys_member_info_thumb_icon');

            bx_import('BxDolMemberInfo');
            $o         = BxDolMemberInfo::getObjectInstance($sThumbSetting);
            $sThumbUrl = $o ? $o->get($aProfile) : '';

            $o              = BxDolMemberInfo::getObjectInstance($sThumbSetting . '_2x');
            $sThumbTwiceUrl = $o ? $o->get($aProfile) : '';

            if (!$sThumbTwiceUrl) {
                $sThumbTwiceUrl = $sThumbUrl;
            }

            $bThumb    = !empty($sThumbUrl);
            $aMenuItem = array(
                'bx_if:show_fu_thumb_image' => array(
                    'condition' => $bThumb,
                    'content'   => array(
                        'image'    => $sThumbUrl,
                        'image_2x' => $sThumbTwiceUrl,
                    )
                ),
                'bx_if:show_fu_thumb_icon'  => array(
                    'condition' => !$bThumb,
                    'content'   => array()
                ),
                'thumbnail'                 => get_member_icon($oMenu->aMenuInfo['memberID']),
                'title'                     => getNickName($oMenu->aMenuInfo['memberID'])
            );

            $sMenuPopupId      = 'sys-service-menu-' . time();
            $sMenuPopupContent = $this->transBox($oMenu->getCode());
        }

        return $GLOBALS['oSysTemplate']->parseHtmlByName('extra_service_menu_wrapper.html', array(
            'bx_if:show_for_visitor' => array(
                'condition' => !$bLogged,
                'content'   => array()
            ),
            'bx_if:show_for_user'    => array(
                'condition' => $bLogged,
                'content'   => $aMenuItem
            ),
            'menu_popup_id'          => $sMenuPopupId,
            'menu_popup_content'     => $sMenuPopupContent
        ));
    }
}

$oFunctions = new BxTemplFunctions();
