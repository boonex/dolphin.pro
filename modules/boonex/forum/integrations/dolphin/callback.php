<?php
/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/

/**
 *
 * redefine callback functions in Forum class
 *******************************************************************************/

$aPathInfo = pathinfo(__FILE__);
require_once ($aPathInfo['dirname'] . '/../base/callback.php');
