<?php


require_once(__DIR__ .'/../SiteConfig.class.php');
$dir = __DIR__ .'/../..';
if(file_exists(__DIR__ .'/../vendor/core/FileSystem.class.php')) {
	$dir = __DIR__ .'/../vendor';
}
elseif(file_exists(__DIR__ .'/../../vendor/core/FileSystem.class.php')) {
	$dir = __DIR__ .'/../../vendor';
}
require_once($dir .'/core/base.abstract.php');
require_once($dir .'/core/Version.class.php');
require_once($dir .'/core/ToolBox.class.php');
require_once($dir .'/core/FileSystem.class.php');