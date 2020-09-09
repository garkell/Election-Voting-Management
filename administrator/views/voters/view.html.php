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
use \Joomla\CMS\MVC\View\GenericDataException;
use \Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Helper\ContentHelper;
use \Joomla\CMS\HTML\HTMLHelper;

/**
 * View class for a list of Gavoting.
 * @since  1.6
 */
class GavotingViewVoters extends BaseHtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 * @param   string  $tpl  Template name
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		GavotingHelper::addSubmenu('voters');

		$this->addToolbar();

        // Import CSS - best practice way to get css via media API
        HTMLHelper::_('stylesheet','com_gavoting/gavoting.css', false, true);
        HTMLHelper::_('stylesheet','com_gavoting/list.css', false, true);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		$canDo = ContentHelper::getActions('com_gavoting','component',0);

		ToolbarHelper::title(Text::_('COM_GAVOTING_TITLE_VOTERS'), 'voters.png');

		if ($canDo->get('core.create')) {
			$toolbar->addNew('voter.add');
		}

		if ($canDo->get('core.edit.state')) {
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-ellipsis-h')
				->buttonClass('btn btn-info')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('voters.publish')->listCheck(true);

			$childBar->unpublish('voters.unpublish')->listCheck(true);

			$childBar->archive('voters.archive')->listCheck(true);

			$childBar->checkin('voters.checkin')->listCheck(true);

			if ($this->state->get('filter.published') != -2) {
				$childBar->trash('voters.trash')->listCheck(true);
			}
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			$toolbar->delete('voters.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin')) {
			$toolbar->preferences('com_gavoting');
		}
	}

}
