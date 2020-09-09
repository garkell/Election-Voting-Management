<?php
/*
# ------------------------------------------------------------------------
# @version     4.0.01
# @copyright   Copyright (C) 2020. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

require_once( dirname(__FILE__).'/helper.php' );

use \Joomla\CMS\Helper\ModuleHelper;

$items = modGavotingHelper::getNominations( $params );
require( ModuleHelper::getLayoutPath( 'mod_gavoting' ) );
$dload = modGavotingHelper::showAsHTML();

?>
