<?php
/**
 * @package		H5P
 * @subpackage	plg_editorsxtd_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Editor H5P Plugin
 *
 * @package		H5P
 * @subpackage	Editors-xtd.H5P
 * @since		1.5
 */
class plgButtonH5P extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param	array	$config  An array that holds the plugin configuration
	 * @since	1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$doc = JFactory::getDocument();

		// This gets the getContent method, not actual content
		$getContent = $this->_subject->getContent($name);
		$setContent = $this->_subject->setContent($name, 'html');
		$logourl = JURI::root(true) . '/libraries/h5pcore/media/h5p-logo.png';

		// Workaround for broken JCE setContent function. We include it here
		// since JCE seems to be quite popular. (Fixes potential other editors
		// with the same weird issue too.) (Replaces 'html' in setContent
		// function with just html)
		$setContent = str_replace("'html'", 'html', $setContent);

		$js = "
			function insertH5P(uid, title) {
				var placeholder = '<img class=\"h5p-placeholder-image\" data-content-id=\"'+uid+'\" title=\"'+title+'\" src=\"{$logourl}\" />';
				var re = new RegExp('\<img.*?h5p-placeholder-image.*?data-content-id\=\"' + uid + '\".*?\>', 'i');
				var current = h5pGetEditorContent();
				var res = re.exec(current);
				if (res) {
					current = current.replace(res, placeholder);
					h5pSetEditorContent(current);
				} else {
					jInsertEditorText(placeholder, '{$name}');
				}
				SqueezeBox.close();
				return false;
			}
			function h5pGetEditorContent() {
				return {$getContent};
			}
			function h5pSetEditorContent(html) {
				{$setContent}
			}
		";
		$doc->addScriptDeclaration($js);

		$link = 'index.php?option=com_h5p&amp;view=h5p&amp;layout=insert&amp;tmpl=component&amp;e_name='.$name;

		JHtml::_('behavior.modal');

		$button = new JObject;
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('PLG_EDITORSXTD_H5P_BUTTON_H5P'));
		$button->set('name', 'h5p blank');
		$button->set('options', "{handler: 'iframe', size: {x: 800, y: 700}, closable: function() {
			if (!H5PRefuseClose) {
				return true;
			}
			return confirm('By doing this you will lose any unsaved work');
		}, onOpen: function() {
			H5PRefuseClose = true;
		}}");

		return $button;
	}
}
