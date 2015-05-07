<?php

/*
 * A class for handling configuration of database-driven web applications.
 * 
 * NOTICE::: this class requires that cs-phpxml and cs-arraytopath are both available
 * at the same directory level as cs-content; all projects are SourceForge.net projects,
 * using their unix names ("cs-phpxml" and "cs-arrayToPath").  The cs-phpxml project 
 * requires cs-arrayToPath for parsing XML paths.
 * 
 */

use crazedsanity\ToolBox;
use crazedsanity\Version;
use crazedsanity\FileSystem;

class SiteConfig  {
	
	private $configDirname;
	private $configFile;
	private $activeSection;
	private $fullConfig=array();
	private $configSections=array();
	private $isInitialized=false;
	
	//-------------------------------------------------------------------------
	/**
	 * Constructor.
	 * 
	 * @param $configFileLocation	(str) URI for config file.
	 * @param $section				(str,optional) set active section (default=MAIN)
	 * @param $setVarPrefix			(str,optional) prefix to add to all global & constant names.
	 * 
	 * @return NULL					(PASS) object successfully created
	 * @return exception			(FAIL) failed to create object (see exception message)
	 */
	public function __construct($configFileLocation, $section=null) {
		if(strlen($configFileLocation) && file_exists($configFileLocation)) {
			
			$this->configDirname = dirname($configFileLocation);
			$this->configFile = $configFileLocation;
		}
		else {
			throw new exception(__METHOD__ .": invalid configuration file (". $configFileLocation .")");
		}
		
		$ini = parse_ini_file($configFileLocation, true);
//		ToolBox::debug_print($ini,1);
//		exit;
		
		$this->parse_config();
		if(strlen($section)) {
			$this->set_active_section($section);
			$this->config = $this->get_section($section);
		}
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/** 
	 * Sets the active section.
	 * 
	 * @param $section		(str) section to be set as active.
	 * 
	 * @return VOID			(PASS) section was set successfully.
	 * @return exception	(FAIL) problem encountred setting section. 
	 */
	public function set_active_section($section) {
		if($this->isInitialized === true) {
			if(in_array($section, $this->get_valid_sections())) {
				$this->activeSection = $section;
			}
			else {
				throw new exception(__METHOD__ .": invalid section (". $section .")");
			}
		}
		else {
			throw new exception(__METHOD__ .": not initialized");
		}
	}//end set_active_section($section)
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function parse_value($value, array $replacements = null) {
		//remove double-slashes (//)
		$value = preg_replace('/[\/]{2,}/', '\/', $value);

		//remove leading slash for string replaces (i.e. "{/MAIN/SITE_ROOT}" becomes "{MAIN/SITE_ROOT}")
		$value = preg_replace('/{\//', '{', $value);

		//replace special vars.
		$value = ToolBox::mini_parser($value, $replacements, '{', '}');
		
		if(strlen($value)) {
			$value = ToolBox::resolve_path_with_dots($value);
		}
		
		return($value);
	}//end parse_value()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Parse the configuration file.  Handles replacing {VARIABLES} in values, 
	 * sets items as global or as constants, and creates array indicating the 
	 * available sections from the config file.
	 * 
	 * @param VOID			(void) no arguments accepted.
	 * 
	 * @return NULL			(PASS) successfully parsed configuration
	 * @return exception	(FAIL) exception indicates problem encountered.
	 */
	private function parse_config() {
		$specialVars = $this->build_special_vars();
		$alsoParse = array();
//		$this->fullConfig = parse_ini_file($this->configFile,true);
		
		$this->fullConfig = array();
		$config = parse_ini_file($this->configFile, true);
		$replacements = array();
		
		if(is_array($config) && count($config) > 0) {
			foreach($config as $sName => $sData) {
				foreach($sData as $k=>$v) {
					$localConfigSection = array();
					if(isset($this->fullConfig[$sName])) {
						$localConfigSection = $this->fullConfig[$sName];
					}
					$replacements = array_merge($alsoParse, $specialVars, $localConfigSection, $replacements);

					$parsedValue = $this->parse_value($v, $replacements, true);
					$this->fullConfig[$sName][$k] = $parsedValue;
					
					$alsoParse[strtoupper($sName) .'/'. strtoupper($k)] = $parsedValue;
					
					//TODO: implement option to set a section/value as a CONSTANT
					$constantName = $k;
					define(strtoupper($constantName), $parsedValue);
					
					$constantPlusSection = $sName .'-'. $constantName;
					define(strtoupper($constantPlusSection), $parsedValue);
					
					//TODO: implement option to set a section/value as a GLOBAL
					$GLOBALS[$k] = $parsedValue;
					
					$alsoParse = array_merge($alsoParse, $this->fullConfig[$sName]);
				}
			}
		}
		else {
			throw new LogicException(__METHOD__ .": no configuration to parse (". $this->configFile .")");
		}
		
		$this->isInitialized = true;
		return $this->fullConfig;
	}//end parse_config()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Retrieve all data about the given section.
	 * 
	 * @param $section		(str) section to retrieve.
	 * 
	 * @return array		(PASS) array contains section data.
	 * @return exception	(FAIL) exception indicates problem.
	 */
	public function get_section($section) {
		if($this->isInitialized === true) {
			$retval = $this->fullConfig[$section];
		}
		else {
			throw new exception(__METHOD__ .": not initialized");
		}
		
		return($retval);
	}//end get_section()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Retrieves list of valid configuration sections, as defined by 
	 * parse_config().
	 * 
	 * @param VOID			(void) no parameters accepted.
	 * 
	 * @return array		(PASS) array holds list of valid sections.
	 * @return exception	(FAIL) exception gives error.
	 */
	public function get_valid_sections() {
		if($this->isInitialized === true) {
			if(is_array($this->fullConfig) && count($this->fullConfig)) {
				$retval = array_keys($this->fullConfig);
			}
			else {
				throw new exception(__METHOD__ .": no sections defined, probably invalid configuration");
			}
		}
		else {
			throw new exception(__METHOD__ .": not initialized");
		}
		
		return($retval);
	}//end get_valid_sections()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function build_special_vars() {
		//determine the current "APPURL" (current URL minus hostname and current filename)
		{
			$appUrl = $_SERVER['SCRIPT_NAME'];
			$bits = explode('/', $appUrl);
			if(!strlen($bits[0])) {
				array_shift($bits);
			}
			if(count($bits)) {
				array_pop($bits);
			}
			if(!count($bits)) {
				$appUrl = '/';
			}
			else {
				$appUrl = '/'. ToolBox::string_from_array($bits, null, '/');
			}
		}
		
		$specialVars = array(
			'_DIRNAMEOFFILE_'	=> $this->configDirname,
			'_CONFIGFILE_'		=> $this->configFile,
			'_THISFILE_'		=> $this->configFile,
			'_APPURL_'			=> $appUrl
		);
		return($specialVars);	
	}//end build_special_vars()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_fullConfig() {
		return $this->fullConfig;
	}//end get_fullConfig()
	//-------------------------------------------------------------------------
	
}//end SiteConfig

