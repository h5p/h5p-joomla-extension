<?php
/**
 * @package   H5P
 * @subpackage  plg_user_h5p
 * @copyright Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.utilities.date');
jimport('h5pcore.h5pjoomla');

/**
 * @package   Joomla.Plugin
 * @subpackage  User.profile
 * @version   1.6
 */
class plgUserH5P extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       2.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Remove H5P data for deleted user
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param array $user Holds the user data
	 * @param boolean $success True if user was succesfully stored in the database
	 * @param string $msg Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$db->setQuery('DELETE FROM #__h5p_status WHERE user_id = '.$userId);
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
		return true;
	}
}
