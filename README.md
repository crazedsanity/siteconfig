# siteconfig
XML/INI-based site configuration for PHP.

## How do I use it?
Create an XML or INI file for your site's configuration.  Values from one 
section can be automatically used in other sections using place holders in the 
form ```{SECTION/VALUE}```.

The configuration can be loaded as constants or into the GLOBALS array simply by 
calling a method, ```make_section_constants()``` and ```make_section_globals()```, 
respectively.
