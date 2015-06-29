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
			<?php echo $item->name; ?>
		</td>
		<td>
      <?php $date =& JFactory::getDate($item->started);
      echo $date->toFormat(); ?>
		</td>
		<td>
      <?php if ($item->finished) {
        $date =& JFactory::getDate($item->finished);
        echo $date->toFormat();
      } ?>
		</td>
    <td>
      <?php if ($item->finished) {
			  echo $item->score . ' / ' . $item->max_score;
      }?>
		</td>
	</tr>
<?php endforeach; ?>
