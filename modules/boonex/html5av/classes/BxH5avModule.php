<?php

bx_import('BxDolModule');

define('BX_H5AV_FALLBACK', true);

define('BX_H5AV_VIDEO_EMBED_HEIGHT', 315);
define('BX_H5AV_VIDEO_EMBED_WIDTH', 560);

define('BX_H5AV_AUDIO_EMBED_HEIGHT', 54);
define('BX_H5AV_AUDIO_EMBED_WIDTH', 560);

class BxH5avModule extends BxDolModule
{

    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    function actionVideoEmbed ($iFileId = 0)
    {
        list($sPlayer, $sMessage) = $this->getVideoPlayer ($iFileId, false, false, 'height:' . BX_H5AV_VIDEO_EMBED_HEIGHT . 'px;');
        echo $this->_oTemplate->parseHtmlByName('embed.html', array(
            'body' => $sPlayer,
            'bx_if:message' => array(
                'condition' => !(bool)$sPlayer,
                'content' => array('message' => $sMessage),
            )
        ));
    }

    function actionAudioEmbed ($iFileId = 0)
    {
        list($sPlayer, $sMessage) = $this->getAudioPlayer ($iFileId, false, 'height:' . BX_H5AV_AUDIO_EMBED_HEIGHT . 'px;');
        echo $this->_oTemplate->parseHtmlByName('embed.html', array(
            'body' => $sPlayer,
            'bx_if:message' => array(
                'condition' => !(bool)$sPlayer,
                'content' => array('message' => $sMessage),
            )
        ));
    }
    
    /**
     * Audio Embed
     */ 
    function serviceResponseAudioEmbed ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        $oAlert->aExtras['override'] = '<iframe width="' . BX_H5AV_AUDIO_EMBED_WIDTH . '" height="' . BX_H5AV_AUDIO_EMBED_HEIGHT . '" src="' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'audio_embed/' . $iFileId . '" frameborder="0" allowfullscreen></iframe>';

