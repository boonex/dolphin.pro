<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php' );

class BxDolProfileQuery extends BxDolDb
{
    function __construct()
    {
        parent::__construct();
    }

    function getIdByEmail( $sEmail )
    {
        $sEmail = process_db_input($sEmail, BX_TAGS_STRIP);
        return $this -> getRow( "SELECT `ID` FROM " . BX_DOL_TABLE_PROFILES . " WHERE `Email` = ?", [$sEmail]);
    }

    function getIdByNickname( $sNickname )
    {
        $sNickname = process_db_input( $sNickname, BX_TAGS_STRIP );
        return $this -> getRow( "SELECT `ID` FROM " . BX_DOL_TABLE_PROFILES . " WHERE `NickName` = ?", [$sNickname]);
    }

    function getProfileDataById( $iID )
    {
        $iID = (int)$iID;
        return $this -> getRow( "SELECT * FROM " . BX_DOL_TABLE_PROFILES . " WHERE `ID` = ?", [$iID]);
    }

    function getNickName( $iID )
    {
        return $this -> getOne( "SELECT `NickName` FROM " . BX_DOL_TABLE_PROFILES . " WHERE `ID` = ?", [$iID]);
    }

}
