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
 * Error handler object
 */


/**
 * constructor
 *		o - HTML Error object
 */
function BxError (o)
{
	alert(o.message + "\n" + o.description);
}

/**
 * constructor
 *		s1 - error message
 *		s2 - error description
 */
function BxError (s1, s2)
{
	alert(s1 + "\n" + s2);
}


