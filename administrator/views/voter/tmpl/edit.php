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


HTMLHelper::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/html');
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

?>

<form
	action="<?php echo Route::_('index.php?option=com_gavoting&view=voter&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="voter-form" class="form-validate form-horizontal">

	
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'voter')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'voter', Text::_('COM_GAVOTING_TAB_VOTER', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_GAVOTING_FIELDSET_VOTER'); ?></legend>
				<?php echo $this->form->renderField('user_id'); ?>
				<?php echo $this->form->renderField('cat_id'); ?>
				<?php echo $this->form->renderField('proxy_vote'); ?>
				<?php echo $this->form->renderField('created_by'); ?>
				<?php echo $this->form->renderField('created_date'); ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAVOTING_TAB_SYSINFO', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<?php echo $this->form->renderField('comment'); ?>
				<?php echo $this->form->renderField('modified_by'); ?>
				<?php echo $this->form->renderField('modified_date'); ?>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('state'); ?>
				<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
