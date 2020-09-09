<?php
/**
 * @version    4.0.01
 * @package    Com_Gavoting
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
$wa->registerAndUseStyle('gavotingItem', 'com_gavoting/item.css');

$user = Factory::getUser();
$canEdit = $user->authorise('core.edit', 'com_gavoting');
$canDelete = $user->authorise('core.delete', 'com_gavoting');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gavoting'))
{
	$canEdit = $user->id == $this->item->created_by;
}
?>

<h2>Voter Record</h2>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_USER_ID'); ?></th>
			<td><?php echo $this->item->user_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_CAT_ID'); ?></th>
			<td><?php echo $this->item->cat_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_PROXY_VOTE'); ?></th>
			<td>
				<?php if ($this->item->proxy_vote) : ?>
					<?php echo 'Yes ('.$this->item->created_by_name.')<br />'.HtmlHelper::date($this->item->created_date, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?>
				<?php else : ?>
					<?php echo 'No'; ?>
				<?php endif; ?>
			</td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gavoting&view=voters'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAVOTING_RETURN"); ?>
</a>


