<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
jimport('h5pcore.model.h5p');

/**
 * H5P Model
 */
class H5PModelH5P extends JModelItem
{
	/**
	 * @var string msg
	 */
	protected $messages;

	/**
	 * Returns a reference to the Table object, always creating it.
	 *
	 * @param       type    The table type to instantiate
	 * @param       string  A prefix for the table class name. Optional.
	 * @param       array   Configuration array for model. Optional.
	 * @return      JTable  A database object
	 * @since       2.5
	 */
	public function getTable($type = 'H5P', $prefix = 'H5PTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the message
	 * @return string The message to be displayed to the user
	 */
	public function getMsg($id = 1)
	{
		if (!is_array($this->messages))
		{
			$this->messages = array();
		}

		if (!isset($this->messages[$id]))
		{
			//request the selected id
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->get('id', 1, 'INT' );

			// Get a TableHelloWorld instance
			$table = $this->getTable();

			// Load the message
			// $table->load($id);

			// Assign the message
			$this->messages[$id] = 'bob';//$table->greeting;
		}

		return $this->messages[$id];
	}

	/**
	 * Get H5P Object form db
	 */
	public function getH5P($id)
	{
		// Load H5P from db
		// return object
	}

	/**
	 * Get H5P JSON data.
	 */
	public function getJson($id)
	{
		// Load H5P from Database.
		// Return JSON string
	}
}
