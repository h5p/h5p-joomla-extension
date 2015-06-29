<?php
/**
 * @package     H5P
 * @subpackage  com_h5p
 * @copyright   Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');
$formaction = JURI::root(true) . '/index.php?option=com_h5p&task=upload&tmpl=component';
?>
<h2 id="h5phead">Upload H5P file</h2>
<form action="<?php echo $formaction ?>" method="post" enctype="multipart/form-data">
	<p><input type="file" name="h5p"/></p>
	<p><input type="submit"/></p>
	<p>PS: Maximum file size: <?php echo ini_get('upload_max_filesize') ?></p>
</form>
