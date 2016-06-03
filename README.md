**This extension is outdated. It only works with old alpha/beta versions of the H5P content types**

This is the Joomla H5P integration package.  It consists of 3 parts and the
containing extension package to wrap them.


Installation
============

Each extension may be installed separatly, or all at once using the
containment package.

To install a single extension, package a zip file of the extension folder and
upload it in the Joomla extension manager.

To install all extensions at once, package each in a zip file, place these in
the packages folder and create a new zip file of the entire package.  Or use
the makepackage.sh script.

1. Install in the extension manager: /administrator/index.php?option=com_installer
2. On first install it is necessary to enable the two plugins manually.
   1. Go to /administrator/index.php?option=com_installer&view=manage
   2. Search up the extensions by searching for h5p.
   3. Enable the H5P Content and H5P Button plugins.

Extensions
==========

There are 4 extensions, and an extension container package.

Core library
------------
Folder: lib_h5pcore

This is the H5P Core library and base Joomla integration.  Contains the core
and editor files from h5p.org.


Content plugin
--------------
Folder: plg_content_h5p

This manages the H5P view.  It is responsible for adding necessary javascript
on content display so the H5P is properly shown.  It replaces the placeholder
image added when inserting an H5P with the proper container div.

Editor plugin
-------------
Folder: plg_editors_xtd_h5p

Provides an editor button to insert an H5P into an article.  Injects an H5P
placeholder image directly into the article DOM.  Button displays
editor/upload form for creating/editing/uploading H5P content.

H5P component
-------------
Folder: com_h5p

The main H5P component.  This handles creating/updating of database tables.
It also provides the views used by the editor plugin.  It might also contain
base H5P management logic, such as listing installed libraries, content
listing etc.

H5P package
-----------

Package data for distribution. (Basically, this is just the XML file for
describing the 3 extensions above.)

To create the package data, zip the plugins and component into separate files,
and place them in the "packages" folder, then zip the entire package. The
result should be a zip file with the following structure:

    pck_h5p.zip
     ├── packages
     │   ├── com_h5p.zip
     │   ├── lib_h5pcore.zip
     │   ├── plg_content_h5p.zip
     │   └── plg_editors_xtd_h5p.zip
     └── pkg_h5p.xml

There is a helper script in the root folder named makepackage.sh to quickly
create a complete package.
