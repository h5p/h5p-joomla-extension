<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the H5P Component
 */
class H5PViewH5P extends JView
{
	// Overwriting JView display method
	function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
			return false;
		}

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true) . '/libraries/h5pcore/media/h5piframes.css' . '?v=0.3.4');

		// Don't extrawrap the content if view mode.
		$isNotView = $this->getLayout() !== 'view';
		if ($isNotView) {
			print '<div id="h5pjoomlawrapper">';
		}

		// Display the view
		parent::display($tpl);

		if ($isNotView) {
			print '</div>';
		}
	}
}
