#!/bin/bash

mkdir -p package/packages
for i in 'lib_h5pcore' 'com_h5p' 'plg_content_h5p' 'plg_editors_xtd_h5p' 'plg_user_h5p'; do
  cd $i
  rm ../package/packages/$i.zip
  zip -r ../package/packages/$i.zip .
  cd ..
done

cd package
rm ../pkg_h5pjoomla.zip
zip -r ../pkg_h5pjoomla.zip .
cd ..

