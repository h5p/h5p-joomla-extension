<?php
/**
 * @package		H5P
 * @subpackage	com_h5p
 * @copyright	Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');
jimport('h5pcore.h5pjoomla');

/**
 * H5P Component Controller
 */
class H5PController extends JController
{
	/**
	 * Receives H5P upload requests from views/h5p/tmpl/upload.php
	 */
	public function upload()
	{
		$destination_folder = JFactory::getConfig()->get('tmp_path') . DIRECTORY_SEPARATOR . uniqid('h5ptmp-');
		$destination_path = $destination_folder . DIRECTORY_SEPARATOR . $_FILES['h5p']['name'];
		$tmp_name = $_FILES['h5p']['tmp_name'];
		if (!$tmp_name || !JFile::upload($tmp_name, $destination_path, FALSE))
		{
			H5PJoomla::setErrorMessage('Not able to upload the given file.');
			print "
				<p>Unable to upload file. Is the file larger than the allowed file transfer size of the server?</p>
				<p><a href=\"javascript:window.history.back();\">Try again.</a></p>
			";
			return;
		}
		$_SESSION['h5p_upload'] = $destination_path;
		$_SESSION['h5p_upload_folder'] = $destination_folder;

		$h5p_validator = H5PJoomla::getInstance('validator');
		$valid = $h5p_validator->isValidPackage();
		if (!$valid)
		{
			H5PJoomla::setErrorMessage('The uploaded file was not a valid h5p package');
			print "
				<p>Uploaded file did not validate as a proper H5P. See errors above for further explanation.</p>
				<p><a href=\"javascript:window.history.back();\">Try again.</a></p>
			";
			return;
		}

		$h5p_storage = H5PJoomla::getInstance('storage');
		$uid = uniqid('h5p-', true); // uniqid is not quite UUID, but good enough here.
		$library_updated = $h5p_storage->savePackage($uid);
		$title = isset($_REQUEST['new-h5p-title']) ? $_REQUEST['new-h5p-title'] : "Untitled H5P upload";

		// Response HTML
		$document = JFactory::getDocument();
		$document->setMimeEncoding('text/html');
		print "
			<script type=\"text/javascript\">
				window.parent.insertH5P('{$uid}', '{$title}');
			</script>
		";
	}

	public function libraries()
	{
		$input = JFactory::getApplication()->input;
		$machine_name = $input->getVar('machine_name');

		if (empty($machine_name))
		{
			$storage = H5PJoomla::getInstance('editorstorage');
			$document = JFactory::getDocument();
			$document->setMimeEncoding('application/json');
			$libs = $storage->getLibraries();
			print $libs;
		}
		else
		{
			$major_version = $input->getVar('major_version');
			$minor_version = $input->getVar('minor_version');
			$storage = H5PJoomla::getInstance('editorstorage');
			$h5p_joomla = H5PJoomla::getInstance('interface');

			// FIXME: H5peditor should use main folder from Interface, but for
			// now, H5peditor uses its own directories.
			$h5p_editor = new H5peditor($storage, JPATH_ROOT . DIRECTORY_SEPARATOR . 'media', JURI::root(false));
			$document = JFactory::getDocument();
			$document->setMimeEncoding('application/json');

			// Joomla System plugin SEF occasionally screw up the output from
			// here.  Turn it off.  This is not permanent and will only affect
			// the output from this particular function.
			$config = JFactory::getConfig();
			$config->set('sef', '0');
			$libdata = $h5p_editor->getLibraryData($machine_name, $major_version, $minor_version);
			print $libdata;
		}
	}

	public function files()
	{
		// $files_directory = file_directory_path();
		$files_directory = JPATH_ROOT . DIRECTORY_SEPARATOR . 'media';

		if (isset($_POST['contentId']) && $_POST['contentId'])
		{
			$files_directory .= DIRECTORY_SEPARATOR . 'h5p/content/' . $_POST['contentId'];
		}
		else
		{
			$files_directory .= DIRECTORY_SEPARATOR . 'h5peditor';
		}

		$file = new H5peditorFile($files_directory);

		if (!$file->isLoaded())
		{
			return;
		}

		if ($file->validate() && $file->copy())
		{
			$storage = H5PJoomla::getInstance('editorstorage');
			$storage->addTempFile($file);
		}

		print $file->getResult();
	}

