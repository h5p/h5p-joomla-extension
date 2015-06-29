<?php
/**
 * @package     H5P
 * @subpackage  com_h5p
 * @copyright   Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * H5Ps Controller
 */
class H5PControllerH5PLibraries extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     * @since   2.5
     */
    public function getModel($name = 'H5P', $prefix = 'H5PLibraryModel')
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}
