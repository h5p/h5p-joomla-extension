<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import the Joomla modellist library
jimport('joomla.application.component.modellist');
/**
 * HelloWorldList Model
 */
class H5PModelStats extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
    $jinput = JFactory::getApplication()->input;
    $db =& JFactory::getDBO();
    $id = $jinput->get('id', '0', 'string');
		return "SELECT u.name, hs.started, hs.finished, hs.score, hs.max_score
			FROM #__h5p_status hs
      LEFT JOIN #__users u ON hs.user_id = u.id
      WHERE hs.h5p_id = " . $db->quote($id);
	}
}
