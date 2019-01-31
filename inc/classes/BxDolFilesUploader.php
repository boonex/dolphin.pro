<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolTemplate.php');

define('BX_DOL_UPLOADER_EP_PREFIX', 'extra_param_');

class BxDolFilesUploader extends BxDolTemplate
{
    var $_iOwnerId;
    var $_sJsPostObject;
    var $sWorkingFile;
    var $_aExtras;

    var $sSendFileInfoFormCaption;
    var $iMaxFilesize; //max accepting filesize (in bytes) 
    var $sAcceptMimeType = '*'; // accept file mime type for html5 uploader, filter files by this type when choosing files from the filesystem
    var $bImageAutoRotate = 0; // autorotate image for html5 uploader

    var $sUploadTypeNC; // Common
    var $sUploadTypeLC; // common

    var $sTempFilename; // uploaded real filename, used as temp name

    var $oModule;

    // constructor
    function __construct($sUploadTypeNC = 'Common')
    {
        parent::__construct();

        $this->sTempFilename = '';

        $this->sUploadTypeNC = $sUploadTypeNC;
        $this->sUploadTypeLC = strtolower($this->sUploadTypeNC);

        $this->_iOwnerId = $this->_getAuthorId();

        $this->_sJsPostObject           = 'o' . $this->sUploadTypeNC . 'Upload';
        $this->sSendFileInfoFormCaption = '';

        $GLOBALS['oSysTemplate']->addJsTranslation(array(
            '_bx_' . $this->sUploadTypeLC . 's_val_title_err',
            '_bx_' . $this->sUploadTypeLC . 's_val_descr_err'
        ));

        //--- Get Extras ---//
        $this->_aExtras = array();
        if (!empty($_POST)) {
            $this->_aExtras = $this->_getExtraParams($_POST);
        }

        $this->iMaxFilesize = min(return_bytes(ini_get('upload_max_filesize')),
            return_bytes(ini_get('post_max_size'))); //max allowed from php.ini
    }

