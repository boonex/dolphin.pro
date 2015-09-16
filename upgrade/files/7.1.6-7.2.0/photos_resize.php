<?php

require_once( './../../../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'images.inc.php' );

$_page['name_index'] = 17;

check_logged();

$_page['header'] = $_page['header_text'] = 'Resize images from "Photos" module';

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode();

PageCode();

function PageCompPageMainCode()
{   
    $iLimit = 30;
    $aNewSizes = array(
        '_t.jpg' => array('w' => 240, 'h' => 240, 'square' => true),
        '_t_2x.jpg' => array('w' => 480, 'h' => 480, 'square' => true, 'new_file' => true),
    );

    $sNewFile = false;
    foreach ($aNewSizes as $sKey => $r) {
        if (isset($r['new_file'])) {
            $sNewFile = $sKey;
            break;
        }
    }

    $sPathPhotos = BX_DIRECTORY_PATH_MODULES . 'boonex/photos/data/files/';

    if ($GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `sys_modules` WHERE `uri` IN('photos')")) {

        $aRow = $GLOBALS['MySQL']->getFirstRow("SELECT `ID`, `Ext` FROM `bx_photos_main` ORDER BY `ID` ASC");
        $iCounter = 0;
        while (!empty($aRow)) {
            $sFileOrig = $sPathPhotos . $aRow['ID'] . '.' . $aRow['Ext'];
            $sFileNew = $sNewFile ? $sPathPhotos . $aRow['ID'] . $sNewFile : false;
            
            if ((!$sFileNew || !file_exists($sFileNew)) && file_exists($sFileOrig)) { // file isn't already processed and original exists
                // resize
                foreach ($aNewSizes as $sKey => $r) {
                    $sFileDest = $sPathPhotos . $aRow['ID'] . $sKey;
                    imageResize($sFileOrig, $sFileDest, $r['w'], $r['h'], true, $r['square']);
                }
                ++$iCounter;
            }

            if ($iCounter >= $iLimit)
                break;

            $aRow = $GLOBALS['MySQL']->getNextRow();
        }

        if (empty($aRow)) {
            $s = "All photos has been resized to the new dimentions.";
        }
        else {
            $s = "Page is reloading to resize next bunch of images...
            <script>
                setTimeout(function () {
                    document.location = '" . BX_DOL_URL_ROOT . "upgrade/files/7.1.6-7.2.0/photos_resize.php?_t=" . time() . "';
                }, 1000);
            </script>";
        }

    }
    else {
        $s = "Module 'Photos' isn't installed";
    }

    return DesignBoxContent($GLOBALS['_page']['header'], $s, $GLOBALS['oTemplConfig'] -> PageCompThird_db_num);
}