	public function save()
	{
		$input = JFactory::getApplication()->input;
		$contentId = $input->getVar('cid');

		// Cannot use input->get() here. It eats all HTML, with no option to
		// tell it not to...  H5P does its own XSS filtering of HTML fields,
		// but it does so when returning it from the database.
		$h5p_params = $_POST['edit-h5p-params'];
		$library = $input->get('edit-h5p-library', '', 'string');

		$h5p_core = H5PJoomla::getInstance('core');
		$h5p_joomla = H5PJoomla::getInstance('interface');
		$library_data = $h5p_core->libraryFromString($library);
		$libraryId = $h5p_joomla->getLibraryId($library_data['machineName'], $library_data['majorVersion'], $library_data['minorVersion']);

		$db = JFactory::getDbo();
		$jsonQuoted = $db->quote($h5p_params);
		$title = $input->get('h5p-title', 'My H5P', 'string');
		if (!$title) {
			$title = 'Untitled ' . $library_data['machineName'];
		}
		$titleQuoted = $db->quote($title);
		$db->setQuery(sprintf(
			"INSERT INTO #__h5p (h5p_id, title, json_content, embed_type, main_library_id)
			  VALUES (%s, %s, %s, 'div, iframe', %d)
			  ON DUPLICATE KEY UPDATE title=%s, json_content=%s, main_library_id=%d",
			$db->quote($contentId), $titleQuoted, $jsonQuoted, $libraryId,
			$titleQuoted, $jsonQuoted, $libraryId));
		$db->query();

		$storage = H5PJoomla::getInstance('editorstorage');
		$h5pStorage = H5PJoomla::getInstance('storage');
		$editor = new H5peditor($storage, JPATH_ROOT . DIRECTORY_SEPARATOR . 'media', JURI::root(true), $h5pStorage);

		if (!$editor->createDirectories($contentId))
		{
			print "<p>Unable to create content directories on the server. Please contact the system administrator.</p>";
			return;
		}

		// Move files.
		$editor->processParameters(
			$contentId,
			$library_data,
			json_decode($h5p_params),
			NULL, //isset($node->h5p_library_old) ? $node->h5p_library_old : NULL,
			NULL //isset($node->h5p_params_old) ? json_decode($node->h5p_params_old) : NULL
		);

		// Response HTML
		header('Content-Type: text/html');
		print "
			<script type=\"text/javascript\">
				window.parent.insertH5P('{$contentId}', '{$title}');
			</script>
		";
	}

  public function setFinished() {
    $document = JFactory::getDocument();
    $document->setMimeEncoding('application/json');
    $result = array('success' => FALSE);

    // Get user
    $user = JFactory::getUser();
    $userId = $user->get('id');

    // Get input
    $jinput = JFactory::getApplication()->input;
    $datas = $jinput->getArray(array(
      'contentId' => 'string',
      'points' => 'int',
      'maxPoints' => 'int'
    ));

    if ($userId !== 0 && $datas['contentId'] !== NULL && $datas['points'] !== NULL && $datas['maxPoints'] !== NULL) {
      $db = JFactory::getDbo();
  		$db->setQuery(sprintf(
    		"UPDATE #__h5p_status SET finished = %d, score = %d, max_score = %d
          WHERE h5p_id = '%s' AND user_id = %d",
        time(), $datas['points'], $datas['maxPoints'], $datas['contentId'], $userId));

      if ($db->query()) {
        $result['success'] = TRUE;
        $result['query'] = sprintf(
    		"UPDATE #__h5p_status SET finished = %d, score = %d, max_score = %d
          WHERE h5p_id = '%s' AND user_id = %d",
        time(), $datas['points'], $datas['maxPoints'], $datas['contentId'], $userId);
      }
    }

    echo json_encode($result);
  }
}
