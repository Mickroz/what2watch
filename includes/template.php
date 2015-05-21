<?php
/**
* Simple template engine class (use [@tag] tags in your templates).
*
* @package What2Watch
* @author Nuno Freitas <nunofreitas@gmail.com>
* @author Mickroz
* @version 1.0
* @link http://www.broculos.net/ Broculos.net Programming Tutorials
* @link https://www.github.com/Mickroz/what2watch
* @copyright (c) 2015 Mickroz
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_W2W'))
{
	exit;
}
/**
* template class
*/
class template
{
	/**
	* The filename of the template to load.
	*
	* @access protected
	* @var string
	*/
	var $root = '';
	var $file = '';
	var $values = array();

	/**
	* Set template location
	* @access public
	*/
	function set_template()
	{
		global $template_path;

		if (file_exists('styles/' . $template_path))
		{
			$this->root = 'styles/' . $template_path;
		}
		else
		{
			trigger_error('Template path could not be found: styles/' . $template_path, E_USER_ERROR);
		}

		return true;
	}
	
	/**
	* Sets the template filename
	* @access public
	*/
	function set_filename($file)
	{
		$this->file =  $this->root .  '/' . $file;
	}
	
	/**
	* Sets a value for replacing a specific tag.
	*
	* @param string $key the name of the tag to replace
	* @param string $value the value to replace
	*/
	function assign_var($key, $value)
	{
	    $this->values[$key] = $value;
	}
	
	function assign_vars($vararray)
	{
		foreach ($vararray as $key => $val)
		{
			$this->values[$key] = $val;
		}

		return true;
	}
	/**
	* Outputs the content of the template, replacing the keys for its respective values.
	*
	* @return string
	*/
	function output()
	{
		global $lang;
		/**
		* Tries to verify if the file exists.
		* If it doesn't return with an error message.
		* Anything else loads the file contents and loops through the array replacing every key for its value.
		*/
		if (!file_exists($this->file))
		{
			return "Error loading template file ($this->file).<br />";
		}
		$output = file_get_contents($this->file);
	    
		foreach ($this->values as $key => $value)
		{
			$key = strtoupper($key);
			$tagToReplace = '{' . $key . '}';
			$output = str_replace($tagToReplace, $value, $output);
		}
		// transform vars prefixed by L_ into their language variable pendant if nothing is set
		if (strpos($output, '{L_') !== false)
		{
			$output = preg_replace_callback('#\{L_([A-Z0-9\-_]+)\}#', 
			function ($m)
			{
				global $lang;
				$match = $m[1];
				return (isset($lang[$match]) ? $lang[$match] : '{ ' . $match . ' }');
			},
			$output);
		}
		preg_match_all('#<!-- INCLUDE (\{\$?[A-Z0-9\-_]+\}|[a-zA-Z0-9\_\-\+\./]+) -->#', $output, $matches);
		foreach ($matches[1] as $include)
		{
			$output = preg_replace('#<!-- INCLUDE ' . $include . ' -->#', file_get_contents($this->root .  '/' . $include), $output);
		}

		return $output;
	}
	
	/**
	* Merges the content from an array of templates and separates it with $separator.
	*
	* @param array $templates an array of template objects to merge
	* @param string $separator the string that is used between each template object
	* @return string
	*/
	static public function merge($templates, $separator = "\n")
	{
		/**
		* Loops through the array concatenating the outputs from each template, separating with $separator.
		* If a type different from Template is found we provide an error message. 
		*/
		$output = "";
	    
		foreach ($templates as $template)
		{
			$content = (get_class($template) !== "template")
			? "Error, incorrect type - expected template."
			: $template->output();
			$output .= $content . $separator;
		}
	    
		return $output;
	}
}