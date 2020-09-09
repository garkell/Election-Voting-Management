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

use \Joomla\CMS\MVC\Controller\BaseController;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_gavoting'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::registerPrefix('Gavoting', JPATH_ADMINISTRATOR . '/components/com_gavoting');
JLoader::register('GavotingHelper', JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/gavoting.php');
JLoader::register('GaupdparamsHelper', JPATH_ADMINISTRATOR . '/components/com_gavoting/helpers/gaupdparams.php');
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$controller = BaseController::getInstance('Gavoting');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
