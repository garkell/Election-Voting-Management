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
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gavoting', JPATH_ADMINISTRATOR);

$user    = Factory::getUser();
$this->item->user = $user;
$this->item->canEdit = GavotingHelper::canUserEdit($this->item, $user);

//$today = GavotingHelper::getTodaysDate();
$this->item->nomsClosed = GavotingHelper::nominationsClosed();
$this->item->votingClosed = GavotingHelper::votingClosed();
$this->item->votingOpen = GavotingHelper::votingOpen();

$this->item->hasVoted = GavotingHelper::hasVoted($user->id);
$this->item->nominations = GavotingHelper::getNomination(0);
$this->item->params = $this->params;
$this->item->canSave = $this->canSave;
$this->item->form = $this->form;

$prevPos='';
$cntr=0;

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gavoting.test.data'));
echo '</pre>';
print_r($this->item);
Factory::getApplication()->setUserState('com_gavoting.test.data',null);
*/
?>

<div class="voter-edit front-end-edit">
<?php if ($this->item->hasVoted): ?>
	<h2><?php echo Text::_('COM_GAVOTING_NOMINATIONS_ALREADY_VOTED'); ?></h2>
<?php else: ?>
<?php if (!$this->item->nomsClosed): ?>
	<h2><?php echo Text::_('COM_GAVOTING_NOMINATIONS_STILL_OPEN'); ?></h2>
<?php else: ?>
	<?php if (!$this->item->canEdit) : ?>
		<h3>
			<?php throw new Exception(Text::_('COM_GAVOTING_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if ($this->item->votingClosed): ?>
			<h2><?php echo Text::_('COM_GAVOTING_VOTING_CLOSED'); ?></h2>
		<?php else: ?>
			<?php if (!$this->item->votingOpen): ?>
				<h2><?php echo Text::_('COM_GAVOTING_VOTING_NOT_OPEN'); ?></h2>
			<?php else : ?>
				<?php if (!empty($this->item->nominations) && $this->item->nominations > ''): ?>

					<?php /* **************************   Voting Form  *********************************/  ?>
					
					<?php echo LayoutHelper::render('default_voting', array('data' => $this->item), dirname(__FILE__)); ?>

				<?php else : ?>
					<?php echo Text::_('COM_GAVOTING_NO_NOMINATIONS'); ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
</div>
