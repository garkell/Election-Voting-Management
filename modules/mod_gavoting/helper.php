<?php
/*
# ------------------------------------------------------------------------
# @version     4.0.01
# @copyright   Copyright (C) 2020. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

use \Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;

class modGavotingHelper
{
	var $items;

    /**
     * Retrieves records to display
     * @param array $params An object containing the module parameters
     * @access public
     */
    public static function getNominations( $params )
    {
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->registerAndUseStyle('gavoting', 'mod_gavoting/default.css');
        //HTMLHelper::_('stylesheet','mod_gavoting/default.css', false, true);

    	// Query the articles table to get the articles in the selected category
   		$db = Factory::getDbo();
   		$query = $db->getQuery(true);
		$query->select(' a.* ');
   		$query->from('#__gavoting_nominations AS a ');
		$query->where($db->quoteName('a.state') . ' = 1 ');
   		$query->order($db->quoteName('a.nomination').' asc');

   		$db->setQuery((string)$query);
     	if (!$db->execute()) {
            throw new Exception(500, $db->getErrorMsg());
		} else {
			$items = $db->loadObjectList();
		}

    	return $items;
    }

    public static function getAjax()
    {
        $app = Factory::getApplication();

        // Check for request forgeries
        if (!$app->getSession()->checkToken())
        {
            $app->enqueueMessage(Text::_('JINVALID_TOKEN'));
            $app->redirect('index.php');
        }

        // Check for user permissions (only example)
        if (!Factory::getUser()->authorise('core.admin'))
        {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
            $app->redirect('index.php?option=com_users&view=login');
        }

        self::exportToExcel();
    }

    public static function getResultSet()
    {           
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

		$query->select(' a.* ');
   		$query->from('#__gavoting_nominations AS a ');
		$query->where($db->quoteName('a.state') . ' = 1 ');
   		$query->order($db->quoteName('a.nomination').' asc');

        $db->setQuery($query);

        // load the results
        try
        {
            if (!$resultset = $db->loadAssocList()) {
                return [['Status' => 'No Rows Found']];
            }
            return $resultset;
        }
        catch (Exception $e)
        {
            return [['Error' => 'Syntax Failure']]; //, ['Message' => $e->getMessage()], ['Query' => $query->dump()]];
        }
    }

    public static function exportToExcel()
    {
        // define output settings
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=Report_" . JHtml::date('now', 'Y_m_d_h_i_s', 'Australia/Brisbane') . ".csv");  // create unique document name
        header("Pragma: no-cache");
        header("Expires: 0");

        // get db data
        $resultset = self::getResultSet();
        $column_heads = array_map('ucwords', str_replace('_', ' ', array_keys($resultset[0])));  // pretty up the column headings

        // write output
        $fp = fopen("php://output", "w");
        fputcsv ($fp, $column_heads);
        foreach ($resultset as $row)
        {
            fputcsv($fp, $row);
        }
        fclose($fp);

        // Close the application gracefully.
        Factory::getApplication()->close();
    }

    public static function showAsHTML()
    {
        $resultset    = self::getResultSet();
        $column_heads = array_map('ucwords', str_replace('_', ' ', array_keys($resultset[0])));  // pretty up the column headings

        echo '<h3>Report</h3>';
        echo '<form method="post" style="display:inline; margin-left:12px;">';
        echo '<input type="hidden" name="option" value="com_ajax">';
        echo '<input type="hidden" name="module" value="gavoting">';
        echo '<input type="hidden" name="format" value="raw">';
        HTMLHelper::_('form.token');
        echo '<button type="submit">Download</button>';
        echo '</form>';
        echo '<table>';
            echo "<tr><th>" , implode("</th><th>", $column_heads) , "</th></tr>";
            foreach ($resultset as $row) {
                echo "<tr><td>" , implode("</td><td>", array_map("htmlspecialchars", $row)) , "</td></tr>";
            }
        echo "</table>";
    }

}
?>
