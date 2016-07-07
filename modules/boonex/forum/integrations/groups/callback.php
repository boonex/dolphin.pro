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

global $f;

$f->getUserPerm = 'getUserPermGroups';

function getUserPermGroups ($sUser, $sType, $sAction, $iForumId)
{
    $iMemberId = getLoggedId();

    $aPerm = BxDolService::call('groups', 'get_forum_permission', array ($iMemberId, $iForumId));
    $isOrcaAdmin = $aPerm['admin'];

    $isLoggedIn = $iMemberId || $isOrcaAdmin ? 1 : 0;

    $isPublicForumReadAllowed  =                $aPerm['read'];
    $isPublicForumPostAllowed  = $isLoggedIn && $aPerm['post'];
    $isPrivateForumReadAllowed = $isPublicForumReadAllowed;
    $isPrivateForumPostAllowed = $isPublicForumPostAllowed;
    $isEditAllAllowed = false;
    $isDelAllAllowed = false;

    return array (
        'read_public' => $isOrcaAdmin || $isPublicForumReadAllowed,
        'post_public' => $isOrcaAdmin || $isPublicForumPostAllowed ? 1 : 0,
        'edit_public' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'del_public'  => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,

        'read_private' => $isOrcaAdmin || $isPrivateForumReadAllowed ? 1 : 0,
        'post_private' => $isOrcaAdmin || $isPrivateForumPostAllowed ? 1 : 0,
        'edit_private' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'del_private'  => $isOrcaAdmin || $isDelAllAllowed ? 1 : 0,

        'edit_own' => 1,
        'del_own' => 1,

        'download_' => $isOrcaAdmin  || $isPublicForumReadAllowed ? 1 : 0,
        'search_' => 0,
        'sticky_' => $isOrcaAdmin,

        'del_topics_' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'move_topics_' => isAdmin() ? 1 : 0,
        'hide_topics_' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'unhide_topics_' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'hide_posts_' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
        'unhide_posts_' => $isOrcaAdmin || $isEditAllAllowed ? 1 : 0,
    );
}
