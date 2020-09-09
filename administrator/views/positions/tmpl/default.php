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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.formvalidator');

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
$wa->registerAndUseStyle('gavotingItem', 'com_gavoting/item.css');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gavoting');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gavoting&task=positions.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'positionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$version = GavotingHelper::getComponentVersion();
$editIcon = '<span class="fa fa-pen-square mr-2" aria-hidden="true"></span>';

?>

<form action="<?php echo Route::_('index.php?option=com_gavoting&view=positions'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container" class="j-main-container">

        <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="positionList">
				<caption id="captionTable">
					<?php echo Text::_('Version: '.$version); ?>
				</caption>
				<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
						<th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
					<?php endif; ?>
					<th width="1%" class="hidden-phone">
						<?php echo HTMLHelper::_('grid.checkall'); ?>
					</th>
					<?php if (isset($this->items[0]->state)): ?>
						<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>

					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_POS_NAME', 'a.pos_name', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_CAT_ID', 'a.cat_id', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ELECT_DATE', 'a.elect_date', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ELECTED', 'a.elected', $listDirn, $listOrder); ?>
					</th>
					<th class='center'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAVOTING_POSITIONS_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
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
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_gavoting');
					$canEdit    = $user->authorise('core.edit', 'com_gavoting');
					$canCheckin = $user->authorise('core.manage', 'com_gavoting');
					$canChange  = $user->authorise('core.edit.state', 'com_gavoting');
					?>
					<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder) :
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; ?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
										  title="<?php echo $disabledLabel ?>"><i class="icon-menu"></i>
									</span>
									<input type="text" style="display:none" name="order[]" size="5"
										   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive"><i class="icon-menu"></i></span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'positions.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>

						<td>
							<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'positions.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_gavoting&task=position.edit&id='.(int) $item->id); ?>">
									<?php echo $editIcon; ?> <?php echo $item->pos_name; ?>
								</a>
							<?php else : ?>
								<?php echo $item->pos_name; ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->cat_id_name; ?>
						</td>
						<td>
							<?php echo substr($item->elect_date,0,10); ?>
						</td>
						<td>
							<?php echo $item->elected; ?>
						</td>
						<td class="center">
							<?php echo $item->id; ?>
						</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		<?php endif; ?>
	</div>
</form>
