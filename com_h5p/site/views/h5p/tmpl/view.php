<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

jimport('h5pcore.h5pjoomla');

// load tooltip behavior
JHtml::_('behavior.tooltip');

$h5pjoomla = H5PJoomla::getInstance('interface');
$h5p_core = H5PJoomla::getInstance('core');
$h5p_cv = H5PJoomla::getInstance('contentvalidator');
$h5p_path = JURI::root(true) . '/media/h5p/';
$h5p_file_suffix = '?v=0.3.4';

// We need to add scripts and styles. Note that we don't add
// anything if the article does not have any H5P
$doc = JFactory::getDocument();
// Add Library scripts and styles
foreach (H5PCore::$styles as $style)
{
	$doc->addStyleSheet(JURI::root(true).'/libraries/h5pcore/core/' . $style . $h5p_file_suffix);
}
foreach (H5PCore::$scripts as $script)
{
	$doc->addScript(JURI::root(true).'/libraries/h5pcore/core/' . $script . $h5p_file_suffix);
}
$doc->addScript(JURI::root(true).'/libraries/h5pcore/js/h5pjoomla.js' . $h5p_file_suffix);
$doc->addScript(JURI::root(true).'/libraries/h5pcore/js/jquery.ba-resize.min.js' . $h5p_file_suffix);

$user = JFactory::getUser();

$script = 'H5PIntegration = H5PIntegration || {};';
$script .= 'H5PIntegration.content = H5PIntegration.content || {};';
$script .= 'H5PIntegration.jsonContentPath = "' . $h5p_path . 'content/";';
$script .= 'H5PIntegration.libraryPath = "' . $h5p_path . 'libraries/";';
$script .= 'H5P.ajaxPath = "' . JURI::root(true) . '/index.php?option=com_h5p&format=raw&task=";';
$script .= 'H5P.postUserStatistics = ' . ($user->get('id') === 0 ? 'false' : 'true') . ';';

$input = JFactory::getApplication()->input;
$h5pId = $input->getCmd('cid');

$db = JFactory::GetDbo();
$db->setQuery(sprintf(
	"SELECT h.json_content, hl.machine_name, hl.fullscreen, hl.semantics
	  FROM #__h5p as h, #__h5p_libraries as hl
	  WHERE h.h5p_id = %s
	    AND hl.library_id = h.main_library_id",
	$db->quote($h5pId)));
$h5p = $db->loadObject();

// Add dependent lib javascripts
$db->setQuery(sprintf(
	"SELECT hl.library_id, hl.machine_name as machineName, hl.major_version as majorVersion,
	        hl.minor_version as minorVersion, hl.patch_version as patchVersion, preloaded_css, preloaded_js, hnl.drop_css
	  FROM #__h5p_library_dependencies hnl
	  JOIN #__h5p_libraries hl
	    ON hnl.library_id = hl.library_id
	  WHERE hnl.h5p_id = %s
	    AND hnl.preloaded = 1",
	$db->quote($h5pId)
));
$res = $db->loadAssocList();
foreach ($res as $library_data)
{
	$libname = $h5p_core->libraryToString($library_data, TRUE);
	if (!empty($library_data['preloaded_js']))
	{
		foreach (explode(',', $library_data['preloaded_js']) as $value)
		{
			$doc->addScript($h5p_path . 'libraries/' . $libname . '/' . trim($value) . '?pv=' . $library_data['patchVersion']);
		}
	}
	if (!empty($library_data['preloaded_css']) && $library_data['drop_css'] != 1)
	{
		foreach (explode(',', $library_data['preloaded_css']) as $value)
		{
			$doc->addStyleSheet($h5p_path . 'libraries/' . $libname . '/' . trim($value) . '?pv=' . $library_data['patchVersion']);
		}
	}
}

// Filter JSON content through content validator.
$semantics = json_decode($h5p->semantics);
$h5p_params = json_decode($h5p->json_content);
$h5p_cv->validateBySemantics($h5p_params, $semantics);
$json_content = json_encode($h5p_params);

// Inject JSON and other settings for this h5p.
$script .= "H5PIntegration.content['{$h5pId}'] = {
	json: '{$db->getEscaped($json_content)}',
	fullscreen: '{$h5p->fullscreen}',
	mainLibrary: '{$h5p->machine_name}'
};";

$doc->addScriptDeclaration($script);

?>
<div class="h5p-content" data-content-id="<?php echo $h5pId ?>" data-class="<?php echo $h5p->machine_name ?>"></div>
