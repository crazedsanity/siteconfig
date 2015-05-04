<?php

require_once(__DIR__ .'/../SiteConfig.class.php');

if(file_exists(__DIR__ .'/../../core/FileSystem.class.php')) {
echo "Found it!\n";
	require_once(__DIR__ .'/../../core/base.abstract.php');
	require_once(__DIR__ .'/../../core/Version.class.php');
	require_once(__DIR__ .'/../../core/ToolBox.class.php');
	require_once(__DIR__ .'/../../core/FileSystem.class.php');
}
else {
	echo "could not find Filesystem....\n";
}
