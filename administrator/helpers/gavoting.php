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
use \Joomla\Data\DataObject;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Toolbar\Toolbar;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Categories\Categories;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Date\Date;

/**
 * Gavoting helper.
 *
 * @since  1.6
 */
class GavotingHelper
{
	/**
	 * Configure the Linkbar.
	 * @param   string  $vName  string
	 * @return void
	 */
	public static function addSubmenu($vName = '')
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_GAVOTING_TITLE_POSITIONS'),
			'index.php?option=com_gavoting&view=positions',
			$vName == 'positions'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_GAVOTING_TITLE_NOMINATIONS'),
			'index.php?option=com_gavoting&view=nominations',
			$vName == 'nominations'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_GAVOTING_TITLE_VOTERS'),
			'index.php?option=com_gavoting&view=voters',
			$vName == 'voters'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_GAVOTING_TITLE_CATEGORIES'),
			"index.php?option=com_categories&extension=com_gavoting",
			$vName == 'categories'
		);
		if ($vName=='categories') {
			ToolbarHelper::title('Voting: Categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 * @return    JObject
	 * @since    1.6
	 */
	public static function getActions()
	{
		// For Joomla 4, trying to use the below helper to get actions in the ViewHtml file
		//$canDo = ContentHelper::getActions('com_gavoting','component',$this->item->id);
		$user   = Factory::getUser();
		$result = new DataObject;

		$assetName = 'com_gavoting';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete', 'core.vote'
		);

		foreach ($actions as $action)
		{
			$result->__set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = Factory::getUser();

        if ($user->authorise('core.edit', 'com_gavoting')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gavoting') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	/**
	 * Get an instance of the named model
	 * @param   string  $name  Model name
	 * @return null|object
	 */
	public static function getSiteModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_gavoting/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_gavoting/models/' . strtolower($name) . '.php';
			$model = BaseDatabaseModel::getInstance($name, 'GavotingModel');
		}

		return $model;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gavoting/gavoting.xml'));

		return $componentXML['version'];
	}

    /**
     * Gets todays date based on global timezone settings
     */
    public static function getTodaysDate()
	{

		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		
		return $today;
	}

    /**
     * Gets a category name from id reference
     */
    public static function getCategoryName($id) {

        // get the club names from the database
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' title ');
		$query->from(' #__categories ');
		$query->where(' id = '.(int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
    }

	public static function getListOptions($table = '#__gavoting_positions', $label = 'Position', $field = 'name' )
	{
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
		$query->from( $db->quoteName($table) . ' AS a ' );
		$query->where(' a.state = 1' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getMembersOptions()
	{
        $params = ComponentHelper::getParams('com_gavoting');
        $single_mship = $params->get('single_mship',0);
        $proffield = $params->get('profile_field');
        $excl_user_gp = $params->get('excl_user_gp', 0);

        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		if ($single_mship) {
			$query->select(' "0" as "value", " - Select Member - " as "text" UNION SELECT a.id as value, CONCAT(a.name," - ",IF(p.profile_value is null, "", REPLACE(p.profile_value,"\"",""))) as "text" ');
			$query->from( $db->quoteName('#__users') . ' AS a ' );
			$query->join('LEFT',' #__user_profiles AS p ON p.user_id = a.id AND p.profile_key = '.$db->quote($proffield));
		} else {
			$query->select(' "0" as "value", " - Select Member - " as "text" UNION SELECT a.id as value, a.name as text ');
			$query->from( $db->quoteName('#__users') . ' AS a ' );
		}
		$query->where(' a.block = 0' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	    
	    // an array of objects is returned from the above
	    Factory::getApplication()->setUserState('com_gavoting.test.data',$options);
	    $objList = array();
	    $excl = false;
		// cycle through list to exclude based on group
	    foreach ($options AS $opt) {
			if ($opt->value) {
				$u = Factory::getUser($opt->value);
				if (is_array($excl_user_gp)) {
					foreach ($excl_user_gp AS $gp) {
						if (in_array($gp, $u->groups)) {
	                        $excl = true;
						}
					}
				}
				if (!$excl) {
					$objList[] = $opt;

				} else {
					$excl = false;
				}
			} else {
				$objList[] = $opt;
			}

		}

		return $objList;
	}

	public static function getProfileFieldsOptions()
	{
        $params = ComponentHelper::getParams('com_gavoting');
        $proflocal = $params->get('profile_suffix');
        $prof_key = 'profile'.$proflocal.'.';
        $keyLen = strlen($prof_key);
		$startkey = $keyLen+1;

		// get the user records in the supplier group
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select Field - " as "text" UNION SELECT profile_key as value, substr(profile_key,'.$startkey.') as text ');
		$query->from( $db->quoteName('#__user_profiles') );
		$query->where(' substr(profile_key,1,'.$keyLen.') = '.$db->Quote($prof_key) );
		$query->group(' profile_key ' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	/**
	 * Get the record
	 * @params integer Record id reference
	 * @return object list of records
	 */
	public static function getPosition($position = 0)
	{
		// get the broadcast types from the database
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, cat.title as election_year, CONCAT(cat.title," - ",a.pos_name) as position_name ');
		$query->from(' #__gavoting_positions AS a ');
		$query->join('LEFT',' #__categories AS cat ON cat.id = a.cat_id');
		$query->where('a.id = '.(int) $position);
		$db->setQuery((string)$query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

	}

	/**
	 * Get the record
	 * @params array of data from form
	 * @return object user of the nominee
	 */
	public static function getNominationName($data = 0)
	{
        $params = ComponentHelper::getParams('com_gavoting');
		$ntd = Factory::getUser($data['nomination']);
        $ntd->profile = UserHelper::getProfile($ntd->id);
		if ($params->get('single_mship', 0)) {
			if ($data['oth_mbr']) {
				$localprof = 'profile'.$params->get('profile_suffix');
				$prof_key = str_replace($localprof.'.', '', $params->get('profile_field'));
				$ntd->nom_name = $ntd->profile->$localprof[$prof_key];
			} else {
				$ntd->nom_name = $ntd->name;
			}
		} else {
			$ntd->nom_name = $ntd->name;
		}

		return $ntd;
	}

	/**
	 * Get the record
	 * @params integer Record id reference
	 * @return object list of records
	 */
	public static function getNomination($nomination = 0)
	{
		// get the broadcast types from the database
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, u.name as user_name, a.nom_name as nomination_name, CONCAT(cat.title," - ",p.pos_name) as position_id_name ');
		$query->select(' n.name as nominator_name, s.name as seconder_name, p.cat_id, p.pos_name ');
		$query->from(' #__gavoting_nominations AS a ');
		$query->join(' LEFT',' #__users AS u ON u.id = a.nomination ');
		$query->join(' LEFT',' #__users AS n ON n.id = a.nom_id ');
		$query->join(' LEFT',' #__users AS s ON s.id = a.sec_id ');
		$query->join(' LEFT',' #__gavoting_positions AS p ON p.id = a.position_id ');
		$query->join(' LEFT',' #__categories AS cat ON cat.id = p.cat_id ');
		if ($nomination) {
			$query->where(' a.id = '.(int) $nomination);
		}
		$query->where(' a.state = 1 ');
		$query->where(' a.agreed = 1 ');
		$query->order(' a.position_id ASC, a.nom_name ASC ' );

		$db->setQuery((string)$query);
	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

	}

	/**
	 * Check if nomination exists
	 * @params form data
	 * @return boolean
	 */
	public static function checkNomination($data)
	{
        $params = ComponentHelper::getParams('com_gavoting');
        $single_mship = $params->get('single_mship');
        $proffield = $params->get('profile_field');
        $proflocal = 'profile'.$params->get('profile_suffix');
        //$profprefix = 'profile'.$proflocal.'.';
        //$prof_key = $profprefix.$proffield;
        $u = Factory::getUser($data['nomination']);
        if ($single_mship) {
			if ($data['oth_mbr']) {
				$profile = UserHelper::getProfile($u->id);
				$prof_key = str_replace($proflocal.'.', '', $proffield);
				$nom_name = $profile->$proflocal[$prof_key];
			} else {
				$nom_name = $u->name;
			}
		} else {
			$nom_name = $u->name;
		}

		// count matching records to indicate nomination exists
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' count(id) ');
		$query->from(' #__gavoting_nominations ');
		$query->where(' state IN (0,1) ');
		$query->where(' nom_name = '.$db->Quote($nom_name));
		$query->where(' position_id = '. (int) $data['position_id']);

		$db->setQuery((string)$query);
	    try {
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

	}

	/**
	 * Check if user has voted
	 * @params integer user id reference
	 * @return boolean true on success
	 */
	public static function hasVoted($id = 0)
	{
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' count(id) ');
		$query->from(' #__gavoting_voters ');
		$query->where(' state = 1 ');
		$query->where(' user_id = '. (int) $id);

		$db->setQuery((string)$query);
	    try {
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
	}

	/**
	 * Get Election Year
	 * @return integer category id
	 */
	public static function getElectionYear()
	{
        $extension = 'com_gavoting';
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' id, title ');
		$query->from(' #__categories ');
		$query->where(' extension = '.$db->Quote($extension) );
		$query->where(' published = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
	}

	/**
	 * Update record
	 * @params array of nomination id references
	 * @return boolean true on success
	 */
	public static function addVoteToNomination($votes = 0)
	{

		// Get the record and then update the object.
		foreach ($votes as $key => $value) {
			if ($value) {
				$db		= Factory::getDbo();
				$db->setQuery((string)'SELECT * FROM #__gavoting_nominations WHERE id = '.$db->quote($value) );
				$nom = $db->loadObject();
				$nom->votes = $nom->votes + 1;
	
				Factory::getDbo()->updateObject('#__gavoting_nominations', $nom, 'id');
			}
		}
		
		return true;
	}

	/**
	 * Get the record
	 * @params integer Record id reference
	 * @return object list of records
	 */
	public static function getPositions()
	{
		// get the broadcast types from the database
        $db		= Factory::getDbo();
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from(' #__gavoting_positions AS a ');
		$query->where(' a.state = 1 ');
		$db->setQuery((string)$query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }

	}

	/**
	 * Setup the email notifications
	 * @params object nomination	user record plus profile
	 * @params object nominator		user record
	 * @params object seconder		user record
	 * @params int position			position_id reference
	 * @params int nom_id			nomination id reference
	 * @return boolean
	 */
    public static function sendNominationEmail($nomination = 0, $nominator = 0, $seconder = 0, $position = 0, $nom_id = 0, $params)
	{
		$ignNomEmail = false;
		$ignNeeEmail = false;
		$ignSecEmail = false;
		$link = Uri::base().'/index.php?option=com_gavoting&task=nomination.agree&id='.$nom_id;
		$pos = GavotingHelper::getPosition($position);
        $single_mship = $params->get('single_mship');
        $proffield = $params->get('profile_field');
        $proflocal = $params->get('profile_suffix');
        $profprefix = 'profile'.$proflocal.'.';
        $prof_key = $profprefix.$proffield;
        $auto_accept = $params->get('auto_accept',0);
        $ignore_prefix = $params->get('ignore_prefix','noemail');
        $emailLen = strlen($ignore_prefix);
        if ($emailLen > 0 && substr($nomination->email, 0, $emailLen) == $ignore_prefix ) {
			$ignNomEmail = true;
		}
        if ($emailLen > 0 && substr($nominator->email, 0, $emailLen) == $ignore_prefix ) {
			$ignNeeEmail = true;
		}
        if ($emailLen > 0 && substr($seconder->email, 0, $emailLen) == $ignore_prefix ) {
			$ignSecEmail = true;
		}

		if ($auto_accept && $nomination->id == $nominator->id) {
			// don't bother to send this email
		} else {
			$recipients = array($nomination->email);
			$subject = 'Nomination for Election';
			$nbody = '<p>Dear '.$nomination->nom_name.',</p><p>You have been nominated for the position of ';
			$nbody .= $pos->position_name.'</p><p>You were nominated by ';
			$nbody .= $nominator->name.' and seconded by '.$seconder->name.'.</p>';
			$nbody .= '<p>Please confirm your acceptance of this nomination by logging onto the website and then clicking on the link below.</p>';
			$nbody .= '<a href="'.$link.'" alt="" title="Agree Button">Accept Nomination</a>  Remember to Login to website before clicking link.';
			if (!$ignNomEmail) {
				GavotingHelper::sendEmail($recipients, $nbody, $subject, 0, 0);
			}
		}

		if (!$ignNeeEmail && !$ignSecEmail) {
			$recipients = array($nominator->email, $seconder->email);
		} elseif (!$ignNeeEmail && $ignSecEmail) {
			$recipients = array($nominator->email);
		} elseif ($ignNeeEmail && !$ignSecEmail) {
			$recipients = array($seconder->email);
		} else {
			return true;
		}

		$body = '<p>Dear '.$nominator->name.' and '.$seconder->name.',</p><p>You have nominated and seconded (respectively) ';
		$body .= $nomination->nom_name.' for the position of '.$pos->position_name.'</p>';
		$body .= '<p>The nominee has been advised by separate email and upon confirmation of acceptance, will appear on the ballot.</p>';
		GavotingHelper::sendEmail($recipients, $body, $subject, 0, 0);

        return true;
	}

    public static function sendEmail($recipients, $body, $subject, $attachfile, $replyTo = 0)
	{
		//$params = ComponentHelper::getParams('com_gausers');
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name
        $body .= '<p></p><p>'.$fromname.'</p>';

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHTML(true);
		$mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if (is_file($attachfile)) {
            $mail->addAttachment($attachfile);
        }
		if ($replyTo) {
            $mail->setReplyTo($replyTo);
        }
        $sent = $mail->Send();

        return true;
	}

	/**
	 * Check date for closing nominations
	 * @return  boolean true on closed
	 */
    public static function nominationsClosed()
	{
		$params = ComponentHelper::getParams('com_gavoting');
		$close_noms = new Date($params->get('close_noms'));
		$today = new Date();
		$close_noms = $today > $close_noms ? true : false;

		return $close_noms;
	}

	/**
	 * Check date for closing voting
	 * @return  boolean true on closed
	 */
    public static function votingClosed()
	{
		$params = ComponentHelper::getParams('com_gavoting');
		$close_votes = new Date($params->get('close_votes'));
		$today = new Date();
		$votingClosed = $today > $close_votes ? true : false;

		return $votingClosed;
	}

	/**
	 * Check date for closing voting
	 * @return  boolean true on closed
	 */
    public static function votingOpen()
	{
		$params = ComponentHelper::getParams('com_gavoting');
		$open_votes = new Date($params->get('open_votes'));
		$today = new Date();
		$votingOpen = $today > $open_votes ? true : false;

		return $votingOpen;
	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
		if (empty($member->partner) || $member->partner == '' || $member->partner == ' ') {
			$result = $member->name;
		} else {
			if (trim($member->surname) == trim($member->surnamep)) {
				$result = trim($member->firstname). ' & ' .trim($member->firstnamep) . ' ' . trim($member->surname);
			} else {
				$result = trim($member->name). ' & ' .trim($member->partner);
			}
		}

		return $result;

	}

	/**
	 * Get all the name fields as an object
	 * @param   id  $user id.
	 * @return  object	membership names object
	 */
    public static function breakdownNamesFromUserID($id = null)
	{
		$params = ComponentHelper::getParams('com_gavoting');
		$profsuf  = $params->get( 'profile_suffix' );
        $proffield = $params->get('profile_field');

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' substr(`name`, 1, LOCATE(" ",`name`)) AS firstname ');
        $query->select(' if(substr(`name`, (LOCATE(" ",`name`)+1), 1)="&",substr(`name`, LOCATE(" ",`name`,(LOCATE(" ",`name`)+3))+1),substr(`name`, LOCATE(" ",`name`)+1)) AS surname ');
        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
        $query->from('`#__users` AS a');
        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.'.$proffield.'" ');
        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			$member = false;
		}

		return $member;

	}

}