    function _addHidden($sPostType = "photo", $sContentType = "upload", $sAction = "post", $iIndex = 1)
    {
        $aResult = array(
            'UploadOwnerId'     => array(
                'type'  => 'hidden',
                'name'  => 'UploadOwnerId',
                'value' => $this->_iOwnerId,
            ),
            'UploadPostAction'  => array(
                'type'  => 'hidden',
                'name'  => 'UploadPostAction',
                'value' => $sAction,
            ),
            'UploadPostType'    => array(
                'type'  => 'hidden',
                'name'  => 'UploadPostType',
                'value' => $sPostType,
            ),
            'UploadContentType' => array(
                'type'  => 'hidden',
                'name'  => 'UploadContentType',
                'value' => $sContentType,
            ),
            'index'             => array(
                'type'  => 'hidden',
                'name'  => 'index',
                'value' => $iIndex,
            ),
        );

        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aResult[BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        return $aResult;
    }

    function _getAuthorId()
    {
        return getLoggedId();
    }

    function _getAuthorPassword()
    {
        return !isMember() ? '' : $_COOKIE['memberPassword'];
    }

    function _getExtraParams(&$aRequest)
    {
        $aParams = array();
        foreach ($aRequest as $sKey => $sValue) {
            if (strpos($sKey, BX_DOL_UPLOADER_EP_PREFIX) !== false) {
                $aParams[str_replace(BX_DOL_UPLOADER_EP_PREFIX, '', $sKey)] = $sValue;
            }
        }

        return $aParams;
    }

    function _updateExtraParams($aExtra, $iFileId, $iAuthorId)
    {
        $aFile = $this->oModule->_oDb->getFileInfo(array('fileId' => $iFileId));
        if (empty($aFile)) {
            return $aExtra;
        }

        $oAlbums = new BxDolAlbums('bx_' . $this->sUploadTypeLC . 's', $iAuthorId);
        $aAlbum  = $oAlbums->getAlbumInfo(array('fileId' => $aFile['albumId'], 'owner' => $iAuthorId));
        if (empty($aAlbum)) {
            return $aExtra;
        }

        $aExtra['privacy_view'] = $aAlbum['AllowAlbumView'];
        if (!isset($aExtra['album'])) {
            $aExtra['album'] = $aAlbum['Uri'];
        }

        return $aExtra;
    }

    /***************************************************************************
     ****************************Semi-common functions****************************
     ****************************************************************************/
    function _GenMainAddCommonForm($aExtras = array(), $aUploaders = array())
    {
        $this->_aExtras = $aExtras;
        $sMode          = isset($_GET['mode']) ? strip_tags($_GET['mode']) : $this->_aExtras['mode'];
        unset($this->_aExtras['mode']);

        $aTxt = array();
        if (!empty($this->_aExtras['txt'])) {
            $aTxt = $this->_aExtras['txt'];
            unset($this->_aExtras['txt']);
        }

        $aUplMethods = $this->oModule->_oConfig->getUploadersMethods();

        if (empty($aUploaders)) {
            $aUploaders = array_keys($aUplMethods);
        }

        if (array_key_exists($sMode, $aUplMethods)) {
            if (is_array($aUplMethods[$sMode])) {
                $aUplMethods[$sMode]['params'] = array_merge(is_array($aUplMethods[$sMode]['params']) ? $aUplMethods[$sMode]['params'] : array(),
                    array('extras' => $this->_aExtras));

                $sForm = BxDolService::callArray($aUplMethods[$sMode]);
            } elseif (is_string($aUplMethods[$sMode]) && method_exists($this, $aUplMethods[$sMode])) {
                $sForm = $this->{$aUplMethods[$sMode]}($this->_aExtras);
            }
        } else {
            $sForm = $this->{$aUplMethods[$aUploaders[0]]}($this->_aExtras);
        }
        ob_start();
        ?>
        <iframe style="display:none;" name="__upload_type___upload_frame"></iframe>
        <script src="__modules_url__boonex/__upload_type__s/js/upload.js" type="text/javascript"
                language="javascript"></script>
        <script type="text/javascript">
            var __js_post_object__ = new Bx__upload_type_nc__Upload({
                iOwnerId: __owner_id__
            });
        </script>
        __form__
        <div id="__upload_type___accepted_files_block"></div>

        <div id="__upload_type___success_message" style="display:none;">__box_upl_succ__</div>
        <div id="__upload_type___failed_file_message" style="display:none;">__box_upl_file_err__</div>
        <div id="__upload_type___failed_message" style="display:none;">__box_upl_err__</div>
        <div id="__upload_type___embed_failed_message" style="display:none;">__box_emb_err__</div>
        <?php
        $sTempl = ob_get_clean();
        $aUnit  = array(
            'upload_type'      => $this->sUploadTypeLC,
            'modules_url'      => BX_DOL_URL_MODULES,
            'js_post_object'   => $this->_sJsPostObject,
            'upload_type_nc'   => $this->sUploadTypeNC,
            'owner_id'         => $this->_iOwnerId,
            'form'             => $sForm,
            'box_upl_succ'     => MsgBox(_t('_bx_' . $this->sUploadTypeLC . 's_upl_succ')),
            'box_upl_file_err' => MsgBox(_t('_sys_txt_upload_failed')),
            'box_upl_err'      => MsgBox(_t('_bx_' . $this->sUploadTypeLC . 's_upl_err')),
            'box_emb_err'      => MsgBox(_t('_bx_' . $this->sUploadTypeLC . 's_emb_err')),
            'txt_select_files' => _t(!empty($aTxt['select_files']) ? $aTxt['select_files'] : '_sys_txt_select_files')
        );
        $this->addCss('upload_media_comm.css');
        $this->addJsTranslation('_bx_' . $this->sUploadTypeLC . 's_emb_err');

        return $this->parseHtmlByContent($sTempl, $aUnit);
    }

    function _getEmbedFormFile()
    {
        $aForm = array(
            'form_attrs' => array(
                'id'      => $this->sUploadTypeLC . '_upload_form',
                'name'    => 'embed',
                'action'  => $this->sWorkingFile,
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'target'  => $this->sUploadTypeLC . '_upload_frame'
            ),
            'inputs'     => array(
                'header1'       => array(
                    'type'    => 'block_header',
                    'caption' => _t('_bx_' . $this->sUploadTypeLC . 's_embed')
                ),
                'embed'         => array(
                    'type'     => 'text',
                    'name'     => 'embed',
                    'caption'  => _t('_bx_' . $this->sUploadTypeLC . 's_Embed'),
                    'required' => true,
                ),
                'example'       => array(
                    'type'    => 'custom',
                    'name'    => 'example',
                    'content' => _t('_bx_' . $this->sUploadTypeLC . 's_Embed_example'),
                ),
                'hidden_action' => array(
                    'type'  => 'hidden',
                    'name'  => 'action',
                    'value' => 'accept_embed'
                ),
                'submit'        => array(
                    'type'  => 'submit',
                    'name'  => 'shoot',
                    'value' => _t('_Continue'),
                    'attrs' => array(
                        'onclick' => "return parent." . $this->_sJsPostObject . ".checkEmbed(true) && parent." . $this->_sJsPostObject . "._loading(true); sh{$this->sUploadTypeNC}EnableSubmit(false);",
                    ),
                ),
            ),
        );

        //--- Process Extras ---//
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aForm['inputs'][BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        $oForm = new BxTemplFormView($aForm);

        return $this->getLoadingCode() . $oForm->getCode();
    }

    function _getRecordFormFile($sCustomRecorderObject = '', $aExtras = array())
    {
        $aForm = array(
            'form_attrs' => array(
                'id'      => $this->sUploadTypeLC . '_upload_form',
                'name'    => 'record',
                'action'  => $this->sWorkingFile,
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'target'  => $this->sUploadTypeLC . '_upload_frame'
            ),
            'inputs'     => array(
                'header1'       => array(
                    'type'    => 'block_header',
                    'caption' => _t('_bx_' . $this->sUploadTypeLC . 's_record')
                ),
                'record'        => array(
                    'type'    => 'custom',
                    'name'    => 'file',
                    'content' => $sCustomRecorderObject,
                    'colspan' => 2
                ),
                'hidden_action' => array(
                    'type'  => 'hidden',
                    'name'  => 'action',
                    'value' => 'accept_record'
                ),
                'submit'        => array(
                    'type'    => 'submit',
                    'name'    => 'shoot',
                    'value'   => _t('_Continue'),
                    'colspan' => true,
                    'attrs'   => array(
                        'disabled' => 'disabled'
                    ),
                ),
            ),
        );

        //--- Process Extras ---//
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aForm['inputs'][BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        $oForm = new BxTemplFormView($aForm);

        return $oForm->getCode();
    }

    function getLoadingCode()
    {
        return $GLOBALS['oFunctions']->loadingBox('upload-loading-container');
    }

    function GenJquieryInjection()
    {
        return '<script src="' . BX_DOL_URL_ROOT . 'plugins/jquery/jquery.js" type="text/javascript" language="javascript"></script>';
    }

    function embedReadUrl($sUrl)
    {
        return bx_file_get_contents($sUrl);
    }

    function embedGetTagContents($sData, $sTag)
    {
        $aData = explode("<" . $sTag, $sData, 2);
        if (strpos($aData[1], ">") > 0) {
            $aData = explode(">", $aData[1], 2);
            $sData = $aData[1];
        } else {
            $sData = substr($aData[1], 1);
        }
        $aData       = explode("</" . $sTag . ">", $sData, 2);
        $sData       = $aData[0];
        $iCdataIndex = strpos($sData, "<![CDATA[");
        if (is_numeric($iCdataIndex) && $iCdataIndex == 0) {
            return $this->getStringPart($sData, "<![CDATA[", "]]>");
        }

        return $sData;
    }

    function embedGetTagAttributes($sData, $sTag, $sAttribute = "")
    {
        $aData      = explode("<" . $sTag, $sData, 2);
        $iTagIndex1 = strpos($aData[1], "/>");
        $iTagIndex  = strpos($aData[1], ">");

        if (!is_integer($iTagIndex1) || $iTagIndex1 > $iTagIndex) {
            $aData = explode(">", $aData[1], 2);
        } else {
            $aData = explode("/>", $aData[1], 2);
        }

        $sAttributes = str_replace("'", '"', trim($aData[0]));
        $aAttributes = array();

        $sPattern = '(([^=])+="([^"])+")';
        preg_match_all($sPattern, $sAttributes, $aMatches);

        $aMatches = $aMatches[0];
        for ($i = 0; $i < count($aMatches); $i++) {
            $aData                        = explode('="', $aMatches[$i]);
            $aAttributes[trim($aData[0])] = substr($aData[1], 0, strlen($aData[1]) - 1);
        }

        return empty($sAttribute) ? $aAttributes : $aAttributes[$sAttribute];
    }

    function embedGetStringPart($sData, $sLeft, $sRight)
    {
        $aParts = explode($sLeft, $sData, 2);
        $aParts = explode($sRight, $aParts[1], 2);

        return count($aParts) == 2 ? $aParts[0] : "";
    }

    function checkAuthorBeforeAdd()
    {
        if (!$this->_iOwnerId) {
            return $this->_getAuthorId() ? "" : '<script type="text/javascript">alert("' . bx_js_string(_t('_LOGIN_REQUIRED_AE1')) . '");</script>';
        }
    }

    /**
     * Form for file titles.
     * Form titles fields are added upon form upload.
     * Upon this form submit serviceAcceptHtml5FilesInfo method is called.
     */
    function getUploadFormHtml5Files()
    {
        $aForm = array(
            'form_attrs' => array(
                'id'      => $this->sUploadTypeLC . '_upload_form',
                'name'    => 'upload',
                'action'  => bx_append_url_params($this->sWorkingFile, array('action' => 'accept_multi_html5')),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'target'  => $this->sUploadTypeLC . '_upload_frame'
            ),
            'inputs'     => array(
                'submit' => array(
                    'type'  => 'submit',
                    'name'  => 'submit_form',
                    'value' => _t('_Submit'),
                ),
            ),
        );

        //--- Process Extras ---//
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aForm['inputs'][BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        $oForm = new BxTemplFormView($aForm);

        return $this->getLoadingCode() . $oForm->getCode();
    }

    function getUploadHtml5File($aExtras)
    {
        $aUploaders = $this->oModule->_oConfig->getUploaders();

        $aCustomFormData = array();
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aCustomFormData[BX_DOL_UPLOADER_EP_PREFIX . $sKey] = $mixedValue;
        }

        $aVars = array(
            'upload_type'            => $this->sUploadTypeLC,
            'form'                   => $this->getUploadFormHtml5Files(),
            'static_path'            => parse_url(BX_DOL_URL_PLUGINS, PHP_URL_PATH),
            'plugins_url'            => BX_DOL_URL_PLUGINS,
            'preview_size'           => 240,
            'action_url'             => bx_append_url_params($this->sWorkingFile,
                array('action' => $aUploaders['html5']['action'])),
            'reload_url'             => $this->sWorkingFile,
            'custom_data'            => json_encode($aCustomFormData),
            'max_file_size'          => $this->iMaxFilesize,
            'image_transform'        => json_encode($this->getUploadHtml5FileImageTransform()),
            'image_auto_orientation' => $this->bImageAutoRotate,
            'accept_mime_type'       => $this->sAcceptMimeType,
            'multiple'               => $aExtras['from_wall'] ? '' : 'multiple',
            'max_file_size_exceeded' => bx_js_string(_t('_sys_txt_upload_size_error',
                _t_format_size($this->iMaxFilesize)), BX_ESCAPE_STR_APOS),
        );

        return $this->parseHtmlByName('uploader_html5.html', $aVars);
    }

    /**
     * Perform file upload with all checks etc.
     * NOTE: this function sets $sTempFilename class property to the uploaded filename (without extension)
     *
     * @param $sFilePath          - uploaded file path
     * @param $sRealFilename      - real file name without path
     * @param $aInfo              - file info such as title, description, etc
     * @param $isMoveUploadedFile - perform move_uploaded_file for $sFilePath
     * @param $aExtraParams       - additional parameters to pass in the particular module
     * @return array with the following keys:
     *                            - id: uploaded file ID if file was successfully uploaded
     *                            - error: error message if file wasn't successfully uploaded
     */
    function performUpload($sFilePath, $sRealFilename = '', $aInfo = array(), $isMoveUploadedFile = true, $aExtraParams = array())
    {
        // override in the particular module
    }

    function getUploadHtml5FileImageTransform()
    {
        // override in the particular module, especially photos module
        return false;
    }

    function performAcceptHtml5File($aFiles, &$aReady, $name = 'file')
    {
        if (isset($aFiles['tmp_name'])) {
            $sFilePath = $aFiles['tmp_name'];
            $sFileName = $_FILES['file']['name'];
            $aReady[]  = $this->performUpload($sFilePath, $sFileName);
        } else {
            foreach ($aFiles as $name => $file) {
                $this->performAcceptHtml5File($file, $aReady, $name);
            }
        }
    }

    function fetchImagesForAcceptHtml5File($files, &$images, $name = 'file')
    {
        if (isset($files['tmp_name'])) {
            $filename = $files['tmp_name'];
            list($mime) = explode(';', @mime_content_type($filename));

            if (strpos($mime, 'image') !== false) {
                $size   = getimagesize($filename);
                $base64 = base64_encode(file_get_contents($filename));

                $images[$name] = array(
                    'width'   => $size[0]
                ,
                    'height'  => $size[1]
                ,
                    'mime'    => $mime
                ,
                    'size'    => filesize($filename)
                ,
                    'dataURL' => 'data:' . $mime . ';base64,' . $base64
                );
            }
        } else {
            foreach ($files as $name => $file) {
                $this->fetchImagesForAcceptHtml5File($file, $images, $name);
            }
        }
    }

    function serviceAcceptHtml5File()
    {
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            // Enable CORS
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type');
            header('Access-Control-Allow-Credentials: true');
        }

        if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            exit();
        }

        require_once(BX_DIRECTORY_PATH_PLUGINS . 'file-api/server/FileAPI.class.php');

        $aFiles  = FileAPI::getFiles(); // Retrieve File List
        $aReady  = array();
        $aImages = array();

        // JSONP callback name
        $sJsonp = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : null;

        // upload files and return error messages or uploaded file IDs
        $this->performAcceptHtml5File($aFiles, $aReady);

        // Fetch all image-info from files list
        $this->fetchImagesForAcceptHtml5File($aFiles, $aImages);

        // JSON-data for server response
        $aJson = array(
            'files'  => $aReady,
            'images' => $aImages,
            'data'   => array('_REQUEST' => $_REQUEST, '_FILES' => $aFiles),
        );

        // Server response: "HTTP/1.1 200 OK"
        FileAPI::makeResponse(array(
            'status'     => FileAPI::OK,
            'statusText' => 'OK',
            'body'       => $aJson
        ), $sJsonp);

        exit;
    }

    function serviceAcceptHtml5FilesInfo()
    {
        header("Content-type: text/html; charset=utf-8");

        $sPattern = 'title-';
        $sOutput  = "<script>parent.$(parent.document).trigger('bx-files-cleanup');</script>";

        if (strtoupper($_SERVER['REQUEST_METHOD']) != 'POST') {
            die($sOutput);
        }

        foreach ($_POST as $k => $s) {
            if (0 !== strpos($k, $sPattern)) {
                continue;
            }

            if (!($iId = (int)str_replace($sPattern, '', $k))) {
                continue;
            }

            if (!$this->initFile($iId, $s)) {
                continue;
            }

            $this->alertAdd($iId);
        }

        die($sOutput);
    }

    function getUploadFormFile($aExtras)
    {
        $aUploaders = $this->oModule->_oConfig->getUploaders();
        $aForm      = array(
            'form_attrs' => array(
                'id'      => $this->sUploadTypeLC . '_upload_form',
                'name'    => 'upload',
                'action'  => bx_append_url_params($this->sWorkingFile,
                    array('action' => $aUploaders['regular']['action'])),
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'target'  => $this->sUploadTypeLC . '_upload_frame'
            ),
            'inputs'     => array(
                'header1'   => array(
                    'type'    => 'block_header',
                    'caption' => _t('_bx_' . $this->sUploadTypeLC . 's_upload')
                ),
                'browse'    => array(
                    'type'     => 'file',
                    'name'     => 'file[]',
                    'caption'  => _t('_bx_' . $this->sUploadTypeLC . 's_browse'),
                    'required' => true,
                    'attrs'    => array(
                        'multiplyable' => $aExtras['from_wall'] ? 'false' : 'true',
                        'onchange' => "parent." . $this->_sJsPostObject . ".onFileChangedEvent(this);"
                    )
                ),
                'submit'    => array(
                    'type'    => 'submit',
                    'name'    => 'upload',
                    'value'   => _t('_Continue'),
                    'colspan' => true,
                    'attrs'   => array(
                        'onclick'  => "return parent." . $this->_sJsPostObject . "._loading(true);",
                        'disabled' => 'disabled'
                    )
                ),
            ),
        );

        //--- Process Extras ---//
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aForm['inputs'][BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        $oForm = new BxTemplFormView($aForm);

        return $this->getLoadingCode() . $oForm->getCode();
    }

    function _GenSendFileInfoForm(
        $iFileID,
        $aDefaultValues = array(),
        $aPossibleImage = array(),
        $aPossibleDuration = array()
    ) {
        header("Content-type: text/html; charset=utf-8");
        $this->addJsTranslation(array(
            '_bx_' . $this->sUploadTypeLC . 's_val_title_err',
            '_bx_' . $this->sUploadTypeLC . 's_val_descr_err'
        ));

        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig();
        $aFormCategories['categories']             = $oCategories->getGroupChooser('bx_' . $this->sUploadTypeLC . 's',
            $this->_iOwnerId, true);
        $aFormCategories['categories']['required'] = false;
        $sKey                                      = 'album';
        $aAlbums                                   = array();
        if ($this->_aExtras[$sKey] != '') {
            $aAlbums[BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => stripslashes($this->_aExtras[$sKey])
            );

        } else {
            $oAlbum     = new BxDolAlbums('bx_' . $this->sUploadTypeLC . 's');
            $aAlbumList = $oAlbum->getAlbumList(array('owner' => $this->_iOwnerId));

            if (count($aAlbumList) > 0) {
                foreach ($aAlbumList as $aValue) {
                    $aList[$aValue['ID']] = stripslashes($aValue['Caption']);
                }
            } else {
                $sDefName         = $oAlbum->getAlbumDefaultName();
                $aList[$sDefName] = stripslashes($sDefName);
            }
            $aAlbums['album'] = array(
                'type'    => 'select_box',
                'name'    => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'caption' => _t('_sys_album'),
                'values'  => $aList
            );
        }

        $sCaptionVal = ($this->sSendFileInfoFormCaption != '') ? $this->sSendFileInfoFormCaption : _t('_Info');
        // processing of possible default values
        $aInputValues = array('title', 'tags', 'description', 'type', $this->sUploadTypeLC);
        foreach ($aInputValues as $sField) {
            $sEmpty                  = $sField == 'type' ? 'upload' : '';
            $sTemp                   = isset($aDefaultValues[$sField]) ? strip_tags($aDefaultValues[$sField]) : $sEmpty;
            $aDefaultValues[$sField] = $sTemp;
        }
        $aForm = array(
            'form_attrs' => array(
                'id'     => $this->sUploadTypeLC . '_file_info_form',
                'method' => 'post',
                'action' => $this->sWorkingFile,
                'target' => 'upload_file_info_frame_' . $iFileID
            ),
            'inputs'     => array(
                'header2'            => array(
                    'type'        => 'block_header',
                    'caption'     => $sCaptionVal,
                    'collapsable' => false
                ),
                'title'              => array(
                    'type'     => 'text',
                    'name'     => 'title',
                    'caption'  => _t('_Title'),
                    'required' => true,
                    'value'    => $aDefaultValues['title']
                ),
                'tags'               => array(
                    'type'    => 'text',
                    'name'    => 'tags',
                    'caption' => _t('_Tags'),
                    'info'    => _t('_Tags_desc'),
                    'value'   => $aDefaultValues['tags']
                ),
                'description'        => array(
                    'type'    => 'textarea',
                    'name'    => 'description',
                    'caption' => _t('_Description'),
                    'value'   => $aDefaultValues['description']
                ),
                'media_id'           => array(
                    'type'  => 'hidden',
                    'name'  => 'file_id',
                    'value' => $iFileID,
                ),
                'hidden_action'      => array(
                    'type'  => 'hidden',
                    'name'  => 'action',
                    'value' => 'accept_file_info'
                ),
                $this->sUploadTypeLC => array(
                    'type'  => 'hidden',
                    'name'  => $this->sUploadTypeLC,
                    'value' => $aDefaultValues[$this->sUploadTypeLC]
                ),
                'type'               => array(
                    'type'  => 'hidden',
                    'name'  => 'type',
                    'value' => $aDefaultValues['type']
                )
            ),
        );

        //--- Process Extras ---//
        foreach ($this->_aExtras as $sKey => $mixedValue) {
            $aForm['inputs'][BX_DOL_UPLOADER_EP_PREFIX . $sKey] = array(
                'type'  => 'hidden',
                'name'  => BX_DOL_UPLOADER_EP_PREFIX . $sKey,
                'value' => $mixedValue
            );
        }

        // merging categories
        $aForm['inputs'] = $this->getUploadFormArray($aForm['inputs'], array($aFormCategories, $aAlbums));

        if (is_array($aPossibleImage) && count($aPossibleImage) > 0) {
            $aForm['inputs'] = array_merge($aForm['inputs'], $aPossibleImage);
        }

        if (is_array($aPossibleDuration) && count($aPossibleDuration) > 0) {
            $aForm['inputs'] = array_merge($aForm['inputs'], $aPossibleDuration);
        }

        $aForm['inputs'][] = array(
            'type'    => 'input_set',
            'colspan' => true,
            0         => array(
                'type'    => 'submit',
                'name'    => 'upload',
                'value'   => _t('_Submit'),
                'colspan' => true,
                'attrs'   => array(
                    'onclick' => "return parent." . $this->_sJsPostObject . ".doValidateFileInfo(this, '" . $iFileID . "');",
                )
            ),
            1         => array(
                'type'    => 'button',
                'name'    => 'delete',
                'value'   => _t('_bx_' . $this->sUploadTypeLC . 's_admin_delete'),
                'colspan' => true,
                'attrs'   => array(
                    'onclick' => "return parent." . $this->_sJsPostObject . ".cancelSendFileInfo('" . $iFileID . "', '" . ('record' == $aDefaultValues['type'] || 'embed' == $aDefaultValues['type'] ? '' : $this->sWorkingFile) . "'); ",
                )
            )
        );

        $oForm = new BxTemplFormView($aForm);
        $sForm = $this->parseHtmlByName('uploader_regular_info.html', array(
        	'id' => $iFileID,
        	'form' => $oForm->getCode()
        ));
        $sForm = str_replace(array("'", "\r", "\n"), array("\'"), $sForm);

        return "<script src='" . BX_DOL_URL_ROOT . "inc/js/jquery.webForms.js' type='text/javascript' language='javascript'></script><script type='text/javascript'>parent." . $this->_sJsPostObject . ".genSendFileInfoForm('" . $iFileID . "', '" . $sForm . "'); parent." . $this->_sJsPostObject . "._loading(false);</script>";
    }

    // method for checking album existense and adding object there
    function addObjectToAlbum(
        &$oAlbums,
        $sAlbumUri,
        $iObjId,
        $bUpdateCounter = true,
        $iAuthorId = 0,
        $aAlbumParams = array()
    ) {
        if (!$iAuthorId) {
            $iAuthorId = $this->_iOwnerId;
        }
        $iObjId     = (int)$iObjId;
        $aAlbumInfo = $oAlbums->getAlbumInfo(array('fileUri' => uriFilter($sAlbumUri), 'owner' => $iAuthorId),
            array('ID'));
        if (is_array($aAlbumInfo) && count($aAlbumInfo) > 0) {
            $iAlbumID = (int)$aAlbumInfo['ID'];
        } else {
            if (isset($aAlbumParams['privacy'])) {
                $iPrivacy = (int)$aAlbumParams['privacy'];
            } elseif ($sAlbumUri == $oAlbums->getAlbumDefaultName()) {
                $iPrivacy = BX_DOL_PG_HIDDEN;
            } else {
                bx_import('BxDolPrivacyQuery');
                $oPrivacy = new BxDolPrivacyQuery();
                $iPrivacy = $oPrivacy->getDefaultValueModule($this->oModule->_oConfig->getUri(), 'album_view');
                if (!$iPrivacy) {
                    $iPrivacy = BX_DOL_PG_NOBODY;
                }
            }

            $aData    = array(
                'caption'        => $sAlbumUri,
                'owner'          => $iAuthorId,
                'AllowAlbumView' => $iPrivacy
            );
            $iAlbumID = $oAlbums->addAlbum($aData, false);
        }
        $oAlbums->addObject($iAlbumID, $iObjId, $bUpdateCounter);
    }

    function getUploadFormArray(&$aForm, $aAddObjects = array())
    {
        if (is_array($aAddObjects) && !empty($aAddObjects)) {
            foreach ($aAddObjects as $aField) {
                $aForm = array_merge($aForm, $aField);
            }
        }

        return $aForm;
    }

    function serviceIsExtAllowed($sExt)
    {
        return $this->oModule->_oConfig->checkAllowedExts(strtolower($sExt));
    }

    /**
     * Handle uploads
     *
     * @param $sAction uploader 'action' name
     * @return HTML output, usually this is HTML with JS output
     *                 to the hidden iframe code which displays alert in case of error,
     *                 or form with uploaded file in case of success.
     */
    function serviceAcceptUpload($sAction)
    {
        $sCode      = '';
        $aUploaders = $this->oModule->_oConfig->getUploaders();
        foreach ($aUploaders as $k => $r) {
            if ($sAction != $r['action']) {
                continue;
            }

            if (is_array($r['handle'])) {
                $sCode = BxDolService::callArray($r['handle']);
            } elseif (is_string($r['handle']) && method_exists($this, $r['handle'])) {
                $sCode = $this->{$r['handle']}();
            }

            break;
        }

        return $sCode;
    }

    function serviceAcceptFile()
    {
        $sResult = '';
        if ($_FILES) {
            for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
                if ($_FILES['file']['error'][$i]) {
                    if ($_FILES['file']['error'][$i] == UPLOAD_ERR_INI_SIZE) {
                        $sResult .= $this->getFileAddError(_t('_sys_txt_upload_size_error',
                            _t_format_size($this->iMaxFilesize)));
                    }
                    continue;
                }
                $sResult .= $this->_shareFile($_FILES['file']['tmp_name'][$i], true, $_FILES['file']['name'][$i],
                    array('file_type' => $_FILES['file']['type'][$i]));
            }
        } else {
            $sResult = $this->getFileAddError(_t('_sys_txt_upload_size_error', _t_format_size($this->iMaxFilesize)));
        }

        return $sResult != '' ? $this->GenJquieryInjection() . $sResult : '';
    }

    function initFile($iMedID, $sTitle, $sCategories = '', $sTags = '', $sDesc = '', $aCustom = array())
    {
        $sMedUri = uriGenerate($sTitle, $this->oModule->_oDb->sFileTable, $this->oModule->_oDb->aFileFields['medUri']);

        $aFields = array(
            'Categories' => $sCategories,
            'medTitle'   => $sTitle,
            'medTags'    => $sTags,
            'medDesc'    => $sDesc,
            'medUri'     => $sMedUri
        );
        if ($aCustom) {
            $aFields = array_merge($aFields, $aCustom);
        }

        $bRes = $this->oModule->_oDb->updateData($iMedID, $aFields);

        if ($bRes) {
            $oTag = new BxDolTags();
            $oTag->reparseObjTags($this->oModule->_oConfig->sPrefix, $iMedID);
            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags($this->oModule->_oConfig->sPrefix, $iMedID);
        }

        return $bRes;
    }

    function alertAdd($iMedID, $bCheckPrivacy = false)
    {
        $aExtra = $this->_getExtraParams($_POST);
        $aExtra = $this->_updateExtraParams($aExtra, $iMedID, $this->_iOwnerId);

        if (!$bCheckPrivacy || !isset($aExtra['privacy_view']) || (int)$aExtra['privacy_view'] != (int)BX_DOL_PG_HIDDEN) {
            $oZ = new BxDolAlerts($this->oModule->_oConfig->sPrefix, 'add', $iMedID, $this->_iOwnerId, $aExtra);
            $oZ->alert();
        }
    }

    function serviceCancelFileInfo()
    {
        $iFileID = (int)$_GET['file_id'];
        if ($iFileID && $this->oModule->serviceRemoveObject($iFileID)) {
            return 1;
        }

        return 0;
    }

    function _shareFile($sFilePath, $isMoveUploadedFile = true, $sRealFilename = '', $aExtraParams = array())
    {
        $a = $this->performUpload($sFilePath, $sRealFilename, array(), $isMoveUploadedFile, $aExtraParams);

        // success
        if (isset($a['id']) && $a['id']) {
            $aDefault = array('title' => $this->sTempFilename);

            return $this->GenSendFileInfoForm($a['id'], $aDefault);
        }

        // error
        return $this->getFileAddError(isset($a['error']) ? $a['error'] : '');
    }

    function getFileAddError($sMessage = '')
    {
        $sMessage = empty($sMessage) ? _t('_sys_txt_upload_failed') : $sMessage;

        return '<script type="text/javascript">alert("' . bx_js_string($sMessage) . '"); parent.' . $this->_sJsPostObject . '._loading(false);</script>';
    }

    function GenMainAddFilesForm($aExtras = array())
    {
        $aUploaders = array_keys($this->oModule->_oConfig->getUploaderList());

        return $this->_GenMainAddCommonForm($aExtras, $aUploaders);
    }

    /**
     * Get array of available uploaders.
     */
    function serviceGetUploadersList()
    {
        return $this->oModule->_oConfig->getUploaderList();
    }

    /**
     * Generate video upload main form
     *
     * @param $aExtras - predefined album and category should appear here
     */
    function serviceGetUploaderForm($aExtras)
    {
        return $this->GenMainAddFilesForm($aExtras);
    }

    function insertSharedMediaToDb($sExt, $aFileInfo, $iAuthorId = 0, $aExtraData = array())
    {
        if (!$iAuthorId) {
            $iAuthorId = $this->_iOwnerId;
        }

        if (getParam($this->oModule->_oConfig->aGlParams['auto_activation']) == 'on') {
            $bAutoActivate = true;
            $sStatus       = 'approved';
        } else {
            $bAutoActivate = false;
            $sStatus       = 'pending';
        }
        $sFileTitle = $aFileInfo['medTitle'];
        $sFileDesc  = $aFileInfo['medDesc'];
        $sFileTags  = $aFileInfo['medTags'];
        $sCategory  = implode(CATEGORIES_DIVIDER, $aFileInfo['Categories']);
        $sDimension = isset($aFileInfo['dimension']) ? $aFileInfo['dimension'] : (int)$aFileInfo['medSize'];

        $sAlbum = mb_strlen($_POST['extra_param_album']) > 0 ? $_POST['extra_param_album'] : getParam('sys_album_default_name');
        $sAlbum = isset($aFileInfo['album']) ? $aFileInfo['album'] : $sAlbum;

        $sMedUri  = uriGenerate($sFileTitle, $this->oModule->_oDb->sFileTable,
            $this->oModule->_oDb->aFileFields['medUri']);
        $sExtDb   = trim($sExt, '.');
        $sCurTime = time();

        $aData       = array(
            'medProfId'  => $iAuthorId,
            'medExt'     => $sExtDb,
            'medTitle'   => $sFileTitle,
            'medUri'     => $sMedUri,
            'medDesc'    => $sFileDesc,
            'medTags'    => $sFileTags,
            'Categories' => $sCategory,
            'medSize'    => $sDimension,
            'Approved'   => $sStatus,
            'medDate'    => $sCurTime,
        );
        $aData       = array_merge($aData, $aExtraData);
        $iInsertedID = $this->oModule->_oDb->insertData($aData);

        if (0 < $iInsertedID) {
            $oTag = new BxDolTags();
            $oTag->reparseObjTags($this->oModule->_oConfig->sPrefix, $iInsertedID);

            $oCateg = new BxDolCategories();
            $oCateg->reparseObjTags($this->oModule->_oConfig->sPrefix, $iInsertedID);

            $aAlbumParams = isset($aFileInfo['albumPrivacy']) ? array('privacy' => $aFileInfo['albumPrivacy']) : array();
            $this->addObjectToAlbum($this->oModule->oAlbums, $sAlbum, $iInsertedID, $bAutoActivate, $iAuthorId,
                $aAlbumParams);

            return $iInsertedID;
        }

        return 0;
    }
}
