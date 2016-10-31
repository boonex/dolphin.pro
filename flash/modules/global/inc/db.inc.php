<?php
/***************************************************************************
 *
 * IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
 * This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
 * This notice may not be removed from the source code.
 *
 ***************************************************************************/

/**
 * This class is needed to work with database.
 */
class BxDbConnect
{
    function getResult($sQuery)
    {
        return BxDolDb::getInstance()->res($sQuery);
    }

    function getArray($sQuery)
    {
        return BxDolDb::getInstance()->getRow($sQuery);
    }

    function getValue($sQuery)
    {
        return BxDolDb::getInstance()->getOne($sQuery);
    }

    function getLastInsertId()
    {
        return BxDolDb::getInstance()->lastId();
    }

    function escape($s)
    {
        return BxDolDb::getInstance()->escape($s, false);
    }
}

global $oDb;
$oDb = new BxDbConnect();

/*
 * Interface functions are needed to simplify the useing of BxDbConnect class.
 */
function getResult($sQuery)
{
    global $oDb;

    return $oDb->getResult($sQuery);
}

function getArray($sQuery)
{
    global $oDb;

    return $oDb->getArray($sQuery);
}

function getValue($sQuery)
{
    global $oDb;

    return $oDb->getValue($sQuery);
}

function getLastInsertId()
{
    global $oDb;

    return $oDb->getLastInsertId();
}

function getEscapedValue($sValue)
{
    global $oDb;

    return $oDb->escape($sValue);
}
