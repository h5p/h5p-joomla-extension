<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * General Controller of H5P component
 */
class H5PController extends JController
{
	function __construct($config = array())
	{
		// Article frontpage Editor pagebreak proxying:
		if (JRequest::getCmd('view') === 'h5p') {
			$config['base_path'] = JPATH_COMPONENT_SITE;
		}

		parent::__construct($config);
	}

	/**
	 * display task
	 *
	 * @return void
	 */
	function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		$input = JFactory::getApplication()->input;
		$input->set('view', $input->getCmd('view', 'h5ps'));

		// call parent behavior
		parent::display($cachable);
	}
}
