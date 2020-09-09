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
 * View to edit
 *
 * @since  1.6
 */
class GavotingViewPosition extends BaseHtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 * @param   string  $tpl  Template name
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

        // Import CSS - best practice way to get css via media API
        HTMLHelper::_('stylesheet','com_gavoting/gavoting.css', false, true);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return void
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user  = Factory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out)) {
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		} else {
			$checkedOut = false;
		}

		//$canDo = GavotingHelper::getActions();
		$canDo = ContentHelper::getActions('com_gavoting','component',$this->item->id);

		ToolbarHelper::title(Text::_('COM_GAVOTING_TITLE_POSITION'), 'position.png');

		// Build the actions for new and existing records.
		if ($isNew) {
			// For new records, check the create permission.
			if ($isNew && ($user->authorise('core.create'))) {
				ToolbarHelper::apply('position.apply');

				ToolbarHelper::saveGroup(
					[
						['save', 'position.save'],
						['save2new', 'position.save2new']
					],
					'btn-success'
				);
			}

			ToolbarHelper::cancel('position.cancel');
		} else {
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			$itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $user->id);

			$toolbarButtons = [];

			// Can't save the record if it's checked out and editable
			if (!$checkedOut && $itemEditable) {
				ToolbarHelper::apply('position.apply');

				$toolbarButtons[] = ['save', 'position.save'];

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) {
					$toolbarButtons[] = ['save2new', 'position.save2new'];
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create')) {
				$toolbarButtons[] = ['save2copy', 'position.save2copy'];
			}

			ToolbarHelper::saveGroup( $toolbarButtons, 'btn-success' );
		}

		// Button for version control
		if ($this->state->params->get('save_history', 1) && $user->authorise('core.edit')) {
			ToolbarHelper::versions('com_gavoting.position', $this->item->id);
		}

		if (empty($this->item->id)) {
			ToolbarHelper::cancel('position.cancel', 'JTOOLBAR_CANCEL');
		} else {
			ToolbarHelper::cancel('position.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
