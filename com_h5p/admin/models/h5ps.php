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
class H5PModelH5Ps extends JModelList
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		return "SELECT h.h5p_id, h.title, h.main_library_id, hl.machine_name, hl.major_version, hl.minor_version, hl.patch_version, count(hc.h5p_id) as article_count
			FROM #__h5p h
			  LEFT JOIN #__h5p_content hc ON hc.h5p_id=h.h5p_id
			  LEFT JOIN #__h5p_libraries hl ON h.main_library_id=hl.library_id
			GROUP BY h.h5p_id";
	}
}
