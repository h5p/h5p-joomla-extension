<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// load tooltip behavior
JHtml::_('behavior.tooltip');

?>
<h2 id="h5phead">H5P operations</h2>
<ul id="h5p-actions">
	<li class="h5p-action"><a href="index.php?option=com_h5p&view=h5p&layout=upload&tmpl=component">Upload file</a></li>
	<li class="h5p-action"><a href="index.php?option=com_h5p&view=h5p&layout=editor&tmpl=component&cid=new">Create new</a></li>
	<!-- li class="h5p-action"><a href="index.php?option=com_h5p&view=h5p&layout=selector&tmpl=component&selectmode=link">Link existing h5p</a></li -->
	<!-- li class="h5p-action"><a href="index.php?option=com_h5p&view=h5p&layout=selector&tmpl=component&selectmode=copy">Clone existing h5p</a></li -->
</ul>
<script type="text/javascript">
	var existing = window.parent.h5pGetEditorContent(),
		actions = document.getElementById('h5p-actions'),
		h5p_re = /\<img.*?h5p-placeholder-image.*?\>/img,
		cid_re = /data-content-id\=[\"\'](.*?)[\"\']/i,
		title_re = /title\=[\"\'](.*?)[\"\']/i,
		lib_re = /data-class\=[\"\'](.*?)[\"\']/i,
		matches, li, lia;

	while (matches = h5p_re.exec(existing)) {
		var cid = cid_re.exec(matches[0]);
		var title = title_re.exec(matches[0]);
		title = title ? title[1] : cid[1];

		// TODO: Make jQuery available here (guaranteed), and use that instead of hardcore JS.
		li = document.createElement('li');
		lia = document.createElement('a');
		lia.href = 'index.php?option=com_h5p&view=h5p&layout=editor&tmpl=component&cid=' + cid[1];
		// TODO: Lookup name of H5P with AJAX.
		lia.innerHTML = 'Edit "' + title + '"';
		li.appendChild(lia);
		li.className = "h5p-action";
		actions.appendChild(li);
	}

</script>
