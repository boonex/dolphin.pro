<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

use OAuth2\Response;

class BxOAuthAPI
{
    protected $_oModule;
    protected $_oDb;
    public $aAction2Scope = array (
        'me' => 'basic',
        'user' => 'basic',
        'friends' => 'basic',
        'service' => 'basic', // service
    );

    function __construct($oModule)
    {
        $this->_oModule = $oModule;
        $this->_oDb = $oModule->_oDb;
    }
    
    function me($aToken)
    {
        // {"access_token":"ed3cc95a337b6abca37b329ee8ce2ca62b4120cb","client_id":"test","user_id":"37","expires":1449277891,"scope":"basic"}
        
        if (!($aProfileInfo = getProfileInfo($aToken['user_id']))) {
            $this->errorOutput('404', 'not_found', 'Profile was not found');
            return;
        }
    
        $this->output($this->_prepareProfileArray($aProfileInfo, false));
    }

    function user($aToken)
    {
        $iProfileId = (int)bx_get('id');

        if ($iProfileId == $aToken['user_id']) {
            $this->me($aToken);
            return;
        }

        if (!($aProfileInfo = $this->_getProfileInfoWithAccessChecking($iProfileId)))
            return;
        
        $this->output($this->_prepareProfileArray($aProfileInfo, !isAdmin($aToken['user_id'])));
    }

    function friends($aToken)
    {
        $iProfileId = (int)bx_get('id');

        if (!($aProfileInfo = $this->_getProfileInfoWithAccessChecking($iProfileId)))
            return;

        $this->output(array(
            'user_id' => $iProfileId,
            'friends' => getMyFriendsEx($iProfileId),
        ));
    }

    function service($aToken) 
    {
        if (!isAdmin($aToken['user_id'])) {
            $this->errorOutput(403, 'access_denied', 'Only admin can access service endpoint');
            return false;
        }

        bx_login($aToken['user_id'], false, false);

        $sUri = bx_get('uri');
        $sMethod = bx_get('method');

        if (!($aParams = bx_get('params')))
            $aParams = array();
        elseif (is_string($aParams) && preg_match('/^a:[\d+]:\{/', $aParams))
            $aParams = @unserialize($aParams);
        if (!is_array($aParams))
            $aParams = array($aParams);

        if (!($sClass = bx_get('class')))
            $sClass = 'Module';

        if (!BxDolRequest::serviceExists($sUri, $sMethod, $sClass)) {
            $this->errorOutput(404, 'not_found', 'Service was not found');
            return false;
        }

        $mixedRet = BxDolService::call($sUri, $sMethod, $aParams, $sClass);

        $this->output(array(
            'uri' => $sUri,
            'method' => $sMethod,
            'data' => $mixedRet,
        ));
    }

    function errorOutput($iHttpCode, $sError, $sErrorDesc)
    {
        $oReponse = new Response();
        $oReponse->setError($iHttpCode, $sError, $sErrorDesc);
        $oReponse->send();
    }

    function output($a)
    {
        $oReponse = new Response();
        $oReponse->setParameters($a);
        $oReponse->send();
    }

    protected function _getProfileInfoWithAccessChecking ($iProfileId) 
    {
        if (!($aProfileInfo = getProfileInfo($iProfileId))) {
            $this->errorOutput('404', 'not_found', 'Profile was not found');
            return false;
        }
        
        if (!bx_check_profile_visibility($iProfileId, $aToken['user_id'], true)) {
            $this->errorOutput(403, 'access_denied', 'You have no rights to view this user info');
            return false;
        }

        return $aProfileInfo;
    }

    protected function _prepareProfileArray ($aProfileInfo, $bPublicFieldsOnly = true) 
    {
        $aProfileInfo['id'] = $aProfileInfo['ID'];

        if ($bPublicFieldsOnly) {
            $aProfileInfo = array(
                'id' => $aProfileInfo['id'],
            );
        } 
        else {
            unset($aProfileInfo['Password']);
            unset($aProfileInfo['Salt']);
            unset($aProfileInfo['LangID']);
            unset($aProfileInfo['ID']);
            unset($aProfileInfo['Status']);
            unset($aProfileInfo['DateLastLogin']);
            unset($aProfileInfo['DateLastNav']);
            unset($aProfileInfo['Featured']);
            unset($aProfileInfo['Location']);
            unset($aProfileInfo['Keyword']);
            unset($aProfileInfo['Couple']);
            unset($aProfileInfo['Avatar']);
            unset($aProfileInfo['aff_num']);
            unset($aProfileInfo['allow_view_to']);
            $aProfileInfo['email'] = $aProfileInfo['Email'];
        }

        $aProfileInfo['profile_display_name'] = $aProfileInfo['name'] = $GLOBALS['oFunctions']->getUserTitle($aProfileInfo['id']);
        $aProfileInfo['profile_display_info'] = $GLOBALS['oFunctions']->getUserInfo($aProfileInfo['id']);
        $aProfileInfo['profile_link'] = getProfileLink($aProfileInfo['id']);

        if (BxDolRequest::serviceExists('photos', 'profile_photo', 'Search'))
            $aProfileInfo['picture'] = BxDolService::call('photos', 'profile_photo', array($aProfileInfo['id'], 'file'), 'Search');
        else
            $aProfileInfo['picture'] = $GLOBALS['oFunctions']->getMemberAvatar($aProfileInfo['id']);

        return $aProfileInfo;
    }

}
