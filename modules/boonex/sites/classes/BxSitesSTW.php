<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    define('ACCESS_KEY', getParam('bx_sites_key_id'));
    define('SECRET_KEY', getParam('bx_sites_secret_key'));
    define('ACCOUNT_TYPE', getParam('bx_sites_account_type')); // 'No Automated Screenshots' or 'Enabled'
    define('THUMBNAIL_URI', $GLOBALS['oBxSitesModule']->sThumbUrl);
    define('THUMBNAIL_DIR', $GLOBALS['oBxSitesModule']->sThumbPath);
    define('INSIDE_PAGES', getParam('bx_sites_inside_pages') == 'on' ? true : false); // set to true if inside capturing should be allowed
    define('CUSTOM_MSG_URL', getParam('bx_sites_custom_msg_url')); // i.e. 'http://yourdomain.com/path/to/your/custom/msgs'
    define('CACHE_DAYS', getParam('bx_sites_cache_days')); // how many days should the local copy be valid?
                             // Enter 0 (zero) to never update screenshots once cached
                             // Enter -1 to disable caching and always use embedded method instead
    define('VER', '2.0.3_dol7'); // allows us to identify known bugs and version control; DONT touch!
    define('DEBUG', getParam('bx_sites_debug') == 'on' ? true : false);
    define('QUOTA_IMAGE', 'quota.jpg');
    define('BANDWIDTH_IMAGE', 'bandwidth.jpg');
    define('NO_RESPONSE_IMAGE', 'no_response.jpg');

    /********************************************
    *	!! DO NOT CHANGE BELOW THIS LINE !!		*
    *	...unless you know what you are doing	*
    ********************************************/

    /**
     * Gets the thumbnail for the specified website, stores it in the cache, and then returns the
     * HTML for loading the image. This handles the ShrinkTheWeb javascript loader for free basic
     * accounts.
     */
    function getThumbnailHTML($sUrl, $aOptions, $sAttribAlt = false, $sAttribClass = false, $sAttribStyle = false)
    {
          $sImageTag = false;
        if (ACCOUNT_TYPE != 'No Automated Screenshots') {
            $aOptions = _generateOptions($aOptions);

            $sImageTag = _getThumbnailPaid($sUrl, $aOptions, $sAttribAlt, $sAttribClass, $sAttribStyle);
        }

          return $sImageTag;
    }

    /**
     * Get Account XML response and save it into database
     */
    function saveAccountInfo()
    {
        $aArgs['stwaccesskeyid'] = ACCESS_KEY;
        $aArgs['stwu'] = SECRET_KEY;

          $sRequestUrl = 'http://images.shrinktheweb.com/account.php';
        $sRemoteData = bx_file_get_contents($sRequestUrl, $aArgs);
        $aResponse = _getAccXMLResponse($sRemoteData);

        if ($aResponse['stw_response_status'] == 'Success') {
            $GLOBALS['oBxSitesModule']->_oDb->addAccountInfo(ACCESS_KEY, $aResponse);
        }

        return $aResponse;
    }

    /**
     * Delete thumbnail
     */
    function deleteThumbnail($sUrl, $aOptions = array())
    {
        $aOptions = _generateOptions($aOptions);
        $aArgs = _generateRequestArgs($aOptions);
        $aArgs['stwurl'] = $sUrl;

        $sFilename = _generateHash($aArgs).'.jpg';
        $sFile = THUMBNAIL_DIR . $sFilename;

           if (file_exists($sFile)) {
            @unlink($sFile);
        }
    }

    function _getThumbnailPaid($sUrl, $aOptions, $sAttribAlt, $sAttribClass, $sAttribStyle)
    {
        $sImageUrl = _getThumbnail($sUrl, $aOptions);

        // if WAY OVER the limits (i.e. request is ignored by STW), grab an "Account Problem" image and store it as NO_RESPONSE_IMAGE
        if ($sImageUrl == 'no_response') {
            $sImageUrl = _getNoResponseImage($sUrl, $aOptions);
        }

        // Add attributes if set
        $sTags = false;
        if ($sAttribStyle) {
            $sTags .= ' style="' . bx_html_attribute($sAttribStyle) . '"';
        }
        if ($sAttribAlt) {
            $sTags .= ' alt="' . bx_html_attribute($sAttribAlt) . '"';
        }
        if ($sAttribClass) {
            $sTags .= ' class="' . bx_html_attribute($sAttribClass) . '"';
        }

        return $sImageUrl ? '<img src="' . bx_html_attribute($sImageUrl) . '"'.$sTags.'/>' : false;
    }

    /**
     * Gets the thumbnail for the specified website, stores it in the cache, and then returns the
     * relative path to the cached image.
     */
    function _getThumbnail($sUrl, $aOptions)
    {
        // create cache directory if it doesn't exist
        _createCacheDirectory();

        $aOptions = _generateOptions($aOptions);
        $aArgs = _generateRequestArgs($aOptions);

        // Try to grab the thumbnail
        $iCacheDays = CACHE_DAYS + 0;
        if ($iCacheDays >= 0 && $aOptions['Embedded'] != 1) {
            $aArgs['stwurl'] = $sUrl;
            $sImageUrl = _getCachedThumbnail($aArgs);
        } else {
            // Get raw image data
            unset($aArgs['stwu']); // ONLY on "Advanced" method requests!! (not allowed on embedded)
            $aArgs['stwembed'] = 1;
            $aArgs['stwurl'] = $sUrl;
            $sImageUrl = urldecode("http://images.shrinktheweb.com/xino.php?".http_build_query($aArgs,'','&'));
        }

        return $sImageUrl;
    }

    /**
     * generate options
     */
    function _generateOptions($aOptions)
    {
        $aOptions['Size'] = $aOptions['Size'] ? $aOptions['Size'] : getParam('bx_sites_thumb_size');
        $aOptions['SizeCustom'] = $aOptions['SizeCustom'] ? $aOptions['SizeCustom'] : getParam('bx_sites_thumb_size_custom');
        $aOptions['FullSizeCapture'] = $aOptions['FullSizeCapture'] ? $aOptions['FullSizeCapture'] : getParam('bx_sites_full_size') == 'on' ? true : false;
        $aOptions['MaxHeight'] = $aOptions['MaxHeight'] ? $aOptions['MaxHeight'] : getParam('bx_sites_max_height');
        $aOptions['NativeResolution'] = $aOptions['NativeResolution'] ? $aOptions['NativeResolution'] : getParam('bx_sites_native_res');
        $aOptions['WidescreenY'] = $aOptions['WidescreenY'] ? $aOptions['WidescreenY'] : getParam('bx_sites_widescreen_y');
        $aOptions['RefreshOnDemand'] = $aOptions['RefreshOnDemand'] ? $aOptions['RefreshOnDemand'] : false;
        $aOptions['Delay'] = $aOptions['Delay'] ? $aOptions['Delay'] : getParam('bx_sites_delay');
        $aOptions['Quality'] = $aOptions['Quality'] ? $aOptions['Quality'] : getParam('bx_sites_quality');

        return $aOptions;
    }

    /**
     * generate the request arguments
     */
    function _generateRequestArgs($aOptions)
    {
        // Get all the options from the database for the thumbnail
        $aArgs['stwaccesskeyid'] = ACCESS_KEY;
        $aArgs['stwu'] = SECRET_KEY;
        $aArgs['stwver'] = VER;

        // Allowing internal links?
        if (INSIDE_PAGES) {
            $aArgs['stwinside'] = 1;
        }

        // If SizeCustom is specified and widescreen capturing  is not activated,
        // then use that size rather than the size stored in the settings
        if (!$aOptions['FullSizeCapture'] && !$aOptions['WidescreenY']) {
               // Do we have a custom size?
            if ($aOptions['SizeCustom']) {
                $aArgs['stwxmax'] = $aOptions['SizeCustom'];
            } else {
                   $aArgs['stwsize'] = $aOptions['Size'];
               }
        }

        // Use fullsize capturing?
        if ($aOptions['FullSizeCapture']) {
            $aArgs['stwfull'] = 1;
            if ($aOptions['SizeCustom']) {
                $aArgs['stwxmax'] = $aOptions['SizeCustom'];
            } else {
                   $aArgs['stwxmax'] = 120;
               }
            if ($aOptions['MaxHeight']) {
                $aArgs['stwymax'] = $aOptions['MaxHeight'];
            }
        }

        // Change native resolution?
        if ($aOptions['NativeResolution']) {
            $aArgs['stwnrx'] = $aOptions['NativeResolution'];
            if ($aOptions['WidescreenY']) {
                  $aArgs['stwnry'] = $aOptions['WidescreenY'];
                if ($aOptions['SizeCustom']) {
                    $aArgs['stwxmax'] = $aOptions['SizeCustom'];
                } else {
                       $aArgs['stwxmax'] = 120;
                   }
            }
        }

        // Wait after page load in seconds
        if ($aOptions['Delay']) {
            $aArgs['stwdelay'] = intval($aOptions['Delay']) <= 45 ? intval($aOptions['Delay']) : 45;
        }

        // Use Refresh On-Demand?
        if ($aOptions['RefreshOnDemand']) {
            $aArgs['stwredo'] = 1;
        }

        // Use another image quality percent %
        if ($aOptions['Quality']) {
            $aArgs['stwq'] = intval($aOptions['Quality']);
        }

        // Use custom messages?
        if (CUSTOM_MSG_URL) {
            $aArgs['stwrpath'] = CUSTOM_MSG_URL;
        }

        return $aArgs;
    }

    /**
     * Get a thumbnail, caching it first if possible.
     */
    function _getCachedThumbnail($aArgs = null)
    {
        $aArgs = is_array($aArgs) ? $aArgs : array();

        // Use arguments to work out the target filename
        $sFilename = _generateHash($aArgs).'.jpg';
        $sFile = THUMBNAIL_DIR . $sFilename;

        $sReturnName = false;
        // Work out if we need to update the cached thumbnail
        $iForceUpdate = $aArgs['stwredo'] ? true : false;
        if ($iForceUpdate || _cacheFileExpired($sFile)) {
            // if quota limit has reached return the QUOTA_IMAGE
            if (_checkLimitReached(THUMBNAIL_DIR . QUOTA_IMAGE)) {
                $sFilename = QUOTA_IMAGE;
            // if bandwidth limit has reached return the BANDWIDTH_IMAGE
            } else if (_checkLimitReached(THUMBNAIL_DIR . BANDWIDTH_IMAGE)) {
                $sFilename = BANDWIDTH_IMAGE;
            // if WAY OVER the limits (i.e. request is ignored by STW) return the NO_RESPONSE_IMAGE
            } else if (_checkLimitReached(THUMBNAIL_DIR . NO_RESPONSE_IMAGE)) {
                $sFilename = NO_RESPONSE_IMAGE;
            } else {
                // check if the thumbnail was captured
                $aImage = _checkWebsiteThumbnailCaptured($aArgs);
                switch ($aImage['status']) {
                    case 'save': // download the image to local path
                        _downloadRemoteImageToLocalPath($aImage['url'], $sFile);
                    break;

                    case 'nosave': // dont save the image but return the url
                        return $aImage['url'];
                    break;

                    case 'quota_exceed': // download the image to local path for locking requests
                        $sFilename = QUOTA_IMAGE;
                        $sFile = THUMBNAIL_DIR . $sFilename;
                        _downloadRemoteImageToLocalPath($aImage['url'], $sFile);
                    break;

                    case 'bandwidth_exceed': // download the image to local path for locking requests
                        $sFilename = BANDWIDTH_IMAGE;
                        $sFile = THUMBNAIL_DIR . $sFilename;
                        _downloadRemoteImageToLocalPath($aImage['url'], $sFile);
                    break;

                    default: // otherwise return the status
                        return $aImage['status'];
                }
            }
        }

        $sFile = THUMBNAIL_DIR . $sFilename;
        // Check if file exists
        if (file_exists($sFile)) {
            $sReturnName = THUMBNAIL_URI . $sFilename;
        }

        return $sReturnName;
    }

    /**
     * Method that checks if the thumbnail for the specified website exists.
     */
    function _checkWebsiteThumbnailCaptured($aArgs)
    {
        $sRequestUrl = 'http://images.shrinktheweb.com/xino.php';
        $sRemoteData = bx_file_get_contents($sRequestUrl, $aArgs);

        if ($sRemoteData != "") {
            $aResponse = _getXMLResponse($sRemoteData);
            // thumbnail is existing, download it
            if ($aResponse['exists'] && $aResponse['thumbnail'] != '') {
                $aImage = array('status' => 'save', 'url' => $aResponse['thumbnail']);
            // lock-to-account, show image but do not store (so users will not be locked out for 6 hours just to update their allowed referrers
            // bandwidth limit has reached, grab embedded image and store it as BANDWIDTH_IMAGE
            } else if ($aResponse['stw_bandwidth_remaining'] == 0 && !$aResponse['locked'] && !$aResponse['invalid'] && !$aResponse['exists']) {
                $aImage = array('status' => 'bandwidth_exceed', 'url' => $aResponse['thumbnail']);
            // quota limit has reached, grab embedded image and store it as QUOTA_IMAGE
            } else if ($aResponse['stw_quota_remaining'] == 0 && !$aResponse['locked'] && !$aResponse['invalid'] && !$aResponse['exists']) {
                $aImage = array('status' => 'quota_exceed', 'url' => $aResponse['thumbnail']);
            // an error has occured, return the url but dont save the image
            } else if (!$aResponse['exists'] && $aResponse['thumbnail'] != '') {
                $aImage = array('status' => 'nosave', 'url' => $aResponse['thumbnail']);
            // otherwise return error because we dont know the situation
            } else {
                $aImage = array('status' => 'error');
            }

              if (DEBUG) {
                $GLOBALS['oBxSitesModule']->_oDb->addRequest($aArgs, $aResponse, _generateHash($aArgs));
           }
        } else {
            $aImage = array('status' => 'no_response');
        }

        return $aImage;
    }

    /**
     * Method to get image at the specified remote Url and attempt to save it to the specifed local path
     */
    function _downloadRemoteImageToLocalPath($sRemoteUrl, $sFile)
    {
        $sRemoteData = bx_file_get_contents($sRemoteUrl, array());

        // Only save data if we managed to get the file content
        if ($sRemoteData) {
            if ($oFile = fopen($sFile, "w+")) {
                fputs($oFile, $sRemoteData);
                fclose($oFile);
            }
        } else {
            // Try to delete file if download failed
            if (file_exists($sFile)) {
                @unlink($sFile);
            }

            return false;
        }

        return true;
    }

    /**
     * Gets the account problem image and returns the relative path to the cached image
     */
    function _getNoResponseImage($sUrl, $aOptions)
    {
        // create cache directory if it doesn't exist
        _createCacheDirectory();

        $aOptions = _generateOptions($aOptions);

        $aArgs['stwaccesskeyid'] = 'accountproblem';

        if ($aOptions['SizeCustom']) {
            $aArgs['stwxmax'] = $aOptions['SizeCustom'];
        } else {
            $aArgs['stwsize'] = $aOptions['Size'];
        }

        $sRequestUrl = 'http://images.shrinktheweb.com/xino.php';
        $sRemoteData = bx_file_get_contents($sRequestUrl, $aArgs);

        if ($sRemoteData != '') {
            $aResponse = _getXMLResponse($sRemoteData);

            if (!$aResponse['exists'] && $aResponse['thumbnail'] != '') {
                $sImageUrl = $aResponse['thumbnail'];

                $sFilename = NO_RESPONSE_IMAGE;
                $sFile = THUMBNAIL_DIR . $sFilename;
                $isDownloaded = _downloadRemoteImageToLocalPath($sImageUrl, $sFile);

                if ($isDownloaded == true) {
                    return THUMBNAIL_URI . $sFilename;
                }
            }
        }

        return false;
    }

    /**
     * Check if the limit reached image is existing, if so return true
     * return false if there is no image existing or the limit reached file is
     * older then 6 hours
     */
    function _checkLimitReached($sFile)
    {
        // file is not existing
        if (!file_exists($sFile)) {
            return false;
        }

        // is file older then 6 hours?
        $iCutoff = time() - (3600 * 6);
        if (filemtime($sFile) <= $iCutoff) {
            @unlink($sFile);
            return false;
        }

        // file is existing and not expired!
        return true;
    }

    /**
     * Create the cache directory if it doesn't exist
     */
    function _createCacheDirectory()
    {
        // Create cache directory if it doesn't exist
        if (!file_exists(THUMBNAIL_DIR)) {
            @mkdir(THUMBNAIL_DIR, 0777, true);
        } else {
            // Try to make the directory writable
            @chmod(THUMBNAIL_DIR, 0777);
        }
    }

    /**
     * Generate the hash for the thumbnail
     */
    function _generateHash($aArgs)
    {
/*        $sPrehash = $aArgs['stwfull'] ? 'a' : 'c';
        $sPrehash .= $aArgs['stwxmax'].'x'.$aArgs['stwymax'];
        if ($aArgs['stwnrx']) {
            $sPrehash .= 'b'.$aArgs['stwnrx'].'x'.$aArgs['stwnry'];
        }
        $sPrehash .= $aArgs['stwinside'];*/

        $aReplace = array('http', 'https', 'ftp', '://');
        $sUrl = str_replace($aReplace, '', $aArgs['stwurl']);

//        return md5($sPrehash.$aArgs['stwsize'].$aArgs['stwq'].$sUrl);
        return md5($sUrl);
    }

    /**
     * store the XML response in an array and generate status bits
     */
    function _getXMLResponse($sResponse)
    {
        if (extension_loaded('simplexml')) { // If simplexml is available, we can do more stuff!
            $oDOM = new DOMDocument;
            $oDOM->loadXML($sResponse);
            $sXML = simplexml_import_dom($oDOM);
            $sXMLLayout = 'http://www.shrinktheweb.com/doc/stwresponse.xsd';

            // Pull response codes from XML feed
            $aResponse['stw_response_status'] = $sXML->children($sXMLLayout)->Response->ResponseStatus->StatusCode; // HTTP Response Code
            $aResponse['stw_action'] = $sXML->children($sXMLLayout)->Response->ThumbnailResult->Thumbnail[1]; // ACTION
            $aResponse['stw_response_code'] = $sXML->children($sXMLLayout)->Response->ResponseCode->StatusCode; // STW Error Response
            $aResponse['stw_last_captured'] = $sXML->children($sXMLLayout)->Response->ResponseTimestamp->StatusCode; // Last Captured
            $aResponse['stw_quota_remaining'] = $sXML->children($sXMLLayout)->Response->Quota_Remaining->StatusCode; // New Reqs left for today
            $aResponse['stw_bandwidth_remaining'] = $sXML->children($sXMLLayout)->Response->Bandwidth_Remaining->StatusCode; // New Reqs left for today
            $aResponse['stw_category_code'] = $sXML->children($sXMLLayout)->Response->CategoryCode->StatusCode; // Not yet implemented
            $aResponse['thumbnail'] = $sXML->children($sXMLLayout)->Response->ThumbnailResult->Thumbnail[0]; // Image Location (alt method)
        } else {
            // LEGACY SUPPPORT
            $aResponse['stw_response_status'] = _getLegacyResponse('ResponseStatus', $sRemoteData);
            $aResponse['stw_response_code'] = _getLegacyResponse('ResponseCode', $sRemoteData);

            // check remaining quota
            $aResponse['stw_quota_remaining'] = _getLegacyResponse('Quota_Remaining', $sRemoteData);
            // check remaining bandwidth
            $aResponse['stw_bandwidth_remaining'] = _getLegacyResponse('Bandwidth_Remaining', $sRemoteData);

            // get thumbnail and status
            $aThumbnail = _getThumbnailStatus($sRemoteData);
            $aResponse = array_merge($aResponse, $aThumbnail);
        }

        if ($aResponse['stw_action'] == 'delivered') {
            $aResponse['exists'] = true;
        } else {
            $aResponse['exists'] = false;
        }

        if ($aResponse['stw_action'] == 'fix_and_retry') {
            $aResponse['problem'] = true;
        } else {
            $aResponse['problem'] = false;
        }

        if ($aResponse['stw_action'] == 'noretry' && !$aResponse['exists']) {
            $aResponse['error'] = true;
        } else {
            $aResponse['error'] = false;
        }

        // if we use the advanced api for free account we get an invalid request
        if ($aResponse['stw_response_code'] == 'INVALID_REQUEST') {
            $aResponse['invalid'] = true;
        } else {
            $aResponse['invalid'] = false;
        }

        // if our domain or IP is not listed in the account's "Allowed Referrers" AND "Lock to Account" is enabled, then we get this error
        if ($aResponse['stw_response_code'] == 'LOCK_TO_ACCOUNT') {
            $aResponse['locked'] = true;
        } else {
            $aResponse['locked'] = false;
        }

        return $aResponse;
    }

    function _getLegacyResponse($sSearch, $s)
    {
        $sRegex = '/<[^:]*:' . $sSearch . '[^>]*>[^<]*<[^:]*:StatusCode[^>]*>([^<]*)<\//';
        if (preg_match($sRegex, $s, $sMatches)) {
            return $sMatches[1];
        }
        return false;
    }

    function _getThumbnailStatus($s)
    {
        $sRegex = '/<[^:]*:ThumbnailResult?[^>]*>[^<]*<[^:]*:Thumbnail\s*(?:Exists=\"((?:true)|(?:false))\")+[^>]*>([^<]*)<\//';
        if (preg_match($sRegex, $s, $sMatches)) {
            return array('stw_action' => $sMatches[1],
                         'thumbnail' => $sMatches[2]);
        }
        return false;
    }

    /**
     * Determine if specified file has expired from the cache
     */
    function _cacheFileExpired($sFile)
    {
        // Use setting to check age of files.
        $iCacheDays = CACHE_DAYS + 0;

        // dont update image once it is cached
        if ($iCacheDays == 0 && file_exists($sFile)) {
            return false;
        // check age of file and if file exists return false, otherwise recache the file
        } else {
            $iCutoff = time() - (3600 * 24 * $iCacheDays);
            return (!file_exists($sFile) || filemtime($sFile) <= $iCutoff);
        }
    }

    /**
     * Safe method to get the value from an array using the specified key
     */
    function _getArrayValue($aArray, $sKey, $isReturnSpace = false)
    {
        if ($aArray && isset($aArray[$sKey])) {
            return $aArray[$sKey];
        }

        // If returnSpace is true, then return a space rather than nothing at all
        if ($isReturnSpace) {
            return '&nbsp;';
        } else {
            return false;
        }
    }

    /**
     * store the Account XML response in an array
     */
    function _getAccXMLResponse($sResponse)
    {
        if (extension_loaded('simplexml')) { // If simplexml is available, we can do more stuff!
            $oDOM = new DOMDocument;
            $oDOM->loadXML($sResponse);
            $sXML = simplexml_import_dom($oDOM);
            $sXMLLayout = 'http://www.shrinktheweb.com/doc/stwacctresponse.xsd';

            // Pull response codes from XML feed
            $aResponse['stw_response_status'] = $sXML->children($sXMLLayout)->Response->Status->StatusCode; // Response Code
            $aResponse['stw_account_level'] = $sXML->children($sXMLLayout)->Response->Account_Level->StatusCode; // Account level
            // check for enabled upgrades
            $aResponse['stw_inside_pages'] = $sXML->children($sXMLLayout)->Response->Inside_Pages->StatusCode; // Inside Pages
            $aResponse['stw_custom_size'] = $sXML->children($sXMLLayout)->Response->Custom_Size->StatusCode; // Custom Size
            $aResponse['stw_full_length'] = $sXML->children($sXMLLayout)->Response->Full_Length->StatusCode; // Full Length
            $aResponse['stw_refresh_ondemand'] = $sXML->children($sXMLLayout)->Response->Refresh_OnDemand->StatusCode; // Refresh OnDemand
            $aResponse['stw_custom_delay'] = $sXML->children($sXMLLayout)->Response->Custom_Delay->StatusCode; // Custom Delay
            $aResponse['stw_custom_quality'] = $sXML->children($sXMLLayout)->Response->Custom_Quality->StatusCode; // Custom Quality
            $aResponse['stw_custom_resolution'] = $sXML->children($sXMLLayout)->Response->Custom_Resolution->StatusCode; // Custom Resolution
            $aResponse['stw_custom_messages'] = $sXML->children($sXMLLayout)->Response->Custom_Messages->StatusCode; // Custom Messages
        } else {
            // LEGACY SUPPPORT
            $aResponse['stw_response_status'] = _getLegacyResponse('Status', $sRemoteData);
            $aResponse['stw_account_level'] = _getLegacyResponse('Account_Level', $sRemoteData); // Account level
            // check for enabled upgrades
            $aResponse['stw_inside_pages'] = _getLegacyResponse('Inside_Pages', $sRemoteData); // Inside Pages
            $aResponse['stw_custom_size'] = _getLegacyResponse('Custom_Size', $sRemoteData); // Custom Size
            $aResponse['stw_full_length'] = _getLegacyResponse('Full_Length', $sRemoteData); // Full Length
            $aResponse['stw_refresh_ondemand'] = _getLegacyResponse('Refresh_OnDemand', $sRemoteData); // Refresh OnDemand
            $aResponse['stw_custom_delay'] = _getLegacyResponse('Custom_Delay', $sRemoteData); // Custom Delay
            $aResponse['stw_custom_quality'] = _getLegacyResponse('Custom_Quality', $sRemoteData); // Custom Quality
            $aResponse['stw_custom_resolution'] = _getLegacyResponse('Custom_Resolution', $sRemoteData); // Custom Resolution
            $aResponse['stw_custom_messages'] = _getLegacyResponse('Custom_Messages', $sRemoteData); // Custom Messages
        }

        return $aResponse;
    }
