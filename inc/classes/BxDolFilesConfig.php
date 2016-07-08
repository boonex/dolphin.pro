<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('BxDolConfig.php');

class BxDolFilesConfig extends BxDolConfig
{
    var $sPrefix;
    //sys_options parameters
    var $aGlParams;
    // array of possible file's endings
    var $aFilePostfix = array();
    // array of shared file's memberships
    var $aMemActions;

    var $isPermalinkEnabled;
    
    var $aDefaultAlbums;

    var $aUploaders;

    var $aFilesConfig = array();

    /**
     * Constructor
     */
    function __construct ($aModule)
    {
        parent::__construct($aModule);

        $this->sPrefix = 'bx_' . $this->getUri();
        $this->isPermalinkEnabled = getParam($this->sPrefix . '_permalinks') == 'on';

        $this->aDefaultAlbums = array('profile_album_name');
    }

    function initConfig()
    {
        foreach ($this->aFilesConfig as $k => $a)
            $this->aFilePostfix[$k] = $a['postfix'];
    }

    /**
     * Get uploaders array
     * @return array of uploaders
     */
    function getUploaders ()
    {
        if ($this->aUploaders)
            return $this->aUploaders;

        // uploaders list with the following keys:
        //   'title' - uploader title
        //   'action' - uploader action name, action name must be passed with the submitted form as well as 'action' 
        //   'form' - uploader class method to get the uploader form, or service call array to get the form
        //   'handle' - uploader class method to handle uploader form, or service call array to process form upload
        $this->aUploaders = array(
            'html5' => array(
                'title' => '_sys_txt_uploader_html5',
                'action' => 'accept_html5',
                'form' => 'getUploadHtml5File',
                'handle' => 'serviceAcceptHtml5File',
            ),
            'regular' => array(
                'title' => '_' . $this->sPrefix . '_regular',
                'action' => 'accept_upload',
                'form' => 'getUploadFormFile',
                'handle' => 'serviceAcceptFile',
            ),
            'record' => array(
                'title' => '_' . $this->sPrefix . '_record',
                'action' => 'accept_record',
                'form' => 'getRecordFormFile',
                'handle' => 'serviceAcceptRecordFile',
            ),
            'embed' => array(
                'title' => '_' . $this->sPrefix . '_embed',
                'action' => 'accept_embed',
                'form' => 'getEmbedFormFile',
                'handle' => 'serviceAcceptEmbedFile',
            ),
        );

        $oAlert = new BxDolAlerts($this->getMainPrefix(), 'uploaders_init', 0, getLoggedId(), array('uploaders' => &$this->aUploaders, 'config' => $this));
        $oAlert->alert();

        return $this->aUploaders;
    }

    function getUploadersMethods ()
    {
        $aUploaders = $this->getUploaders ();
        $a = array();
        foreach ($aUploaders as $k => $r)
            $a[$k] = $r['form'];
        return $a;
    }

    function getFilesPath ()
    {
        return $this->getHomePath() . 'data/files/';
    }

    function getFilesUrl ()
    {
        return $this->getHomeUrl(). 'data/files/';
    }

    function getGlParam ($sPseud)
    {
        $sName = array_key_exists($sPseud, $this->aGlParams) ? $this->aGlParams[$sPseud] : $this->getMainPrefix() . '_' .$sPseud;
        return getParam($sName);
    }

    function getMainPrefix ()
    {
        return $this->sPrefix;
    }

    function getActionArray ()
    {
        $sPref = '_' . $this->sPrefix . '_admin_';
        return array(
            'action_activate' => array(
                'caption' => $sPref . 'activate',
                'method' => 'adminApproveFile'
            ),
            'action_deactivate' => array(
                'caption' => $sPref . 'deactivate',
                'method' => 'adminDisapproveFile'
            ),
            'action_featured' => array(
                'caption' => $sPref . 'feature',
                'method' => 'adminMakeFeatured'
            ),
            'action_unfeatured' => array(
                'caption' => $sPref . 'unfeature',
                'method' => 'adminMakeUnfeatured'
            ),
            'action_delete' => array(
                'caption' => $sPref . 'delete',
                'method' => '_deleteFile'
            ),
        );
    }

    function getAlbumMainActionsArray ()
    {
        $sPref = 'album_';
        return array(
            $sPref . 'edit' => array('type' => 'submit', 'value' => _t('_Edit')),
            $sPref . 'delete' => array('type' => 'submit', 'value' => _t('_Delete')),
            $sPref . 'organize' => array('type' => 'submit', 'value' => _t('_' . $this->sPrefix . '_organize_objects')),
            $sPref . 'add_objects' => array('type' => 'submit', 'value' => _t('_' . $this->sPrefix . '_add_objects')),
        );
    }

    function getUploaderSwitcher ($sLink = '')
    {
        $aAllUploaders = $this->getAllUploaderArray($sLink);
        $aList = array_values($this->getUploaderList());
        $aChoosen = array();
        foreach ($aAllUploaders as $sKey => $aValue) {
            if (in_array($sKey, $aList))
                $aChoosen[_t($sKey)] = $aValue;
        }
        return $aChoosen;
    }

    function checkAllowedExts ($sExt)
    {
        if (!($sAllowed = $this->getGlParam('allowed_exts')))
            return true;

        $aExts = preg_split('/[\s,;]/', $sAllowed);
        return in_array($sExt, $aExts);
    }

    function checkAllowedExtsByFilename ($sFilename)
    {
        $sExt = pathinfo($sFilename, PATHINFO_EXTENSION);
        return $this->checkAllowedExts(strtolower($sExt));
    }

    function getUploaderList ()
    {        
        $aUploaders = $this->getUploaders();
        $aAllTypes = array_keys($this->getUploaders());
        $sData = getParam($this->sPrefix . '_uploader_switcher');
        if (strlen($sData) > 0)
            $aAllTypes = explode(',', $sData);

        $aItems = array();
        foreach ($aAllTypes as $sValue) {
            if (isset($aUploaders[$sValue]))
                $aItems[$sValue] = $aUploaders[$sValue]['title'];
        }
        return $aItems;
    }

    function getAllUploaderArray ($sLink = '')
    {
        $aUploaders = $this->getUploaders ();
        $a = array();
        foreach ($aUploaders as $k => $r) {
            $a[$r['title']] = array (
                'active' => $_GET['mode'] == $k ? true : false,
                'href' => $sLink . '&mode=' . $k,
            );
        }
        return $a;
    }

    function getDefaultAlbums($bProcessed = false, $aReplacement = array())
    {
    	if(!$bProcessed)
    		return $this->aDefaultAlbums;

		$aResult = array();
    	foreach($this->aDefaultAlbums as $sAlbum)
    		$aResult[] = str_replace(array_keys($aReplacement), array_values($aReplacement), $this->getGlParam($sAlbum));

    	return $aResult;
    }
}
