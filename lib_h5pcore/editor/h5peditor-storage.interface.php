<?php
/**
 * @package    H5P
 * @subpackage Editor
 * @copyright  Copyright (C) 2013 Joubel AS. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//FIXME: Document this (What is provided in params (and how), and what is
//expected in return (how))
// FIXME2: Remove this! Make part of main H5P Interface
interface H5peditorStorage {
  public function getSemantics($machine_name, $major_version, $minor_version);
  public function getLanguage($machine_name, $major_version, $minor_version);
  public function getFilePaths($libraryId);
  public function addTempFile($file);
  public function removeFile($path);
  public function keepFile($oldPath, $newPath);
  public function getLibraries();
  public function getEditorLibraries($machine_name, $major_version, $minor_version);
}