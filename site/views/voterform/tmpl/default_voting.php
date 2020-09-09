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
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('gavotingCore', 'com_gavoting/gavoting.css');
$wa->registerAndUseStyle('gavotingForm', 'com_gavoting/form.css');

$user    = Factory::getUser();

$nominations = $displayData['data'];

$prevPos='';
$cntr=0;

/*
echo '<pre>Test<br />';
print_r($nominations);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gavoting.test.data'));
Factory::getApplication()->setUserState('com_gavoting.test.data',null);
*/
?>

<h2><?php echo Text::_('COM_GAVOTING_VOTER_SUBMIT_TEXT'); ?></h2>
<form id="form-voter"
	  action="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.save'); ?>"
	  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

	<input type="hidden" name="jform[id]" value="<?php echo $nominations->id; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $nominations->ordering; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $nominations->state; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $nominations->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $nominations->checked_out_time; ?>" />
	<input type="hidden" name="jform[created_by]" value="<?php echo $nominations->created_by; ?>" />
	<input type="hidden" name="jform[modified_by]" value="<?php echo $nominations->modified_by; ?>" />
	<input type="hidden" name="jform[created_date]" value="<?php echo $nominations->created_date; ?>" />
	<input type="hidden" name="jform[modified_date]" value="<?php echo $nominations->modified_date; ?>" />
	<input type="hidden" name="jform[user_id]" value="<?php echo $user->id; ?>" />
	<input type="hidden" name="jform[cat_id]" value="<?php echo $nominations->cat_id; ?>" />

	<div>
		<span style="color:red;"><?php echo Text::_('COM_GAVOTING_PROXY_MESSAGE'); ?><br />
			<?php if ($nominations->params->get('proxy_type') == 0) : ?>
				<?php if ($nominations->params->get('dupl_vote')) : ?>
					<?php echo Text::_('COM_GAVOTING_PROXY_GENERAL_DUPYES'); ?>
				<?php else : ?>
					<?php echo Text::_('COM_GAVOTING_PROXY_GENERAL_DUPNO'); ?>
				<?php endif; ?>
			<?php elseif ($nominations->params->get('proxy_type') == 1) : ?>
				<?php echo Text::_('COM_GAVOTING_PROXY_SPECIFIC'); ?>
			<?php elseif ($nominations->params->get('proxy_type') == 2) : ?>
				<?php echo Text::_('COM_GAVOTING_PROXY_HYBRID'); ?>
			<?php endif; ?>
		</span>
	</div>
	<div class="center"><?php echo Text::_('COM_GAVOTING_FORM_LBL_VOTER_SPACER'); ?></div>

	<?php echo $nominations->form->renderField('proxy_vote'); ?>
	<?php echo $nominations->form->renderField('proxy_for'); ?>

	<?php foreach ($nominations->nominations AS $nom) : $cntr++; ?>

		<?php if ($cntr == 1): $prevPos = $nom->pos_name; ?>
			<div class="control-group">
				<div class="control-label spacerheader">
					<label id="jform_nom_ids_lbl" for="jform_nom_ids_<?php echo $nom->position_id; ?>">
						<?php echo $nom->pos_name; ?>
					</label>
				</div>
				<div class="controls extrawide">
				<select id="jform_nom_ids_nom_id<?php echo $nom->position_id; ?>" 
					name="jform[nom_ids][nom_id<?php echo $nom->position_id; ?>]" 
					class="custom-select valid" aria-invalid="false" >
					<option value="0"> - Select a Nomination - </option>
					<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
		<?php else : ?>
			<?php if ($nom->pos_name != $prevPos): $prevPos = $nom->pos_name; ?>
					</select>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label spacerheader">
						<label id="jform_nom_ids_lbl" for="jform_nom_ids_<?php echo $nom->position_id; ?>">
							<?php echo $nom->pos_name; ?>
						</label>
					</div>
					<div class="controls extrawide">
					<select id="jform_nom_ids_nom_id<?php echo $nom->position_id; ?>" 
						name="jform[nom_ids][nom_id<?php echo $nom->position_id; ?>]" 
						class="custom-select valid" aria-invalid="false" >
						<option value="0"> - Select a Nomination - </option>
						<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
			<?php else : ?>
					<option value="<?php echo $nom->id; ?>"><?php echo $nom->nomination_name; ?></option>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<?php if ($nominations->canSave): ?>
				<button type="submit" class="validate btn btn-primary">
					<?php echo Text::_('JSUBMIT'); ?>
				</button>
			<?php endif; ?>
			<a class="btn btn-secondary"
			   href="<?php echo Route::_('index.php?option=com_gavoting&task=voterform.cancel'); ?>"
			   title="<?php echo Text::_('JCANCEL'); ?>">
				<?php echo Text::_('JCANCEL'); ?>
			</a>
		</div>
	</div>

	<input type="hidden" name="option" value="com_gavoting"/>
	<input type="hidden" name="task" value="voterform.save"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
