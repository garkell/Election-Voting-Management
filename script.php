<?php
/**
 * @version     4.0.01
 * @package     com_gavoting
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Installer\InstallerScript;
use \Joomla\CMS\Installer\Adapter\InstallerAdapter;
use \Joomla\CMS\Installer\Adapter\ComponentAdapter;
use \Joomla\CMS\Installer\Adapter\ModuleAdapter;
use \Joomla\CMS\Installer\Adapter\PluginAdapter;

class com_gavotingInstallerScript
{

	/**
	 * The extension name. This should be set in the installer script.
	 */
	protected $extension = 'com_gavoting';
	/**
	 * This version string. This should be set in the installer script.
	 */
	private $newRelease = '4.0.01';
	/**
	 * Minimum PHP version required to install the extension
	 */
	protected $minimumPhp = '7.2.10';
	/**
	 * Minimum Joomla! version required to install the extension
	 */
	protected $minimumJoomla = '4.0.0';
	/**
	 * Minimum MySQL version required to install the extension
	 */
	protected $minimumMySql = '5.7.14';
	/**
	 * List of required PHP extensions.
	 */
	protected $PHPextensions = array('dom', 'json', 'SimpleXML');
	/**
	 * @var  CMSApplication  Holds the application object
	 */
	private $app;
	/**
	 * @var  string  During an update, it will be populated with the old release version
	 */
	private $oldRelease;

	/**
	 *  Constructor
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
	}

	/**
	 * method to install the component
	 * @return void
	 */
	public function install($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_INSTALL_TEXT') . '</p>';

		/* --------------------------------  Action Log  ------------------------------ */
		// New action logging system
		//$extension = 'com_gavoting';
        $ActLog = $this->checkIfActionLog();
        if (!$ActLog) {
			$this->loadToActionLog();
			echo '<p>' . Text::_('Action Logging Setup') . '</p>';
		}
        $ALConf = $this->checkIfActionLogConfig();
        if (!$ALConf) {
			$this->loadToActionLogConfig('user_id', 'id', 'position_id', '#__gavoting_nominations','COM_GAVOTING_ACTION');
			echo '<p>' . Text::_('Action Log Configuration Setup') . '</p>';
		}
		/* -------------------------------------------------------------- */

        $this->installSearchPlugin($parent);
        $this->enablePlugin('search', 'gavoting');
        $this->installModule($parent);

        // this I think creates a main dashboard entry
		//$this->addDashboardMenu('kunena', 'kunena');
        
	}

	/**
	 * method to update the component
	 * @return void
	 */
	public function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_UPDATE_TEXT') . '</p>';

		/* --------------------------------  Action Log  ------------------------------ */
		// New action logging system
        $ActLog = $this->checkIfActionLog();
        if (!$ActLog) {
			$this->loadToActionLog();
			echo '<p>' . Text::_('Action Logging Setup') . '</p>';
		}
        $ALConf = $this->checkIfActionLogConfig();
        if (!$ALConf) {
			$this->loadToActionLogConfig('user_id', 'id', 'position_id', '#__gavoting_nominations','COM_GAVOTING_ACTION');
			echo '<p>' . Text::_('Action Log Configuration Setup') . '</p>';
		}
		/* -------------------------------------------------------------- */

        $this->installSearchPlugin($parent);
        $this->installModule($parent);

	}

	/**
	 * method to run before an install/update/uninstall method
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// the variable $type was returning lowercase values so have inserted STRTOUPPER
		$type = STRTOUPPER($type);
		echo '<p>' . Text::_('COM_GAVOTING_PREFLIGHT_' . $type . '_TEXT') . '</p>';

		if ($type == 'INSTALL')
		{
			// check if requirements are met
			//$this->checkRequirements();
		}

	}

	/**
	 * method to uninstall the component
	 * @return void
	 */
	function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . Text::_('COM_GAVOTING_UNINSTALL_TEXT') . '</p>';
		
		//$this->_removeMenu();
        $this->uninstallModPlug($parent, 'module', 'gavoting');
        $this->uninstallModPlug($parent, 'plugin', 'gavoting');

	}
 
	/**
	 * method to run after an install/update/uninstall method
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		// the variable $type was returning lowercase values so have inserted STRTOUPPER
		$type = STRTOUPPER($type);
		echo '<p>' . Text::_('COM_GAVOTING_POSTFLIGHT_' . $type . '_TEXT') . '</p>';

		// setup data for first time install
        if ($type == "INSTALL") {
            
            // Load categories
			$cattitles = array(
					"2020"
					);
            $cat_id = $this->createCategory($cattitles);
			$this->updatePositions($cat_id);
		}

        // After install/update redirect to component admin view
		$parent->getParent()->setRedirectUrl('index.php?option='.$this->extension);

		// Clear Joomla system cache.
		$cache = Factory::getCache();
		$cache->clean('_system');
	}

	/**
	 * @param   string  $parent  parent
	 * @return void
	 */
	public function discover_install($parent)
	{
		return self::install($parent);
	}

	/**
	 * @param   string  $group    group
	 * @param   string  $element  element
	 * @return boolean
	 */
	public function enablePlugin($group, $element)
	{
		$plugin = Table::getInstance('extension');
		if (!$plugin->load(array('type' => 'plugin', 'folder' => $group, 'element' => $element)))
		{
			return false;
		}
		$plugin->enabled = 1;
		return $plugin->store();
	}

	/**
	 * Check versions of components
	 * @return boolean|integer
	 */
	public function checkRequirements()
	{
		$db   = Factory::getDbo();
		$pass = $this->checkVersion('PHP', $this->getCleanPhpVersion());
		$pass &= $this->checkVersion('Joomla!', JVERSION);
		//$pass &= $this->checkVersion('MySQL', $this->minimumMySql);
		$pass &= $this->checkVersion('MySQL', $db->getVersion());
		$pass &= $this->checkDbo($db->name, array('mysql', 'mysqli'));
		//$pass &= $this->checkExtensions($this->PHPextensions);
		return $pass;
	}

	/**
	 *  On some hosting the PHP version given with the version of the packet in the distribution
	 * @return string
	 */
	protected function getCleanPhpVersion()
	{
		$version = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
		return $version;
	}

	/**
	 * @param   string  $name     name of the element
	 * @param   string  $version  version of the element
	 * @return boolean
	 * @throws Exception
	 */
	protected function checkVersion($name, $version)
	{
		$minor = $name == 'MySQL' ? $this->minimumMySql : 0;
		$app = Factory::getApplication();
		$major = $minor = 0;
		foreach ($this->versions[$name] as $major => $minor)
		{
			if (!$major || version_compare($version, $major, '<'))
			{
				continue;
			}
			if (version_compare($version, $minor, '>='))
			{
				return true;
			}
			break;
		}
		if (!$major)
		{
			$minor = reset($this->versions[$name]);
		}
		$recommended = end($this->versions[$name]);
		$app->enqueueMessage(sprintf("%s %s is not supported. Minimum required version is %s %s, but it is highly recommended to use %s %s or later.",
			$name, $version, $name, $minor, $name, $recommended
		), 'notice'
		);
		return false;
	}

	/**
	 * @param   string  $name   name
	 * @param   array   $types  types
	 * @return boolean
	 * @throws Exception
	 */
	protected function checkDbo($name, $types)
	{
		$app = Factory::getApplication();
		if (in_array($name, $types))
		{
			return true;
		}
		$app->enqueueMessage(sprintf("Database driver '%s' is not supported. Please use MySQL instead.", $name), 'notice');
		return false;
	}

	/**
	 * @param   array  $extensions  extensions
	 * @return integer
	 * @throws Exception
	 */
	protected function checkExtensions($PHPextensions)
	{
		$app = Factory::getApplication();
		$pass = 1;
		foreach ($PHPextensions as $name)
		{
			if (!extension_loaded($name))
			{
				$pass = 0;
				$app->enqueueMessage(sprintf("Required PHP extension '%s' is missing. Please install it into your system.", $name), 'notice');
			}
		}
		return $pass;
	}

	/**
	 * Update position records with category id on initial install
	 * @param   int $id   Category ID reference
	 * @return boolean
	 */
	public function updatePositions($cat_id = 0)
	{
        if ($cat_id) {
			$db = Factory::getDbo();
	        $db->setQuery(' UPDATE #__gavoting_positions SET cat_id = '.(int) $cat_id );
		    try {
		        $db->execute();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		        return false;
		    }
	    }

		return true;
	}

    /**
    * Function to create category records
    * @param array category titles
    * @param string category group or type
    * @return void
    */
    public function createCategory($cat_titles)
    {
		foreach ($cat_titles as $cat) {
            $category = Table::getInstance('Category');
            $category->extension = $this->extension;
            $category->title = $cat;
            $category->description = '';
            $category->published = 1;
            $category->access = 1;
            $category->params = '{"category_layout":"","image":"","image_alt":""}';
            $category->metadata = '{"page_title":"","author":"","robots":""}';
            $category->language = '*';
            // Set the location in the tree
            $category->setLocation(1, 'last-child');
            // Check to make sure our data is valid
            if (!$category->check()) {
                throw new Exception(500, $category->getError());
                return false;
            }
            // Now store the category
            if (!$category->store(true)) {
                throw new Exception(500, $category->getError());
                return false;
            }
	 	}
        // Build the path for our category
        $category->rebuildPath($category->id);
        echo '<p>' . Text::_('Categories created') . '</p>';
        return $category->id;
	}

	/**
	 * Install module and/or plugin for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	public function installSearchPlugin($parent)
	{
		$installer = new Installer;
		$installation_folder = $parent->getParent()->getPath('source');
		$path = $installation_folder . '/plugins/search/';

		if (!$this->isAlreadyInstalled('plugin', 'gavoting', 'search')) {
			$result = $installer->install($path);
		} else {
			$result = $installer->update($path);
		}

		if ($result) {
			$this->app->enqueueMessage('Installation of Search Plugin was successful.', 'message');
			return true;
		} else {
			$this->app->enqueueMessage('There was an issue installing the Search Plugin', 'error');
			return false;
		}

	}

	/**
	 * Install module and/or plugin for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
	public function installModule($parent)
	{
		$installer = new Installer;
		$installation_folder = $parent->getParent()->getPath('source');
		$path = $installation_folder . '/modules/';

		if (!$this->isAlreadyInstalled('module', 'mod_gavoting', null)) {
			$result = $installer->install($path);
		} else {
			$result = $installer->update($path);
		}

		if ($result) {
			$this->app->enqueueMessage('Installation of Module was successful.', 'message');
			return true;
		} else {
			$this->app->enqueueMessage('There was an issue installing the Module', 'error');
			return false;
		}

	}

	/**
	 * Install module and/or plugin for this component
	 * @param   mixed $parent Object who called the install/update method
	 * @return void
	 */
