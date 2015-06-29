<?php
/**
 * @package   H5P
 * @subpackage  plg_content_h5p
 * @copyright Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.utilities.date');
jimport('h5pcore.h5pjoomla');

/**
 * @package   Joomla.Plugin
 * @subpackage  User.profile
 * @version   1.6
 */
class plgContentH5P extends JPlugin
{
	public $h5p_re = '/\<img.*?h5p-placeholder-image.*?data-content-id\=\"(.*?)\".*?\>/';
	private $fileSuffix = '?v=0.3.4';
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       2.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * onContentPrepare
	 *
	 * Replaces IMG H5P placeholder with an iFrame.
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		$matches = array();
		if (preg_match_all($this->h5p_re, $article->text, $matches, PREG_SET_ORDER))
		{
			// We need to add a little script
			$doc = JFactory::getDocument();
			$doc->addScript(JURI::root(true) . '/libraries/h5pcore/core/js/jquery.js' . $this->fileSuffix);
			$doc->addScript(JURI::root(true) . '/libraries/h5pcore/core/js/h5p.js' . $this->fileSuffix);
			$doc->addScript(JURI::root(true) . '/libraries/h5pcore/js/h5piframeresize.js' . $this->fileSuffix);
			$doc->addStyleSheet(JURI::root(true) . '/libraries/h5pcore/core/styles/h5p.css' . $this->fileSuffix);
			$doc->addStyleSheet(JURI::root(true) . '/libraries/h5pcore/media/h5pdisplay.css' . $this->fileSuffix);

			$html = '<div class="h5p-iframe-wrapper" id="iframe-wrapper-##h5pId##">';
			$html .= '<iframe id="iframe-##h5pId##" class="h5p-iframe" src="##iframeSrc##" style="width: 100%; height: 400px; border: none;"></iframe>';
			$html .= '</div>';

			$article_url = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid, $article->itemid)) ."-$article->alias";
			foreach ($matches as $key => $match) {
				$h5pId = $match[1];
				$h5pDomId = str_replace('.', '_', $h5pId); // Periods make jQuery unhappy.

				if ($context === 'com_content.article') {
					$iframeSrc = 'index.php?option=com_h5p&view=h5p&layout=view&tmpl=component&cid=' . $h5pId;
					$replacement = str_replace(array('##h5pId##', '##iframeSrc##'), array($h5pDomId, $iframeSrc), $html);

					$article->text = preg_replace(
						'/\<img.*?h5p-placeholder-image.*?data-content-id\=\"' . $h5pId . '\".*?\>/',
						$replacement,
						$article->text,
						1);
					$this->registerStartTime($h5pId);
				}
				elseif (isset($article->id)) {
					$article->text = preg_replace('/\<img.*?h5p-placeholder-image.*?data-content-id\=\"' . $h5pId . '\".*?\>/', '<a href="' . $article_url . '">$0</a>', $article->text, 1);
				}
				else {
					$article->text = preg_replace('/\<img.*?h5p-placeholder-image.*?data-content-id\=\"' . $h5pId . '\".*?\>/', '', $article->text, 1);
				}
			}
		}
	}

	/**
	 * Register that the user has started to watch an H5P
	 *
	 * @param string $h5pId
	 *  The id of the H5P
	 */
	public function registerStartTime($h5pId)
	{
		$user = JFactory::getUser();
		$userId = $user->get('id');
		if ($userId !== 0) {
			$db = JFactory::getDbo();
			$db->setQuery(sprintf(
				"INSERT IGNORE INTO #__h5p_status
				  (h5p_id, user_id, started) VALUES (%s, %d, %d)",
				$db->quote($h5pId), (int) $userId, time()));
			$db->query();
		}
	}

	/**
	 * onContentAfterSave
	 *
	 * Checks saved article for links to H5Ps, marks them in the h5p_content
	 * table of exists.
	 */
	public function onContentAfterSave($context, &$article, $isNew)
	{
		// Check if article has an H5P in it.
		$matches = array();
		$text = $article->introtext . " " . $article->fulltext;
		$db = JFactory::getDbo();

		// Delete existing H5P links for this content id.
		$db->setQuery("DELETE FROM #__h5p_content WHERE content_id = " . $article->id);
		$db->query();

		// Add new links, if any exists in the document.
		if (preg_match_all($this->h5p_re, $text, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $idx => $match)
			{
				$h5pId = $match[1];
				$db->setQuery(sprintf(
					"INSERT IGNORE INTO #__h5p_content
					  (h5p_id, content_id) VALUES (%s, %d)",
					$db->quote($h5pId), (int) $article->id));
				$db->query();
			}
		}
	}

	/**
	 * onContentAfterDelete
	 *
	 * Remove H5P link from h5p_content table if content is deleted.
	 */
	public function onContentAfterDelete($context, $table)
	{
		if ($context === 'com_content.article')
		{
			$db = JFactory::getDbo();
			$db->setQuery("DELETE FROM #__h5p_content WHERE content_id = " . $table->id);
			$db->query();
		}
	}
}
