<?php
/**
 * @package		H5P
 * @subpackage	lib_h5pcore
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once('core/h5p.classes.php');
require_once('editor/h5peditor.class.php');
require_once('editor/h5peditor-file.class.php');
require_once('editor/h5peditor-storage.interface.php');

class H5PJoomla implements H5PFrameworkInterface
{
	protected $libraryCache;

	/**
	 * Get an instance of one of the h5p library classes
	 *
	 * This function stores the h5p core in a static variable so that the variables there will
	 * be kept between validating and saving the node for instance
	 *
	 * @staticvar H5PJoomla $interface
	 *  The interface between the H5P library and Joomla
	 * @staticvar H5PCore $core
	 *  Core functions and storage in the h5p library
	 * @param string $type
	 *  Specifies the instance to be returned; validator, storage, interface or core
	 * @return object
	 *  The instance og h5p specified by type
	 */
	public static function getInstance($type) {
		static $interface, $core, $editorstorage;

		if (!isset($interface))
		{
			$interface = new H5PJoomla();
			$core = new H5PCore($interface);
		}

		switch ($type)
		{
			case 'validator':
				return new H5PValidator($interface, $core);
			case 'storage':
				return new H5PStorage($interface, $core);
			case 'contentvalidator':
				return new H5PContentValidator($interface, $core);
			case 'interface':
				return $interface;
			case 'core':
				return $core;
			case 'editorstorage':
				if (!$editorstorage)
				{
					$editorstorage = new H5PJoomlaEditorStorage();
				}
				return $editorstorage;
		}
	}

	private function _libraryByName($machineName, $majorVersion, $minorVersion)
	{
		// Make sure we got a libraryCache array.
		if (!is_array($this->libraryCache))
		{
			$this->libraryCache = array();
		}

		// Create key for libraryCache array
		$fullname = "{$machineName}-{$majorVersion}.{$minorVersion}";

		if (!isset($this->libraryCache[$fullname]))
		{
			// Get from database.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__h5p_libraries')
				->where(array('machine_name = ' . $db->quote($machineName),
											'major_version = ' . $majorVersion,
											'minor_version = ' . $minorVersion));
			$db->setQuery((string) $query);
			$lib = $db->loadObject();

			if (empty($lib))
			{
				return FALSE;
			}
			// Store in cache. Twice.
			$this->libraryCache[$fullname] = $lib;
			$this->libraryCache[$lib->library_id] = $lib;
		}

		return $this->libraryCache[$fullname];
	}

	private function _libraryById($libraryId)
	{
		// Make sure we got a libraryCache array.
		if (!is_array($this->libraryCache))
		{
			$this->libraryCache = array();
		}

		if (!isset($this->libraryCache[$libraryId]))
		{
			// Get from database.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__h5p_libraries')
				->where('library_id = ' . $libraryId);
			$db->setQuery((string) $query);
			$lib = $db->loadObject();

			// Store in cache. Twice.
			if (empty($lib))
			{
				return FALSE;
			}
			$fullname = "{$lib->machineName}-{$lib->majorVersion}.{$lib->minorVersion}";
			$this->libraryCache[$fullname] = $lib;
			$this->libraryCache[$lib->library_id] = $lib;
		}

		return $this->libraryCache[$libraryId];
	}

	public function setErrorMessage($message)
	{
		JFactory::getApplication()->enqueueMessage($message, 'error');
	}

	public function setInfoMessage($message)
	{
		JFactory::getApplication()->enqueueMessage($message, 'notice');
	}

	public function t($message, $replacements = array())
	{
		// return t($message, $replacements);
		$transd = JText::_($message);
		if (empty($replacements))
		{
			return $transd;
		}
		else
		{
			return strtr($transd, $replacements);
		}
	}

	/**
	 * Get the Path to the folder containing the unpacked copy of the
	 * last uploaded H5P
	 *
	 * @return string
	 *   Path to the folder where the last uploaded h5p for this session is located.
	 */
	public function getUploadedH5pFolderPath()
	{
		// This is set by the controller receiving the file.
		return $_SESSION['h5p_upload_folder'];
	}

	/**
	 * @return string Path to the folder where all h5p files are stored
	 */
	public function getH5pPath()
	{
		$filepath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'h5p';
		if (!is_dir($filepath))
		{
			@mkdir($filepath . DIRECTORY_SEPARATOR . 'libraries', 0777, true);
			@mkdir($filepath . DIRECTORY_SEPARATOR . 'content', 0777, true);
		}
		return $filepath;
	}

	/**
	 * Get the path to the last uploaded h5p file
	 *
	 * @return string Path to the last uploaded h5p
	 */
	public function getUploadedH5pPath()
	{
		// This is set by the controller receiving the file.
		return $_SESSION['h5p_upload'];
	}

	/**
	 * Get file extension whitelist
	 *
	 * The default extension list is part of h5p, but admins should be allowed to modify it
	 *
	 * @param boolean $isLibrary
	 * @param string $defaultContentWhitelist
	 * @param string $defaultLibraryWhitelist
	 */
	public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
	{
		// TODO: Make whitelist configurable in admin section!
		$whitelist = $defaultContentWhitelist;
		if ($isLibrary)
		{
			$whitelist .= ' ' . $defaultLibraryWhitelist;
		}
		return $whitelist;
	}

	/**
	 * Is the current user allowed to update libraries?
	 *
	 * @return boolean
	 *  TRUE if the user is allowed to update libraries
	 *  FALSE if the user is not allowed to update libraries
	 */
	public function mayUpdateLibraries()
	{
		// TODO: Define library update permission for Joomla
		return TRUE;
	}

	/**
	 * Get id to an excisting library
	 *
	 * @param string $machineName
	 *  The librarys machine name
	 * @param int $majorVersion
	 *  The librarys major version
	 * @param int $minorVersion
	 *  The librarys minor version
	 * @return int
	 *  The id of the specified library or FALSE
	 */
	public function getLibraryId($machineName, $majorVersion, $minorVersion)
	{
		$lib = $this->_libraryByName($machineName, $majorVersion, $minorVersion);
		if ($lib)
		{
			return $lib->library_id;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Is the library a patched version of an existing library?
	 *
	 * @param object $library
	 *  The library data for a library we are checking
	 * @return boolean
	 *  TRUE if the library is a patched version of an existing library
	 *  FALSE otherwise
	 */
	public function isPatchedLibrary($library)
	{
		$lib = $this->_libraryByName($library['machineName'], $library['majorVersion'], $library['minorVersion']);
		return ($lib && $lib->patch_version < $library['patchVersion']);
	}

	/**
	* Convert list of file paths to csv
	*
	* @param array $libraryData
	*  Library data as found in library.json files
	* @param string $key
	*  Key that should be found in $libraryData
	* @return string
	*  file paths separated by ', '
	*/
	private function pathsToCsv($libraryData, $key) {
		if (isset($libraryData[$key]))
		{
			$paths = array();
			foreach ($libraryData[$key] as $file)
			{
				$paths[] = $file['path'];
			}
			return implode(', ', $paths);
		}
		return '';
	}

	/**
	 * Store data about a library
	 *
	 * Also fills in the libraryId in the libraryData object if the object is new
	 *
	 * @param object $libraryData
	 *  Object holding the information that is to be stored
	 */
	public function saveLibraryData(&$libraryData, $new = TRUE)
	{
		$preloadedJs = $this->pathsToCsv($libraryData, 'preloadedJs');
		$preloadedCss =  $this->pathsToCsv($libraryData, 'preloadedCss');
		$dropLibraryCss = '';
		if (isset($libraryData['dropLibraryCss']))
		{
			$libs = array();
			foreach ($libraryData['dropLibraryCss'] as $lib)
			{
				$libs[] = $lib['machineName'];
			}
			$dropLibraryCss = implode(', ', $libs);
		}
		$embedTypes = '';
		if (isset($libraryData['embedTypes']))
		{
			$embedTypes = implode(', ', $libraryData['embedTypes']);
		}
		if (!isset($libraryData['semantics']))
		{
			$libraryData['semantics'] = '';
		}
		if (!isset($libraryData['fullscreen']))
		{
			$libraryData['fullscreen'] = 0;
		}

		$db_object = new stdClass;
		$db_object->title = $libraryData['title'];
		$db_object->patch_version = $libraryData['patchVersion'];
		$db_object->runnable = $libraryData['runnable'];
		$db_object->fullscreen = $libraryData['fullscreen'];
		$db_object->embed_types = $embedTypes;
		$db_object->preloaded_js = $preloadedJs;
		$db_object->preloaded_css = $preloadedCss;
		$db_object->drop_library_css = $dropLibraryCss;
		$db_object->semantics = $libraryData['semantics'];
		$db = JFactory::getDbo();
		if ($new)
		{
			$db_object->machine_name = $libraryData['machineName'];
			$db_object->major_version = $libraryData['majorVersion'];
			$db_object->minor_version = $libraryData['minorVersion'];
			$db_object->library_id = 0;

			$db->insertObject('#__h5p_libraries', $db_object, 'library_id');
			$libraryData['libraryId'] = $db_object->library_id;
		}
		else
		{
			$db_object->library_id = $libraryData['libraryId'];
			$db->updateObject('#__h5p_libraries', $db_object, 'library_id', TRUE);
			$this->deleteLibraryDependencies($libraryData['libraryId']);
		}
		// Update languages
		$q = $db->getQuery(true);
		$q->delete('#__h5p_library_languages')->where('library_id = ' . $libraryData['libraryId']);
		$db->setQuery($q);
		$db->query(); // Execute

		if (isset($libraryData['language']))
		{
			foreach ($libraryData['language'] as $languageCode => $languageJson)
			{
				$langobject = new stdClass;
				$langobject->library_id = $libraryData['libraryId'];
				$langobject->language_code = $languageCode;
				$langobject->language_json = $languageJson;
				$db->insertObject('#__h5p_library_languages', $langobject);
			}
		}
	}

	/**
	 * Save what libraries a library is dependending on
	 *
	 * @param int $libraryId
	 *  Library Id for the library we're saving dependencies for
	 * @param array $dependencies
	 *  List of dependencies in the format used in library.json
	 * @param string $dependency_type
	 *  What type of dependency this is, for instance it might be an editor dependency
	 */
	public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
	{
		$db = JFactory::getDbo();
		foreach ($dependencies as $dependency)
		{
			$db->setQuery(sprintf(
				"INSERT INTO #__h5p_library_subdependencies
					(library_id, required_library_id, dependency_type)
					SELECT %d, hl.library_id, %s
						FROM #__h5p_libraries hl
						WHERE machine_name = '%s'
							AND major_version = %d
							AND minor_version = %d",
				$libraryId, $db->quote($dependency_type),
				$dependency['machineName'], $dependency['majorVersion'], $dependency['minorVersion']
			));
			$db->query();
		}
	}

	/**
	 * Stores contentData
	 *
	 * @param int $contentId
	 *  Framework specific id identifying the content
	 * @param string $contentJson
	 *  The content data that is to be stored
	 * @param array $mainJsonData
	 *  The data extracted from the h5p.json file
	 * @param int $contentMainId
	 *  Any contentMainId defined by the framework, for instance to support revisioning
	 */
	public function saveContentData($contentId, $contentJson, $mainJsonData, $mainLibraryId, $contentMainId = NULL)
	{
		$embedTypes = '';
		if (isset($mainJsonData['embedTypes']))
		{
			$embedTypes = implode(', ', $mainJsonData['embedTypes']);
		}
		$db = JFactory::getDbo();
		$title = isset($mainJsonData['title']) ? $mainJsonData['title'] : 'Untitled H5P upload';
		$_REQUEST['new-h5p-title'] = $title;
		$db->setQuery(sprintf(
				"INSERT INTO #__h5p
				  (h5p_id, title, json_content, embed_type, main_library_id)
				VALUES (%s, %s, %s, %s, %d)",
				$db->quote($contentId), $db->quote($title), $db->quote($contentJson), $db->quote($embedTypes), $mainLibraryId
			));
		$res = $db->query();
		// @todo: Add support for allowing the user to select embed type
	}

	/**
	 * Deletes content data
	 *
	 * @param int $contentId
	 *  Framework specific id identifying the content
	 */
	public function deleteContentData($contentId)
	{
		$db = JFactory::getDbo();
		$db->setQuery(sprintf("DELETE FROM #__h5p WHERE h5p_id = %s", $db->quote($contentId)));
		$db->query();

		// Delete article links
		$db->setQuery("DELETE FROM #__h5p_content WHERE h5p_id = " . $db->quote($contentId));
		$db->query();
		// TODO: Delete from article content too?

    // Delete user stats
    $db->setQuery("DELETE FROM #__h5p_status WHERE h5p_id = " . $db->quote($contentId));
    $db->query();

		// Delete from library links
		$this->deleteLibraryUsage($contentId);
	}

	/**
	 * Copies library usage
	 *
	 * @param int $contentId
	 *  Framework specific id identifying the content
	 * @param int $copyFromId
	 *  Framework specific id identifying the content to be copied
	 * @param int $contentMainId
	 *  Framework specific main id for the content, typically used in frameworks
	 *  That supports versioning. (In this case the content id will typically be
	 *  the version id, and the contentMainId will be the frameworks content id
	 */
	public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL)
	{
		$db = JFactory::getDbo();
		$db->setQuery(sprintf(
			"INSERT INTO #__h5p_library_dependencies
				(h5p_id, library_id, preloaded, drop_css)
				SELECT %s, hnl.library_id, hnl.preloaded, hnl.drop_css
					FROM #__h5p_library_dependencies hnl
					WHERE hnl.h5p_id = %s",
			$db->quote($contentId), $db->quote($copyFromId)
		));
		$db->query();
	}

	/**
	 * Delete what libraries a content item is using
	 *
	 * @param int $contentId
	 *  Content Id of the content we'll be deleting library usage for
	 */
	public function deleteLibraryUsage($contentId)
	{
		$db = JFactory::getDbo();
		$db->setQuery(sprintf("DELETE FROM #__h5p_library_dependencies WHERE h5p_id = %s", $db->quote($contentId)));
		$db->query();
	}

	/**
	 * Saves what libraries the content uses
	 *
	 * @param int $contentId
	 *  Framework specific id identifying the content
	 * @param array $librariesInUse
	 *  List of libraries the content uses. Libraries consist of arrays with:
	 *   - libraryId stored in $librariesInUse[<place>]['library']['libraryId']
	 *   - libraryId stored in $librariesInUse[<place>]['preloaded']
	 */
	public function saveLibraryUsage($contentId, $librariesInUse)
	{
		$dropLibraryCssList = array();
		foreach ($librariesInUse as $machineName => $library)
		{
			if (!empty($library['library']['dropLibraryCss']))
			{
				$dropLibraryCssList = array_merge($dropLibraryCssList, explode(', ', $library['library']['dropLibraryCss']));
			}
		}
		$db = JFactory::getDbo();
		foreach ($librariesInUse as $machineName => $library)
		{
			$dropCss = in_array($machineName, $dropLibraryCssList) ? 1 : 0;
			$db->setQuery(sprintf(
				"INSERT INTO #__h5p_library_dependencies
					(h5p_id, library_id, preloaded, drop_css)
					VALUES (%s, %d, %d, %d)
					ON DUPLICATE KEY UPDATE preloaded = %d, drop_css = %d",
					$db->quote($contentId),
					$library['library']['libraryId'], $library['preloaded'], $dropCss, $library['preloaded'], $dropCss
				));
			$res = $db->query();
		}
	}

	/**
	 * Loads a library
	 *
	 * @param string $machineName
	 * @param int $majorVersion
	 * @param int $minorVersion
	 * @return array|FALSE
	 *  Array representing the library with dependency descriptions
	 *  FALSE if the library doesn't exist
	 */
	public function loadLibrary($machineName, $majorVersion, $minorVersion)
	{
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);
		$q->select(array(
				'library_id as libraryId',
				'machine_name as machineName',
				'title',
				'major_version as majorVersion',
				'minor_version as minorVersion',
				'patch_version as patchVersion',
				'embed_types as embedTypes',
				'preloaded_js as preloadedJs',
				'preloaded_css as preloadedCss',
				'drop_library_css as dropLibraryCss',
				'fullscreen',
				'runnable',
				'semantics'))
			->from('#__h5p_libraries')
			->where(array(
				'machine_name = ' . $db->quote($machineName),
				'major_version = ' . $majorVersion,
				'minor_version = ' . $minorVersion));
		$db->setQuery($q);
		$library = $db->loadAssoc();

		$db->setQuery(sprintf(
			"SELECT hl.machine_name as machineName,
				hl.major_version as majorVersion,
				hl.minor_version as minorVersion,
				hll.dependency_type as dependencyType
			FROM #__h5p_library_subdependencies hll
			JOIN #__h5p_libraries hl
				ON hll.required_library_id = hl.library_id
			WHERE hll.library_id = %d", $library['libraryId']
		));

		$res = $db->loadAssocList();
		foreach ($res as $dependency)
		{
			$library[$dependency['dependencyType'] . 'Dependencies'][] = array(
				'machineName' => $dependency['machineName'],
				'majorVersion' => $dependency['majorVersion'],
				'minorVersion' => $dependency['minorVersion'],
			);
		}
		return $library;
	}

	/**
	 * Loads and decodes library semantics.
	 *
	 * @param string $machineName
	 * @param int $majorVersion
	 * @param int $minorVersion
	 * @return array|FALSE
	 *  Array representing the library with dependency descriptions
	 *  FALSE if the library doesn't exist
	 */
	public function getLibrarySemantics($machineName, $majorVersion, $minorVersion)
	{
		$lib = $this->_libraryByName($machineName, $majorVersion, $minorVersion);
		$semantics = json_decode($lib->semantics);
		return $semantics;
	}

	/**
	 * Delete all dependencies belonging to given library
	 *
	 * @param int $libraryId
	 *  Library Id
	 */

	public function deleteLibraryDependencies($libraryId)
	{
		$db = JFactory::getDbo();
		$q = $db->getQuery(true);
		$q->delete('#__h5p_library_subdependencies')->where('library_id = ' . $libraryId);
		$db->setQuery($q);
		$db->query();
	}
}

