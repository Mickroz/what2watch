<?php
/**
*
* @package What2Watch
* @author Mickroz
* @version Id$
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
* Class for grabbing/handling cached entries
*/
class cache
{
	// Path to cache folder (with trailing /)
	public $cache_path = 'cache/';
	// Length of time to cache a file (in seconds)
	public $cache_time = 3600;
	// Cache file extension
	public $cache_extension = '.cache';

	/**
	* Get saved cache object
	*/	
	public function get($label)
	{
		if ($this->is_cached($label))
		{
			$filename = $this->cache_path . 'data_' . $this->safe_filename($label) . $this->cache_extension;
			return file_get_contents($filename);
		}
		return false;
	}
	/**
	* Put data into cache
	*/	
	public function put($label, $data)
	{
		file_put_contents($this->cache_path . 'data_' . $this->safe_filename($label) . $this->cache_extension, $data);
	}
	/**
	* Check if a given cache entry exist
	*/
	public function is_cached($label)
	{
		$filename = $this->cache_path . 'data_' . $this->safe_filename($label) . $this->cache_extension;
		if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;
		return false;
	}
	
	/**
	* Purge cache data
	*/
	public function purge()
	{
		// Purge all cache files
		$dir = @opendir($this->cache_path);

		if (!$dir)
		{
			return;
		}

		while (($entry = readdir($dir)) !== false)
		{
			if (strpos($entry, 'data_') !== 0)
			{
				continue;
			}

			$this->remove_file($this->cache_path . $entry);
		}
		closedir($dir);
	}
	/**
	* Removes/unlinks file
	*/
	public function remove_file($filename, $check = false)
	{
		if ($check && !is_writable($this->cache_path))
		{
			// E_USER_ERROR - not using language entry - intended.
			trigger_error('Unable to remove files within ' . $this->cache_path . '. Please check directory permissions.', E_USER_ERROR);
		}

		return @unlink($filename);
	}
	//Helper function to validate filenames
	private function safe_filename($filename)
	{
		return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
	}
}