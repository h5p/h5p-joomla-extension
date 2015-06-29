<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th>
		<?php echo JText::_('COM_H5P_MANAGER_H5P_TITLE'); ?>
	</th>
	<th>
		<?php echo JText::_('COM_H5P_MANAGER_H5P_LIBRARY'); ?>
	</th>
	<th>
		<?php echo JText::_('COM_H5P_MANAGER_H5P_ARTICLE_USAGE'); ?>
	</th>
  <th>
		<?php echo JText::_('COM_H5P_MANAGER_H5P_USAGE_STATISTICS'); ?>
	</th>
</tr>