class H5PJoomlaEditorStorage implements H5peditorStorage {
	public function getSemantics($machine_name, $major_version, $minor_version)
	{
		$interface = H5PJoomla::getInstance('interface');
		$semantics = $interface->getLibrarySemantics($machine_name, $major_version, $minor_version);
		return json_encode($semantics);
	}

	public function getLanguage($machineName, $majorVersion, $minorVersion) {
		// FIXME: Get current user language.
		$language = 'en';
		$db = JFactory::getDbo();
		$lang = $db->setQuery(sprintf(
			"SELECT language_json
				FROM #__h5p_library_languages hlt
				JOIN #__h5p_libraries hl
					ON hl.library_id = hlt.library_id
				WHERE hl.machine_name = %s
					AND hl.major_version = %d
					AND hl.minor_version = %d
					AND hlt.language_code = %s",
			$db->quote($machineName), (int) $majorVersion, (int) $minorVersion, $db->quote($language)));
		$lang = $db->loadAssoc();
		if (!empty($lang)) {
			return $lang['language_json'];
		}
		return false;
	}

	public function addTempFile($file)
	{

	}

	public function removeFile($path)
	{

	}

	public function keepFile($oldPath, $newPath)
	{

	}

	public function getLibraries() {
		$libraries = array();

		$db = JFactory::getDbo();
		if (isset($_POST['libraries'])) {
			// Get details for the specified libraries.
			foreach ($_POST['libraries'] as $libraryName) {
				$matches = array();
				preg_match_all('/(.+)\s(\d)+\.(\d)$/', $libraryName, $matches);
				if ($matches) {
					$db->setQuery(sprintf(
						"SELECT machine_name AS name, title, major_version as majorVersion, minor_version as minorVersion
							FROM #__h5p_libraries
							WHERE machine_name = %s
								AND major_version = %d
								AND minor_version = %d
								AND semantics IS NOT NULL",
						$db->quote($matches[1][0]), (int) $matches[2][0], (int) $matches[3][0]));
					$library = $db->loadObject();
					if ($library) {
						$library->uberName = $libraryName;
						$libraries[] = $library;
					}
				}
			}
		}
		else {
			// Get some books from the library.
			$db->setQuery(sprintf(
				"SELECT machine_name AS machineName, title, major_version as majorVersion, minor_version as minorVersion
					FROM #__h5p_libraries
					WHERE runnable = 1
						AND semantics IS NOT NULL"));
			$libraries = $db->loadObjectList();
		}

		return json_encode($libraries);
	}

	public function getEditorLibraries($machineName, $majorVersion, $minorVersion) {
		$editorLibraries = array();
		$jsonData = array('preloadedDependencies' => array());

		$db = JFactory::getDbo();
		// TODO: Add support for fetching additional libraries this library depends on
		$db->setQuery(sprintf(
			"SELECT hll.required_library_id AS libraryId, hl2.machine_name AS machineName, hl2.major_version AS majorVersion, hl2.minor_version AS minorVersion
				FROM #__h5p_libraries hl
				JOIN #__h5p_library_subdependencies hll
					ON hll.library_id = hl.library_id
				JOIN #__h5p_libraries hl2
					ON hl2.library_id = hll.required_library_id
			WHERE hl.machine_name = %s
				AND hl.major_version = %d
				AND hl.minor_version = %d
				AND hll.dependency_type = 'editor'",
			$db->quote($machineName), (int) $majorVersion, (int) $minorVersion));

		$results = $db->loadObjectList();
		foreach ($results as $editorLibrary) {
			$editorLibraries[$editorLibrary->libraryId] = $editorLibrary;

			$jsonData['preloadedDependencies'][$editorLibrary->libraryId] = array(
				'machineName' => $editorLibrary->machineName,
				'majorVersion' => $editorLibrary->majorVersion,
				'minorVersion' => $editorLibrary->minorVersion
			);
		}

		$libraries = array();
		// TODO: Fix, using private function since no API is available!
		$storage = H5PJoomla::getInstance('storage');
		$storage->getLibraryUsage($libraries, $jsonData);

		foreach ($libraries as $library) {
			if ($library['preloaded']) {
				$editorLibraries[$library['library']['libraryId']] = $library['library'];
			}
		}

		return $editorLibraries;
	}

	public function getFilePaths($libraryId) {
		$file_paths = array(
			'js' => array(),
			'css' => array(),
		);
		$h5p_core = H5PJoomla::getInstance('core');
		$h5p_joomla = H5PJoomla::getInstance('interface');
		$h5p_path = $h5p_joomla->getH5pPath(); // Ensure paths exists.
		$lib_path = 'media/h5p/libraries/';

		$db = JFactory::getDbo();
		$db->setQuery(sprintf(
			"SELECT hl.preloaded_css, hl.preloaded_js, hl.library_id, hl.machine_name as machineName, hl.major_version as majorVersion, hl.minor_version as minorVersion
			  FROM #__h5p_libraries hl
			WHERE hl.library_id = %d", (int) $libraryId));
		$results = $db->loadAssocList();

		foreach ($results as $paths) {
			if (!empty($paths['preloaded_js'])) {
				foreach (explode(',', $paths['preloaded_js']) as $js_path) {
					$file_paths['js'][] = $lib_path . $h5p_core->libraryToString($paths, TRUE) . '/' . trim($js_path);
				}
			}
			if (!empty($paths['preloaded_css'])) {
				foreach (explode(',', $paths['preloaded_css']) as $css_path) {
					$file_paths['css'][] = $lib_path . $h5p_core->libraryToString($paths, TRUE) . '/' . trim($css_path);
				}
			}
		}
		return $file_paths;
	}

}
