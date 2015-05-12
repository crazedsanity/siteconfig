<?php


use crazedsanity\core\ToolBox;
use crazedsanity\core\FileSystem;
use crazedsanity\SiteConfig\SiteConfig;

class SiteConfigTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * To ensure SimpleXML-based queries work (for removing dependencies on 
	 * PHPXML)...
	 */
	public function testSimpleXML() {
		$myFile = dirname(__FILE__) .'/files/siteConfig.xml';
		$this->assertFileExists($myFile);
		
		$x = new SimpleXMLElement(file_get_contents($myFile));
		
		$siteRoot = $x->website->SITE_ROOT;
		$this->assertTrue(is_object($siteRoot));
		$this->assertEquals('{_DIRNAMEOFFILE_}/..', "$siteRoot");//basically, the object is cast into a string which is the value of the tag.
	}
	
	
	public function testConfig() {
		$this->assertFalse(defined('SITE_ROOT'));
		
		$configFile = dirname(__FILE__) .'/files/siteConfig.ini';
		$this->assertTrue(file_exists($configFile));
		$x = new SiteConfig($configFile, null);
		
		$this->assertTrue(is_object($x));
//		$this->assertTrue(is_array($x->config));
		
		
		$this->assertTrue(is_array($GLOBALS));
		
		$myFs = new FileSystem(dirname(__FILE__));
		
		// so... set things as constants and GLOBALS
		foreach($x->get_valid_sections() as $sectionName) {
			$x->make_section_constants($sectionName);
			$x->make_section_globals($sectionName);
		}
		
		$this->assertEquals(ToolBox::resolve_path_with_dots(dirname($configFile) .'/..'), $GLOBALS['SITE_ROOT']);
		$this->assertEquals($GLOBALS['SITE_ROOT'], $GLOBALS['SITEROOT']);
		
		//Test to make sure the constant and the global are identical.
		$this->assertEquals(constant('SITE_ROOT'), $GLOBALS['SITE_ROOT']);
		
		$this->assertEquals('CS_SESSID', constant('SESSION_NAME'));
		$this->assertTrue(isset($GLOBALS['SESSION_NAME']));
		$this->assertFalse(isset($GLOBALS['API_AUTHTOKEN']));
		
		
		
		$testProjectSection = $x->get_section('cs-project');
		$this->assertEquals(1, count($testProjectSection));
		
		$a = $x->get_fullConfig();
		$this->assertEquals($a['cs-project']['api_authtoken'], $a['test']['TOKEN']);
		$this->assertEquals($a['cs-project']['api_authtoken'], $a['test']['TOKEN2']);
		
		$keys = array_keys($a);
		$this->assertEquals($x->get_valid_sections(), $keys);
		
		foreach($keys as $name) {
			$this->assertEquals($a[$name], $x->get_section($name));
		}
	}
	
	
	public function testCompareXmlToIni() {
		$xml = new siteConfig(__DIR__ .'/files/siteConfig.xml');
		$ini = new siteConfig(__DIR__ .'/files/siteConfig.ini');
		
		$this->assertEquals($xml->get_fullConfig(), $ini->get_fullConfig());
	}

}
