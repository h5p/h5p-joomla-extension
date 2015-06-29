<?php
/**
 * @package     H5P
 * @subpackage  com_h5p
 * @copyright   Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

/**
 * Hello Table class
 */
class H5PTableH5P extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__h5p', 'h5p_id', $db);
	}
}