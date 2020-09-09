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
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;

HTMLHelper::_('behavior.core');

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');

$date = new DateTime();
$config = Factory::getConfig();
$date->setTimezone(new DateTimeZone($config->get('offset')));
$today = date_format($date, $dateformat);

$user = Factory::getUser();
/*
echo '<pre><br />';
print_r($items);
echo '</pre>';
*/

?>

<div class="mod_gavoting<?php echo $moduleclass_sfx; ?>" >

    <div id="mentor_agreements<?php echo $module->id; ?>">
        <?php if ($disp_mentors) : ?>
        	<table class="table-bordered table-striped" style="margin: 0 auto;" width="100%">
			<tr><th width="50%">Name</th><th width="50%">Address</th></tr>
			<?php foreach ($items as $item) : ?>
				<?php echo '<tr><td>'.$item->nomination.'</td><td>'.$item->nom_name.'</td></tr>'; ?>
	        <?php endforeach; ?>
	        </table>
        <?php endif; ?>
	</div>
	
</div>
