<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

//  language compiling class

class BxLang
{
    var $_sLang = 'en';
    var $_sSkin = 'default';
    var $_iVisualProcessing = true;

    function __construct ($sLang, $sSkin)
    {
        $this->_sLang = $sLang;
        $this->_sSkin = preg_replace ('#_\w{2}$#', '', $sSkin);
    }

    function setVisualProcessing ($i)
    {
        $this->_iVisualProcessing = $i;
    }

    function compile ()
    {
        $ret =  $this->_copyFromOrig ();

        $this->_cleanJsCache ();

        return $ret;
    }

    function _cleanJsCache ()
    {
        global $gConf;

        if (isset($gConf['dir']['cache'])) {
            $d = dir($gConf['dir']['cache']);

            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                @unlink ($gConf['dir']['cache'] . $entry);
            }

        }
    }

    function _copyFromOrig ()
    {
        global $gConf;

        // copy base
        if (!$this->_fullCopy ($gConf['dir']['layouts'] . 'base', $gConf['dir']['layouts'] . 'base_' . $this->_sLang))
            return false;

        // copy skins
        $sDirSkin = $gConf['dir']['layouts'] . $this->_sSkin;
        if (!$this->_fullCopy ($sDirSkin, $sDirSkin . '_' . $this->_sLang))
            return false;

        // copy classes
        $sDirClasses = preg_replace ('#classes/\w{2}/$#', 'classes/', $gConf['dir']['classes']);
        if (!$this->_fullCopy ($sDirClasses, $sDirClasses . $this->_sLang, false))
            return false;

        // copy javascripts
        $sDirJs = preg_replace ('#js/\w{2}/$#', 'js/', $gConf['dir']['js']);
        if (!$this->_fullCopy ($sDirJs, $sDirJs . $this->_sLang, false))
            return false;

        return true;
    }

    function _replaceVars ($sFilePath)
    {
        $s = $this->_fileGetContents ($sFilePath);

        $sExt = substr($sFilePath, -4);

        if ('.xsl' == $sExt || '.php' == $sExt || '.js' == substr($sFilePath, -3)) {
            $this->_replaceLangs ($s);
        }

        if ('.xsl' == $sExt || '.php' == $sExt || '.css' == $sExt) {
            $this->_replacePatches ($s);
        }

        if ('loader.php' == substr($sFilePath, -10)) {
            $s = str_replace ("'..'","'../..'", $s);
        }

        $this->_filePutContents ($sFilePath, $s);

        if ($this->_iVisualProcessing)
            echo ".";
    }

    function _replacePatches (&$s)
    {
        $s = str_replace (
            array(
                'base/',
                $this->_sSkin . '/'
            ),
            array(
                'base_' . $this->_sLang . '/',
                $this->_sSkin . '_' . $this->_sLang . '/'
            ),
            $s);
    }

    function _langReplaceHandler ($m)
    {
        return getLangString($m[1], $this->_sLang);
    }

    function _replaceLangs (&$s)
    {
        $s = preg_replace_callback ('#\[L\[(.*?)\]\]#', array($this, '_langReplaceHandler'), $s);
    }

    function _fullCopy ($source, $target, $recursively = true)
    {
        if (is_dir($source)) {
            @mkdir($target, 0777);
            @chmod($target, 0777);

            $d = dir($source);

            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    if ($recursively) {
                        if (!$this->_fullCopy($Entry, $target . '/' . $entry))
                            return false;
                    }
                } else {
                    if (!copy($Entry, $target . '/' . $entry))
                        return false;
                    @chmod($target . '/' . $entry, 0666);
                    $this->_replaceVars ($target . '/' . $entry);
                }
            }

            $d->close();
        } else {
            if (!copy($source, $target))
                return false;
            @chmod($target, 0666);
            $this->_replaceVars ($target);
        }

        return true;
    }

    function _fileGetContents ($sFilePath)
    {
        return file_get_contents ($sFilePath);
    }

    function _filePutContents ($sFilePath, $s)
    {
        $f = fopen ($sFilePath, 'w');
        if (!$f) return false;
        fwrite ($f, $s);
        fclose($f);
        return true;
    }
}

?>