// 	public function installModPlug($parent, $modplug = 'module')
// 	{
// 		$installer = new Installer;
// 		$installation_folder = $installer->getPath('source');
// 		//$app = Factory::getApplication();
// 		$modplugs = $modplug.'s';
// 
// 		if (!empty($parent->getManifest->$modplugs)) {
// 			$modplugs = $parent->getManifest->$modplugs;
// 
// 			if (count($modplugs->children())) {
// 				foreach ($modplugs->children() as $mp) {
// 					$mpName = (string) $mp[$modplug];
// 					if ($modplug == 'plugin') {
// 						$mpGroup = (string) $mp['group'];
// 						$path = $installation_folder . '/'.$modplug.'s/' . $mpGroup;
// 					} else {
// 						$mpGroup = null;
// 						$path = $installation_folder . '/'.$modplug.'s/' . $mpName;
// 					}
// 
// 					//$installer  = new Installer;
// 
// 					if (!$this->isAlreadyInstalled($modplug, $mpName, $mpGroup)) {
// 						$result = $this->install($path);
// 					} else {
// 						$result = $this->update($path);
// 					}
// 
// 					if ($result) {
// 						$this->app->enqueueMessage($modplug . ' ' . $mpName . ' was installed successfully');
// 					} else {
// 						$this->app->enqueueMessage('There was an issue installing the ' . $modplug . ' ' . $mpName, 'error');
// 					}
// 				}
// 			}
// 		} else {
// 			$this->app->enqueueMessage('There was an issue installing the extension from ' . $test, 'error');
// 		}
// 	}

	/**
	 * Uninstalls modules
	 * @param   mixed $parent Object who called the uninstall method
	 * @return void
	 */
	public function uninstallModPlug($parent, $modplug = 'module', $mpName = '')
	{
		$modplugs = $modplug.'s';

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('extension_id');
		$query->from('#__extensions');
		if ($modplug == 'plugin') {
			$query->where(
					array (
						'type LIKE ' . $db->quote($modplug),
						'element LIKE ' . $db->quote($mpName),
						'folder LIKE ' . $db->quote('search')
					)
				);
		} else {
			$query->where(
					array (
						'type LIKE ' . $db->quote($modplug),
						'element LIKE ' . $db->quote('mod_'.$mpName)
					)
				);
		}
		$db->setQuery($query);
		$extension = $db->loadResult();

		if (!empty($extension)) {
			$installer = new Installer;
			$result    = $installer->uninstall($modplug, $extension);

			if ($result) {
				$this->app->enqueueMessage('Uninstalling the ' . $modplug . ' (' . $mpName . ') was successfully');
			} else {
				$this->app->enqueueMessage('There was an issue uninstalling the ' . $modplug . ' ' . $mpName, 'error');
			}
		} else {
			$this->app->enqueueMessage('Empty extension for ' . $modplug . ' ' . $mpName, 'warning');
		}
	}


	/**
	 * Check if an extension is already installed in the system
	 * @param   string $type   Extension type
	 * @param   string $name   Extension name
	 * @param   mixed  $folder Extension folder(for plugins)
	 * @return boolean
	 */
	public function isAlreadyInstalled($type, $name, $folder = null)
	{
		$result = false;

		switch ($type)
		{
			case 'plugin':
				$result = file_exists(JPATH_SITE . '/plugins/' . $folder . '/' . $name);
				break;
			case 'module':
				$result = file_exists(JPATH_SITE . '/modules/' . $name);
				break;
		}

		return $result;
	}

	/**
	 * Check if extension is set in Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	public function checkIfActionLog()
	{
		$result = false;

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_logs_extensions'))
			->where($db->quoteName('extension') .' = '. $db->Quote($this->extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	public function loadToActionLog()
	{
		$result = false;
        $db = Factory::getDbo();
        $db->setQuery(' INSERT into #__action_logs_extensions (extension) VALUES ('.$db->Quote($this->extension).') ' );
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return $result;
	}

	/**
	 * Check if extension is set in Action Logs register
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	public function checkIfActionLogConfig()
	{
		$result = false;

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__action_log_config'))
			->where($db->quoteName('type_alias') .' = '. $db->Quote($this->extension));
		$db->setQuery($query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

		return $result;
	}

	/**
	 * Insert extension to the Action Log Configuration record
	 * @param   string $extension   Extension name
	 * @return boolean
	 */
	public function loadToActionLogConfig($type, $key = 'id', $title, $tablename, $txtpref)
	{
		// Create and populate an object.
		$logConf = new stdClass();
		$logConf->id = 0;
		$logConf->type_title = $type;
		$logConf->type_alias = $this->extension;
		$logConf->id_holder = $key;
		$logConf->title_holder = $title;
		$logConf->table_name = $tablename;
		$logConf->text_prefix = $txtpref;

	    try {
	        // If it fails, it will throw a RuntimeException
			// Insert the object into the user profile table.
			$result = Factory::getDbo()->insertObject('#__action_log_config', $logConf);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return $result;
	}

}
