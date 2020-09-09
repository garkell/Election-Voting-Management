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
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Date\Date;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
$wa->registerAndUseStyle('gavotingList', 'com_gavoting/list.css');


// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user       = Factory::getUser();
$userId     = $user->id;
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gavoting');
$canEdit    = $user->authorise('core.edit', 'com_gavoting');
$canCheckin = $user->authorise('core.manage', 'com_gavoting');
$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
$canDelete  = $user->authorise('core.delete', 'com_gavoting');
$showVotes = $this->params->get('show_votes',0);

$ndate = new Date($this->params->get('close_noms'));
$odate = new Date($this->params->get('open_votes'));
$cdate = new Date($this->params->get('close_votes'));
$adate = new Date($this->params->get('agm_date'));

$nomsClosed = GavotingHelper::nominationsClosed();
$votingClosed = GavotingHelper::votingClosed();
$votingOpen = GavotingHelper::votingOpen();

?>
<h2><?php echo Text::_('COM_GAVOTING_TITLE_NOMINATIONS'); ?></h2>
<p>Important dates for this coming election at our AGM on <?php echo HtmlHelper::date($adate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?>:</p>
<ul>
	<li><?php echo Text::_('COM_GAVOTING_CLOSE_NOMS_LABEL').' '.HtmlHelper::date($ndate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
	<li><?php echo Text::_('COM_GAVOTING_OPEN_VOTES_LABEL').' '.HtmlHelper::date($odate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
	<li><?php echo Text::_('COM_GAVOTING_CLOSE_VOTES_LABEL').' '.HtmlHelper::date($cdate, Text::_('COM_GAVOTING_DISPLAY_DATETIME')); ?></li>
</ul>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="com-contact-categories categories-list">
	<table class="table table-striped" id="nominationList">
		<thead>
		<tr>
			<th width="25%" class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_POSITION_ID', 'a.position_id_name', $listDirn, $listOrder); ?>
			</th>
			<th width="25%" class='center'>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_NOMINATION', 'nom.name', $listDirn, $listOrder); ?>
			</th>
			<th width="15%" class="center">
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_NOM_ID', 'a.nom_name', $listDirn, $listOrder); ?>
			</th>
			<th width="15%" class="center">
				<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_SEC_ID', 'sec_name', $listDirn, $listOrder); ?>
			</th>
			<?php if ($canCheckin || $showVotes): ?>
				<th width="15%" class="center">
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAVOTING_NOMINATIONS_VOTES', 'a.votes', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<?php if ($canCheckin || $canDelete): ?>
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
				<?php $canEdit = Factory::getUser()->id == $item->nomination; ?>
			<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'nominations.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit): ?>
						<a href="<?php echo Route::_('index.php?option=com_gavoting&view=nomination&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->position_id_name); ?>
						</a>
					<?php else : ?>
						<?php echo $this->escape($item->position_id_name); ?>
					<?php endif; ?>
				</td>
				<td class="left">
					<?php echo $item->nom_name; ?>
					<?php if ($item->agreed == 0 && $item->nomination == $userId): ?>
						<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nomination.agree&id='.(int) $item->id, false, 2); ?>" class="btn btn-mini" type="button" title="Agree">
							<i class="icon-publish" ></i>
						</a>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->nominator_name; ?>
				</td>
				<td class="center">
					<?php echo $item->seconder_name; ?>
				</td>
				<?php if ($canCheckin || $showVotes): ?>
					<td class="center">
						<?php echo $item->votes; ?>
					</td>
				<?php endif; ?>

				<?php if ($canCheckin || $canDelete): ?>
					<td class="center">
						<?php if ($canCheckin): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.edit&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini" type="button" title="Edit Record"><i class="icon-edit" ></i></a> &nbsp; &nbsp;
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.remove&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini delete-button" type="button" title="Delete Record"><i class="icon-trash" ></i></a> &nbsp; &nbsp;
							<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.archive&id=' . $item->id, false, 2); ?>" class="btn-secondary btn-mini" type="button" title="Archive Record"><i class="icon-archive" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
    </div>
	<?php if ($canCreate && !$nomsClosed) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=nominationform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_ADD_NOMINATION'); ?>
		</a>
	<?php endif; ?>
	<?php if ($canCreate && $votingOpen && !$votingClosed) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.edit&id=0', false, 0); ?>"
		   class="btn btn-warning btn-small"><i class="icon-plus"></i> <?php echo Text::_('COM_GAVOTING_CAST_VOTE'); ?>
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
