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
<?php foreach($this->items as $i => $item): ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->h5p_id); ?>
		</td>
		<td>
			<?php echo $item->title; ?>
		</td>
		<td>
			<?php echo "{$item->machine_name}-{$item->major_version}.{$item->minor_version}.{$item->patch_version}"; ?>
		</td>
		<td>
			<?php echo $item->article_count; ?>
		</td>
    <td>
			<a href="<?php echo JRoute::_('index.php?option=com_h5p&view=stats&id='. $item->h5p_id); ?>"><?php echo JText::_('COM_H5P_MANAGER_H5P_USAGE_STATISTICS'); ?></a>
		</td>
	</tr>
<?php endforeach; ?>