        return true;
    }
    
    /**
     * Video Embed
     */ 
    function serviceResponseVideoEmbed ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        if (!($aFile = $this->_oDb->getRow("SELECT * FROM `RayVideoFiles` WHERE `ID` = ?", [$iFileId])))
            return false;

        if ("" == $aFile['Source']) {
            $oAlert->aExtras['override'] = '<iframe width="' . BX_H5AV_VIDEO_EMBED_WIDTH . '" height="' . BX_H5AV_VIDEO_EMBED_HEIGHT . '" src="' . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'video_embed/' . $iFileId . '" frameborder="0" allowfullscreen></iframe>';
        }

        return true;
    }

    /**
     * Video Player
     */
    function serviceResponseVideoPlayer ($oAlert)
    {
        if(!empty($oAlert->aExtras['extra']['ext']))
            return true;

        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        list($sPlayer, $sMessage) = $this->getVideoPlayer ($iFileId);

        if ($sPlayer || $sMessage)
            $oAlert->aExtras['override'] = ($sPlayer ? $sPlayer : $this->_oTemplate->addCss(array('default.css', 'common.css', 'general.css'), true) . MsgBox($sMessage));

        return true;
    }

    function getVideoPlayer ($iFileId, $bEnableAutoplay = true, $bSetMaxHeight = true, $sCustomStyles = '')
    {
        if (!($aFile = $this->_oDb->getRow("SELECT * FROM `RayVideoFiles` WHERE `ID` = ?", [$iFileId])))
            return array(false, _t('_sys_media_not_found'));

        global $sIncPath;
        global $sModulesPath;
        global $sFilesPath;
        global $sFilesUrl;
        global $oDb;
        global $sModule;

        require_once($sIncPath . 'db.inc.php');

        $sModule = "video";
        $sModulePath = $sModulesPath . $sModule . '/inc/';

        require_once($sModulesPath . $sModule . '/inc/header.inc.php');
        require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
        require_once($sModulesPath . $sModule . '/inc/functions.inc.php');
        require_once($sModulesPath . $sModule . '/inc/customFunctions.inc.php');

        $aRet = array(false, false);
        switch($aFile['Status']) {
            case STATUS_PENDING:
            case STATUS_PROCESSING:
                $aRet = array(false, _t('_sys_media_processing'));
                break;
            case STATUS_DISAPPROVED:
                if (!isAdmin()) {
                    $aRet = array(false, _t('_sys_media_disapproved'));
                    break;            
                }    
            case STATUS_APPROVED:
                if (file_exists($sFilesPath . $iFileId . M4V_EXTENSION)) {

                    $sToken = getToken($iFileId);

                    if (file_exists($sFilesPath . $iFileId . '.webm'))
                        $sSourceWebm = '<source type=\'video/webm; codecs="vp8, vorbis"\' src="' . BX_DOL_URL_ROOT . "flash/modules/video/get_file.php?id=" . $iFileId . "&ext=webm&token=" . $sToken . '" />';

                    $sFlash = getApplicationContent('video','player',array('id' => $iFileId, 'user' => getLoggedId(), 'password' => clear_xss($_COOKIE['memberPassword'])),true);
                    $sId = 'bx-media-' . genRndPwd(8, false);
                    $sJs = $sSourceWebm ? // if no .webm video available - we need nice fallback in firefox and other browsers with no mp4 support
                            '' : '
                            var eMedia = document.createElement("video");
                            if (eMedia.canPlayType && !eMedia.canPlayType("video/x-m4v")) {
                                var sReplace = "' . bx_js_string(BX_H5AV_FALLBACK ? $sFlash : '<b>Your browser doesn\'t support this media playback.</b>', BX_ESCAPE_STR_QUOTE) . '";
                                $("#' . $sId . '").replaceWith(sReplace);
                            }';
                    $sJs .= $aFile['Time'] ? // if length is not set
                            '' : '
                            eFile.on("canplay", function (e) {
                                $.post("' . BX_DOL_URL_ROOT . 'flash/XML.php", {
                                    module: "video",
                                    action: "updateFileTime",
                                    id: ' . $iFileId . ',
                                    time: parseInt(this.duration * 1000)
                                });
                            });';

                    $sAutoPlay = $bEnableAutoplay && TRUE_VAL == getSettingValue('video', 'autoPlay') && class_exists('BxVideosPageView') ? 'autoplay' : '';
                    
                    $sFilePoster = 'flash/modules/video/files/' . $iFileId . '.jpg';
                    $sPoster = file_exists(BX_DIRECTORY_PATH_ROOT . $sFilePoster) ? ' poster="' . BX_DOL_URL_ROOT . $sFilePoster . '" ' : '';

                    $sStyleMaxHeight = $bSetMaxHeight ? 'max-height:' . getSettingValue('video', 'player_height') . 'px;' : '';

                    $sPlayer = '
                        <video controls preload="metadata" autobuffer ' . $sAutoPlay . $sPoster . ' style="width:100%;' . $sStyleMaxHeight . $sCustomStyles . '" id="' . $sId . '">
                            ' . $sSourceWebm . '
                            <source src="' . BX_DOL_URL_ROOT . "flash/modules/video/get_file.php?id=" . $iFileId . "&ext=m4v&token=" . $sToken . '" />
                            ' . (BX_H5AV_FALLBACK ? $sFlash : '<b>Can not playback media - your browser doesn\'t support HTML5 audio/video tag.</b>') . '
                        </video>
                        <script>
                            var eFile = $("#' . $sId . '");
                            eFile.on("play", function () {
                                var ePlaying = this;
                                $("video").each(function () {
                                    if (this != ePlaying)
                                        this.pause();
                                });
                            });
                            ' . $sJs . '
                        </script>';
                    $aRet = array($sPlayer, '');
                break;
                }
            case STATUS_FAILED:
            default:
                if (!BX_H5AV_FALLBACK || !file_exists($sFilesPath . $iFileId . FLV_EXTENSION))
                    $aRet = array(false, _t('_sys_media_not_found'));
                break;
        }

        return $aRet;
    }

    /**
     * Video Convert
     */
    function serviceResponseVideoConvert ($oAlert)
    {

        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        if (!$oAlert->aExtras['result'])
            return true;

        $sFfmpegPath = $oAlert->aExtras['ffmpeg'];
        $sTempFile = $oAlert->aExtras['tmp_file'];
        $iBitrate = $oAlert->aExtras['bitrate'];
        $sSize = $oAlert->aExtras['size'];
        $sPlayFile = $sTempFile . '.webm';

        if (!file_exists($sTempFile))
            $sTempFile .= '.flv';

        $sCommand = $sFfmpegPath . " -y -i " . $sTempFile . " -acodec libvorbis -b:a 128k -ar 44100 -b:v {$iBitrate}k -s {$sSize} " . $sPlayFile;
        popen($sCommand, "r");

        return true;
    }

    /**
     * Video Delete
     */
    function serviceResponseVideoDelete ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        $oMain = BxDolModule::getInstance('BxVideosModule');

        @unlink($oMain->_oConfig->getFilesPath() . $iFileId . '.webm');

        return true;
    }

    /**
     * Audio Player
     */
    function serviceResponseAudioPlayer ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        list($sPlayer, $sMessage) = $this->getAudioPlayer ($iFileId);

        if ($sPlayer || $sMessage)
            $oAlert->aExtras['override'] = ($sPlayer ? $sPlayer : $this->_oTemplate->addCss(array('default.css', 'common.css', 'general.css'), true) . MsgBox($sMessage));

        return true;
    }

    function getAudioPlayer ($iFileId, $bEnableAutoplay = true, $sCustomStyles = '')
    {
        if (!($aFile = $this->_oDb->getRow("SELECT * FROM `RayMp3Files` WHERE `ID` = ?", [$iFileId])))
            return array(false, _t('_sys_media_not_found'));

        global $sIncPath;
        global $sModulesPath;
        global $sModule;
        global $sFilesPath;
        global $sFilesPathMp3;
        global $oDb;
        global $sModule;

        require_once($sIncPath . 'db.inc.php');

        $sModule = "mp3";
        $sModulePath = $sModulesPath . $sModule . '/inc/';

        require_once($sModulesPath . $sModule . '/inc/header.inc.php');
        require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
        require_once($sModulesPath . $sModule . '/inc/functions.inc.php');
        require_once($sModulesPath . $sModule . '/inc/customFunctions.inc.php');

        $aRet = array(false, false);
        switch($aFile['Status']) {
            case STATUS_PENDING:
            case STATUS_PROCESSING:
                $aRet = array(false, _t('_sys_media_processing'));
                break;
            case STATUS_DISAPPROVED:
                if (!isAdmin()) {
                    $aRet = array(false, _t('_sys_media_disapproved'));
                    break;
                }                
            case STATUS_APPROVED:
                if (file_exists($GLOBALS['sFilesPathMp3'] . $iFileId . MP3_EXTENSION)) {

                    $sToken = getMp3Token($iFileId);

                    if (file_exists($GLOBALS['sFilesPathMp3'] . $iFileId . '.ogg'))
                        $sSourceOgg = '<source type=\'audio/ogg; codecs="vorbis"\' src="' . BX_DOL_URL_ROOT . "flash/modules/mp3/get_file.php?id=" . $iFileId . "&token=" . $sToken . '&ext=ogg" />';

                    $sFlash = getApplicationContent('mp3', 'player', array('id' => $iFileId, 'user' => getLoggedId(), 'password' => clear_xss($_COOKIE['memberPassword'])), true);
                    $sId = 'bx-media-' . genRndPwd(8, false);
                    $sJs = $sSourceOgg ? // if no .ogg audio available - we need nice fallback in firefox and other browsers with no mp3 support
                            '' : '
                            var eMedia = document.createElement("audio");
                            if (eMedia.canPlayType && !eMedia.canPlayType("audio/mpeg")) {
                                var sReplace = "' . bx_js_string(BX_H5AV_FALLBACK ? $sFlash : '<b>Your browser doesn\'t support this media playback.</b>', BX_ESCAPE_STR_QUOTE) . '";
                                $("#' . $sId . '").replaceWith(sReplace);
                            }';
                    $sJs .= $aFile['Time'] ? // if length is not set
                            '' : '
                            eFile.on("canplay", function (e) {
                                $.post("' . BX_DOL_URL_ROOT . 'flash/XML.php", {
                                    module: "mp3",
                                    action: "updateFileTime",
                                    id: ' . $iFileId . ',
                                    time: parseInt(this.duration * 1000)
                                });
                            });';                    
                    $sAutoPlay = $bEnableAutoplay && TRUE_VAL == getSettingValue('mp3', 'autoPlay') && class_exists('BxSoundsPageView') ? 'autoplay' : '';
                    $sPlayer = '
                        <audio controls ' . $sAutoPlay . ' preload="metadata" autobuffer style="width:100%; ' . $sCustomStyles . '" id="' . $sId . '">
                            <source type=\'audio/mpeg; codecs="mp3"\' src="' . BX_DOL_URL_ROOT . "flash/modules/mp3/get_file.php?id=" . $iFileId . "&token=" . $sToken . '" />
                            ' . $sSourceOgg . '
                            ' . (BX_H5AV_FALLBACK ? $sFlash : '<b>Can not playback media - your browser doesn\'t support HTML5 audio/video tag.</b>') . '
                        </audio>
                        <script>
                            var eFile = $("#' . $sId . '");
                            eFile.on("play", function () {
                                var ePlaying = this;
                                $("audio").each(function () {
                                    if (this != ePlaying)
                                        this.pause();
                                });
                            });
                            ' . $sJs . '
                        </script>';
                    $aRet = array($sPlayer, '');
                    break;
                }
            case STATUS_FAILED:
            default:
                $aRet = array(false, _t('_sys_media_not_found'));
                break;
        }

        return $aRet;
    }
    
    /**
     * Audio Convert
     */
    function serviceResponseAudioConvert ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        if (!$oAlert->aExtras['result'])
            return true;

        $sFfmpegPath = $oAlert->aExtras['ffmpeg'];
        $sTempFile = $oAlert->aExtras['tmp_file'];
        $iBitrate = $oAlert->aExtras['bitrate'];
        $sPlayFile = $sTempFile . '.ogg';

        $sCommand = $sFfmpegPath . " -y -i " . $sTempFile . MP3_EXTENSION . " -vn -b:a " . $iBitrate . "k -acodec libvorbis " . $sPlayFile;
        popen($sCommand, "r");

        return true;
    }

    /**
     * Audio Delete
     */
    function serviceResponseAudioDelete ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        $oMain = BxDolModule::getInstance('BxSoundsModule');

        @unlink($oMain->_oConfig->getFilesPath() . $iFileId . '.ogg');

        return true;
    }

    /**
     * Cmts Player
     */
    function serviceResponseCmtsPlayer ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        if (!($aFile = $this->_oDb->getRow("SELECT * FROM `RayVideo_commentsFiles` WHERE `ID` = ?", [$iFileId])))
            return false;

        global $sIncPath;
        global $sModulesPath;
        global $sFilesPath;
        global $sFilesUrl;
        global $oDb;

        require_once($sIncPath . 'db.inc.php');

        $sModule = "video_comments";
        $sModulePath = $sModulesPath . $sModule . '/inc/';

        require_once($sModulesPath . $sModule . '/inc/header.inc.php');
        require_once($sModulesPath . $sModule . '/inc/constants.inc.php');
        require_once($sModulesPath . $sModule . '/inc/functions.inc.php');
        require_once($sModulesPath . $sModule . '/inc/customFunctions.inc.php');

        $sOverride = false;
        switch($aFile['Status']) {
            case VC_STATUS_DISAPPROVED:
                $sOverride = $this->_oTemplate->addCss(array('default.css', 'common.css', 'general.css'), true) . MsgBox(_t('_sys_media_disapproved'));
                break;
            case VC_STATUS_PENDING:
            case VC_STATUS_PROCESSING:
                $sOverride = $this->_oTemplate->addCss(array('default.css', 'common.css', 'general.css'), true) . MsgBox(_t('_sys_media_processing'));
                break;
            case VC_STATUS_APPROVED:
                if (file_exists($sFilesPath . $iFileId . VC_M4V_EXTENSION)) {

                    $sToken = _getToken($iFileId);

                    if (file_exists($sFilesPath . $iFileId . '.webm'))
                        $sSourceWebm = '<source type=\'video/webm; codecs="vp8, vorbis"\' src="' . BX_DOL_URL_ROOT . "flash/modules/video_comments/get_file.php?id=" . $iFileId . "&ext=webm&token=" . $sToken . '" />';

                    $sFlash = $oAlert->aExtras['data'];
                    $sId = 'bx-media-' . genRndPwd(8, false);
                    $sOverride = '
                        <video controls preload="metadata" autobuffer id="' . $sId . '">
                            ' . $sSourceWebm . '
                            <source src="' . BX_DOL_URL_ROOT . "flash/modules/video_comments/get_file.php?id=" . $iFileId . "&ext=m4v&token=" . $sToken . '" />
                            ' . (BX_H5AV_FALLBACK ? $sFlash : '<b>Can not playback media - your browser doesn\'t support HTML5 audio/video tag.</b>') . '
                        </video>' .
                        ($sSourceWebm ? // if no .webm video available - we need nice fallback in firefox and other browsers with no mp4 support
                            '' :
                            '<script>
                                var eMedia = document.createElement("video");
                                if (eMedia.canPlayType && !eMedia.canPlayType("video/x-m4v")) {
                                    var sReplace = "' . bx_js_string(BX_H5AV_FALLBACK ? $sFlash : '<b>Your browser doesn\'t support this media playback.</b>', BX_ESCAPE_STR_QUOTE) . '";
                                    $("#' . $sId . '").replaceWith(sReplace);
                                }
                            </script>');
                    break;
                }
            case VC_STATUS_FAILED:
            default:
                if (!BX_H5AV_FALLBACK || !file_exists($sFilesPath . $iFileId . FLV_EXTENSION))
                    $sOverride = $this->_oTemplate->addCss(array('default.css', 'common.css', 'general.css'), true) . MsgBox(_t('_sys_media_not_found'));
                break;
        }

        if ($sOverride)
            $oAlert->aExtras['data'] = $sOverride;

        return true;
    }

    /**
     * Cmts Convert
     */
    function serviceResponseCmtsConvert ($oAlert)
    {

        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        if (!$oAlert->aExtras['result'])
            return true;

        $sFfmpegPath = $oAlert->aExtras['ffmpeg'];
        $sTempFile = $oAlert->aExtras['tmp_file'];
        $iBitrate = $oAlert->aExtras['bitrate'];
        $sSize = $oAlert->aExtras['size'];
        $sPlayFile = $sTempFile . '.webm';

        $sCommand = $sFfmpegPath . " -y -i " . $sTempFile . " -acodec libvorbis -b:a 128k -ar 44100 -b:v {$iBitrate}k -s {$sSize} " . $sPlayFile;
        popen($sCommand, "r");

        return true;
    }

    /**
     * Cmts Delete
     */
    function serviceResponseCmtsDelete ($oAlert)
    {
        if (!($iFileId = (int)$oAlert->iObject))
            return false;

        @unlink($oAlert->aExtras['files_path'] . $iFileId . '.webm');

        return true;
    }
}
