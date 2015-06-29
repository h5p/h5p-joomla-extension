<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

$h5p_file_suffix = '?v=0.3.4';

jimport('h5pcore.h5pjoomla');

// load tooltip behavior
JHtml::_('behavior.tooltip');

$doc = JFactory::getDocument();

// Add core styles and scripts
$corebase   = JURI::root(true) . '/libraries/h5pcore/core/';
$editorbase = JURI::root(true) . '/libraries/h5pcore/editor/';

foreach (H5PCore::$styles as $style)
{
	$doc->addStyleSheet($corebase . $style . $h5p_file_suffix);
}
foreach (H5PCore::$scripts as $script)
{
	$doc->addScript($corebase . $script . $h5p_file_suffix);
}

// Add editor styles and scripts
foreach (H5peditor::$styles as $style)
{
	$doc->addStyleSheet($editorbase . $style . $h5p_file_suffix);
}
foreach (H5peditor::$scripts as $script)
{
	$doc->addScript($editorbase . $script . $h5p_file_suffix);
}

$doc->addScript(JURI::root(true) . '/libraries/h5pcore/js/h5pjoomla.js' . $h5p_file_suffix);
$doc->addScript(JURI::root(true) . '/libraries/h5pcore/js/h5peditorjoomla.js' . $h5p_file_suffix);
$doc->addScript(JURI::root(true) . '/libraries/h5pcore/editor/language/en.js' . $h5p_file_suffix);

$fileIconPath = JURI::root(true) . '/libraries/h5pcore/editor/images/binary-file.png';

$input = JFactory::getApplication()->input;
$contentId = $input->getCmd('cid');
if ($contentId == 'new')
{
	$header = 'Create new H5P';
	$contentId = uniqid('h5p-', true);
	$jsonContent = '{}';
	$mainLibrary = '';
}
else
{
	// Get Title, JSON and main library for given H5P content ID.
	$header = 'Edit H5P';
	$db = JFactory::getDbo();
	$db->setQuery(sprintf(
		"SELECT h.title, h.json_content, hl.machine_name as machineName, hl.major_version as majorVersion, hl.minor_version as minorVersion, hl.semantics
			FROM #__h5p as h, #__h5p_libraries as hl
			WHERE h.h5p_id=%s
			  AND h.main_library_id=hl.library_id",
		$db->quote($contentId)));
	$res = $db->loadAssoc();

	$h5p_core = H5PJoomla::getInstance('core');
	$mainLibrary = $h5p_core->libraryToString($res);
	$title = $res['title'];

	// Get H5P params as object
	$h5p_params = json_decode($res['json_content']);

	// Validate and filter against main library semantics.  This is done
	// after the hook above, to prevent modules that might inject invalid
	// data in the content.
	$h5p_joomla = H5PJoomla::getInstance('interface');
	$validator = H5PJoomla::getInstance('contentvalidator');
	$semantics = $h5p_joomla->getLibrarySemantics($res['machineName'], $res['majorVersion'], $res['minorVersion']);
	$validator->validateBySemantics($h5p_params, $semantics);
	$jsonContent = json_encode($h5p_params);
}

$h5p_path = JURI::root(true) . '/media/h5p';
$ajaxPath = JURI::root(true) . '/index.php?option=com_h5p&format=raw&task=';
$js = "var h5peditordata = {
	fileIcon: {
		path: \"{$fileIconPath}\",
		width: 50,
		height: 50
	},
	ajaxPath: \"{$ajaxPath}\",
	basePath: \"{$editorbase}\",
	contentId: \"{$contentId}\"
};
H5PIntegration = H5PIntegration || {};
H5PIntegration.jsonContentPath = \"{$h5p_path}/content/\";
H5PIntegration.libraryPath = \"{$h5p_path}/libraries/\";

// Joomla stylesheets interferes massively with our CSS due to its use of
// scoping everything in #main.  Therefore, rename #main within our little
// iFrame.
// PS: domready is Mootools. jQuery.ready() does not work properly in iFrames
window.addEvent('domready', function() {
	H5P.jQuery('#main').attr('id', 'joomlamain');
});
";

$doc->addScriptDeclaration($js);

$formaction = JURI::root(true) . '/index.php?option=com_h5p&task=save&tmpl=component&cid=' . $contentId;

?>
<h2 id="h5phead"><?php echo $header ?></h2>
<form id="h5peditor-form" method="post" action="<?php echo $formaction ?>">
	<div class="title-input">
		<label>
			<span>Title</span>
			<input type="text" name="h5p-title" title="Enter title for H5P content, used to identify it later." value="<?php echo $title ?>" />
		</label>
		<input id="h5p-form-save" type="submit" value="Save" />
	</div>
	<div class="h5p-editor">Waiting for javascript</div>

	<input name="edit-h5p-library" id="edit-h5p-library" type="hidden" value="<?php echo $mainLibrary ?>" />
	<input name="edit-h5p-params" id="edit-h5p-params" type="hidden" value="<?php echo htmlspecialchars($jsonContent) ?>" />
	<input name="h5p-content-id" id="h5p-content-id" type="hidden" value="<?php echo $contentId ?>" />
</form>
