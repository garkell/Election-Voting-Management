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
//use \Joomla\CMS\WebAsset\WebAssetManager;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
//HTMLHelper::_('behavior.modal');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
// $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
// $wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
// $wa->registerAndUseStyle('gavotingItem', 'com_gavoting/item.css');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user       = Factory::getUser();
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');

if (!$canEdit && Factory::getUser()->authorise('core.edit.own', 'com_gavoting')) {
	$canEdit = Factory::getUser()->id == $this->item->created_by;
}
?>

<h2><?php echo $this->item->pos_name; ?></h2>

<div class="item_fields">

	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_CAT_ID'); ?></th>
			<td><?php echo $this->item->cat_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_ELECT_DATE'); ?></th>
			<td><?php echo $this->item->elect_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_ELECTED'); ?></th>
			<td><?php echo $this->item->elected; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAVOTING_FORM_LBL_POSITION_COMMENT'); ?></th>
			<td><?php echo nl2br($this->item->comment); ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gavoting&view=positions'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAVOTING_RETURN"); ?>
</a>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gavoting&task=position.edit&id='.$this->item->id); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAVOTING_EDIT_ITEM"); ?>
	</a>

<?php endif; ?>

<?php if($canDelete) : ?>
	<a class="btn btn-danger delete-button pull-right" href="<?php echo Route::_('index.php?option=com_gavoting&task=position.remove&id='.$this->item->id); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAVOTING_DELETE_ITEM"); ?>
	</a>
	<script type="text/javascript">
	
		jQuery(document).ready(function () {
			jQuery('.delete-button').click(deleteItem);
		});
	
		function deleteItem() {
	
			if (!confirm("<?php echo Text::_('COM_GAVOTING_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>

