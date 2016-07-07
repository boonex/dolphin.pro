<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolFtp
{
    var $_sHost;
    var $_sLogin;
    var $_sPassword;
    var $_sPath;
    var $_rStream;

    function __construct($sHost, $sLogin, $sPassword, $sPath = '/')
    {
        $this->_sHost = $sHost;
        $this->_sLogin = $sLogin;
        $this->_sPassword = $sPassword;
        $this->_sPath = $sPath . ('/' == substr($sPath, -1) ? '' : '/');
    }
    function connect()
    {
        $this->_rStream = ftp_connect($this->_sHost);
        if($this->_rStream === false)
        	return false;

        return @ftp_login($this->_rStream, $this->_sLogin, $this->_sPassword);
    }
    function isDolphin()
    {
        return @ftp_size($this->_rStream, $this->_sPath . 'inc/header.inc.php') > 0;
    }
    function copy($sFilePathFrom, $sFilePathTo)
    {
        $sFilePathTo = $this->_sPath . $sFilePathTo;
        return $this->_copyFile($sFilePathFrom, $sFilePathTo);
    }
    function delete($sPath)
    {
        $sPath = $this->_sPath . $sPath;
        return $this->_deleteDirectory($sPath);
    }
	function setPermissions($sPath, $sMode)
	{
    	$sPath = $this->_sPath . $sPath;
    	return $this->_setPermissions($sPath, $sMode);
    }

    function _copyFile($sFilePathFrom, $sFilePathTo)
    {
        if(substr($sFilePathFrom, -1) == '*')
            $sFilePathFrom = substr($sFilePathFrom, 0, -1);
        $bResult = false;
        if (is_file($sFilePathFrom)) {
            if ($this->_isFile($sFilePathTo)) {
                $aFileParts = $this->_parseFile($sFilePathTo);
                if (isset($aFileParts[0])) {
                    $bRet = $this->_ftpMkDirR($aFileParts[0]);
                }
                $bResult = @ftp_put($this->_rStream, $sFilePathTo, $sFilePathFrom, FTP_BINARY);
            } else if($this->_isDirectory($sFilePathTo)) {
                $bRet = $this->_ftpMkDirR($sFilePathTo);
                $aFileParts = $this->_parseFile($sFilePathFrom);
                if (isset($aFileParts[1])) {
                    $bResult = @ftp_put($this->_rStream, $this->_validatePath($sFilePathTo) . $aFileParts[1], $sFilePathFrom, FTP_BINARY);
                }
            }
        } else if(is_dir($sFilePathFrom) && $this->_isDirectory($sFilePathTo)) {
            $bRet = $this->_ftpMkDirR($sFilePathTo);
            $aInnerFiles = $this->_readDirectory($sFilePathFrom);
            foreach($aInnerFiles as $sFile)
                $bResult = $this->_copyFile($this->_validatePath($sFilePathFrom) . $sFile, $this->_validatePath($sFilePathTo) . $sFile);
        } else {
            $bResult = false;
        }

        return $bResult;
    }
    function _readDirectory($sFilePath)
    {
        if(!is_dir($sFilePath) || !($rSource = opendir($sFilePath))) return false;

        $aResult = array();
        while(($sFile = readdir($rSource)) !== false) {
            if($sFile == '.' || $sFile =='..' || $sFile[0] == '.') continue;
            $aResult[] = $sFile;
        }
        closedir($rSource);

        return $aResult;
    }
    function _deleteDirectory($sPath)
    {
        if ($this->_isDirectory($sPath)) {
            if (substr($sPath, -1) != '/')
                $sPath .= '/';

            if (($aFiles = @ftp_nlist($this->_rStream, $sPath)) !== false)
                foreach ($aFiles as $sFile)
                    if ($sFile != '.' && $sFile != '..')
                        $this->_deleteDirectory(false === strpos($sFile, '/') ? $sPath . $sFile : $sFile);

            if (!@ftp_rmdir($this->_rStream, $sPath))
                return false;

        } else if (!@ftp_delete($this->_rStream, $sPath)) {
            return false;
        }
        return true;
    }
    function _validatePath($sPath)
    {
        if($sPath && substr($sPath, -1) != '/' && $this->_isDirectory($sPath))
            $sPath .= '/';

        return $sPath;
    }
    function _parseFile($sFilePath)
    {
        $aParts = array();
        preg_match("/^([a-zA-Z0-9@~_\.\\\\\/:-]+[\\\\\/])([a-zA-Z0-9~_-]+\.[a-zA-Z]{2,8})$/", $sFilePath, $aParts) ? true : false;
        return count($aParts) > 1 ? array_slice($aParts, 1) : false;
    }
    function _isFile($sFilePath)
    {
        return preg_match("/^([a-zA-Z0-9@~_\.\\\\\/:-]+)\.([a-zA-Z]){2,8}$/", $sFilePath) ? true : false;
    }
    function _isDirectory($sFilePath)
    {
        return preg_match("/^([a-zA-Z0-9@~_\.\\\\\/:-]+)[\\\\\/]([a-zA-Z0-9~_-]+)[\\\\\/]?$/", $sFilePath) ? true : false;
    }
	protected function _setPermissions($sPath, $sMode)
	{
		$aConvert = array(
			'writable' => $this->_isDirectory($sPath) ? 0777 : 0666, 
			'executable' => 0777
		);

    	if(@ftp_chmod($this->_rStream, $aConvert[$sMode], $sPath) === false)
    		return false;

		return true;
    }
    function _ftpMkDirR($sPath)
    {
        $sPwd = ftp_pwd ($this->_rStream);
        $aParts = explode("/", $sPath);
        $sPathFull = '';
        if ('/' == $sPath[0]) {
            $sPathFull = '/';
            ftp_chdir($this->_rStream, '/');
        }
        foreach ($aParts as $sPart) {
            if (!$sPart)
                continue;
            $sPathFull .= $sPart;
            if ('..' == $sPart) {
                @ftp_cdup($this->_rStream);
            } elseif (!@ftp_chdir($this->_rStream, $sPart)) {
                if (!@ftp_mkdir($this->_rStream, $sPart)) {
                    ftp_chdir($this->_rStream, $sPwd);
                    return false;
                }
                @ftp_chdir($this->_rStream, $sPart);
            }
            $sPathFull .= '/';
        }
        ftp_chdir($this->_rStream, $sPwd);
        return true;
    }

}
