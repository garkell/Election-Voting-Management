<?php

/**
 * @version    4.0.01
 * @package    Com_Gavoting
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// insert this to have pagination selection available
$pages = $data['view']->get('pagination');
//echo '<pre>'; print_r($pages); echo '</pre>';
// Check if any filter field has been filled
$filters       = false;
$filtered      = false;
$search_filter = false;
$show_filter = false;

if (isset($data['view']->filterForm))
{
	$filters = $data['view']->filterForm->getGroup('filter');
}

// Check if there are filters set.
if ($filters !== false)
{
	$filterFields = array_keys($filters);
	$filled       = false;

	foreach ($filterFields as $filterField)
	{
		$filterField = substr($filterField, 7);
		$filter      = $data['view']->getState('filter.' . $filterField);

		if (!empty($filter))
		{
			$filled = $filter;
		}

		if (!empty($filled))
		{
			$filtered = true;
			break;
		}
	}

	$search_filter = $filters['filter_search'];
	unset($filters['filter_search']);
}

$options = $data['options'];

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($options['filtersHidden']) ? $options['filtersHidden'] : empty($data['view']->activeFilters) && !$filtered,
	'defaultLimit'        => isset($options['defaultLimit']) ? $options['defaultLimit'] : Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
HTMLHelper::_('searchtools.form', $formSelector, $data['options']);
?>

<div class="js-stools" style="margin-bottom: 20px;">
	<div class="clearfix">
		<div class="js-stools-container-bar" style="width:100%;">
			<label for="filter_search" class="element-invisible float-left"
				aria-invalid="false"><?php echo Text::_('COM_GAVOTING_SEARCH_FILTER_SUBMIT'); ?></label>

			<div class="btn-wrapper form-inline float-left">
				<?php echo $search_filter->input; ?>
				<button type="submit" class="btn btn-primary hasTooltip float-left" title=""
					data-original-title="<?php echo Text::_('COM_GAVOTING_SEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>
			</div>
			<?php if ($show_filter): ?>
				<?php if ($filters): ?>
					<div class="btn-wrapper float-left hidden-phone" style="margin-left: 5px;">
						<button type="button" class="btn btn-info hasTooltip js-stools-btn-filter" title=""
							data-original-title="<?php echo Text::_('COM_GAVOTING_SEARCH_TOOLS_DESC'); ?>">
							<?php echo Text::_('COM_GAVOTING_SEARCH_TOOLS'); ?> <i class="caret"></i>
						</button>
					</div>
				<?php endif; ?>
			<?php endif; ?>

			<div class="btn-wrapper float-left" style="margin-left: 5px;">
				<button type="button" class="btn btn-secondary hasTooltip js-stools-btn-clear" title=""
					data-original-title="<?php echo Text::_('COM_GAVOTING_SEARCH_FILTER_CLEAR'); ?>"
					onclick="jQuery(this).closest('form').find('input').val('');submit();">
					<?php echo Text::_('COM_GAVOTING_SEARCH_FILTER_CLEAR'); ?>
				</button>
			</div>

			<?php // insert this to have pagination selection available   ?>
			<div class="btn-group float-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $pages->getLimitBox(); ?>
			</div>
		</div>
	</div>
	<!-- Filters div -->
	<?php if ($show_filter): ?>
		<div class="js-stools-container-filters hidden-phone clearfix" style="">
			<?php // Load the form filters ?>
			<?php if ($filters) : ?>
				<?php foreach ($filters as $fieldName => $field) : ?>
					<?php if ($fieldName != 'filter_search') : ?>
						<div class="js-stools-field-filter">
							<?php echo $field->renderField(array('hiddenLabel' => false)); ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
