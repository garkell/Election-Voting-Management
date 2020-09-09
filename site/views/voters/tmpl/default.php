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

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
$wa->registerAndUseStyle('gavotingList', 'com_gavoting/list.css');

$user       = Factory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');

?>

<h2><?php echo Text::_('COM_GAVOTING_TITLE_VOTERS'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="com-contact-categories categories-list">
	<table class="table table-striped" id="voterList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_VOTERS_USER_ID', 'v.name', $listDirn, $listOrder); ?>
			</th>
			<th class='center'>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_VOTERS_CAT_ID', 'cat_id_name', $listDirn, $listOrder); ?>
			</th>
			<th class='center'>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_VOTERS_PROXY_VOTE', 'a.proxy_vote', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th width="15%" class="center"><?php echo Text::_('COM_GAVOTING_ACTIONS'); ?></th>
			<?php endif; ?>

		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $canEdit = $user->authorise('core.edit', 'com_gavoting'); ?>

			<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gavoting')): ?>
				<?php $canEdit = Factory::getUser()->id == $item->created_by; ?>
			<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
						<?php if ($item->state == -2): ?>
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gavoting&task=voter.publish&id=' . $item->id . '&state=' . ((0) % 2), false, 2) : '#'; ?>">
						<?php else : ?>
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gavoting&task=voter.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
						<?php endif; ?>
						<?php if ($item->state == 1): ?>
							<i class="icon-publish" title="Active"></i>
						<?php elseif ($item->state == 0): ?>
							<i class="icon-unpublish" title="Not Active"></i>
						<?php elseif ($item->state == -2): ?>
							<i class="icon-trash" title="Trashed"></i>
						<?php elseif ($item->state == 2): ?>
							<i class="icon-archive" title="Archived"></i>
						<?php endif; ?>
						</a>
					</td>
				<?php endif; ?>

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'voters.', $canCheckin); ?>
					<?php endif; ?>
					<a style="color:#3c4d91;" href="<?php echo Route::_('index.php?option=com_gavoting&view=voter&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->user_id_name); ?>
					</a>
				</td>
				<td class="center">
					<?php echo $item->cat_id_name; ?>
				</td>
				<td class="center">
					<?php if ($item->proxy_vote) { echo 'Yes ('.$item->created_by_name.')'; } else { echo 'No'; } ?>
				</td>

				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.edit&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini" type="button" title="Edit Record"><i class="icon-edit" ></i></a> &nbsp; &nbsp;
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.remove&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini delete-button" type="button" title="Delete Record"><i class="icon-trash" ></i></a> &nbsp; &nbsp;
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.archive&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini" type="button" title="Archive Record"><i class="icon-archive" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
    </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_ADD_ITEM'); ?>
		</a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
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
