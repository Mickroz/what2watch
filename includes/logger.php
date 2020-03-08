<?php
/**
* Simple logger class based on a similer class created by 
* Darko Bunic (http://www.redips.net/php/write-to-log-file/)	 
* Does simple logging to a specified file. See https://bitbucket.org/huntlyc/simple-php-logger for more details.
*
* @package What2Watch
* @author Huntly Cameron <huntly.cameron@gmail.com>
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
* Logger class
*/
class PHPLogger
{
	/**
	 * log_file - the log file to write to
	 *  
	 * @var string
	 **/	
	private $log_file;
	private $threshold = 1024;
	private $first_run = true;
	
	/**
	 * Constructor
	 * @param String logfile - [optional] Absolute file name/path. Defaults to ubuntu apache log.
	 * @return void
	 **/	
	function __construct($log_file = "what2watch.log")
	{
		global $log_filesize;
		$this->log_file = $_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/' . $log_file;
		$this->threshold = (int) $log_filesize;

		if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/'))
		{
			mkdir($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/');
		}
		
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/' . $log_file))
		{
			//Attempt to create log file		
			touch($_SERVER['DOCUMENT_ROOT'] . '/what2watch/logs/' . $log_file);
		}

		//Make sure we'ge got permissions
		if (!(is_writable($log_file) || $this->win_is_writable($log_file)))
		{   
			//Cant write to file,
			throw new Exception("LOGGER ERROR: Can't write to log", 1);
		}
	}
	
	/** 
	* runned the first time 
	*/ 
	private function firstRun() 
	{ 
		$this->rotate_log(); 
		$this->flog = fopen($this->log_file,'a+'); 
		$this->first_run = false; 
	} 
	
	/**
	 * d - Log Debug
	 * @param String tag - Log Tag
	 * @param String message - message to spit out
	 * @return void
	 **/	
	public function debug($tag, $message)
	{
		global $debug;
		
		if ($debug)
		{
			$this->writeToLog("DEBUG", $tag, $message);
		}
	}

	/**
	 * e - Log Error
	 * @param String tag - Log Tag
	 * @param String message - message to spit out
	 * @author 
	 **/	
	public function error($tag, $message)
	{
		$this->writeToLog("ERROR", $tag, $message);		
	}

	/**
	 * w - Log Warning
	 * @param String tag - Log Tag
	 * @param String message - message to spit out
	 * @author 
	 **/	
	public function warning($tag, $message)
	{
		$this->writeToLog("WARNING", $tag, $message);		
	}

	/**
	 * i - Log Info
	 * @param String tag - Log Tag
	 * @param String message - message to spit out
	 * @return void
	 **/	
	public function info($tag, $message)
	{
		$this->writeToLog("INFO", $tag, $message);		
	}

	/** 
	* rotate log $filename adding .x to end of filename 
	* use this to force rotate 
	*/ 
	public function rotate_log() 
	{
		$threshold_bytes = $this->threshold* 1024; 
		$filename = $this->log_file; 
		if( file_exists($this->log_file) && filesize($filename) >= $threshold_bytes ) 
		{ 
			// rotate 
			$path_info = pathinfo($filename); 
			$base_directory = $path_info['dirname']; 
			$base_name = $path_info['basename']; 
			$num_map = array(); 
			foreach( new DirectoryIterator($base_directory) as $fInfo)
			{ 
				if($fInfo->isDot() || ! $fInfo->isFile()) continue; 
				if (preg_match('/^'.$base_name.'\.?([0-9]*)$/',$fInfo->getFilename(), $matches) )
				{ 
					$num = $matches[1]; 
					$file2move = $fInfo->getFilename(); 
					if ($num == '') $num = -1; 
					$num_map[$num] = $file2move; 
				} 
			} 
			krsort($num_map); 
			foreach($num_map as $num => $file2move)
			{ 
				$targetN = $num+1; 
				rename($base_directory.DIRECTORY_SEPARATOR.$file2move,$filename.'.'.$targetN); 
			} 
		} 
	} 
	/**
	 * writeToLog - writes out timestamped message to the log file as 
	 * defined by the $log_file class variable.
	 *
	 * @param String status - "INFO"/"DEBUG"/"ERROR" e.t.c.
	 * @param String tag - "Small tag to help find log entries"
	 * @param String message - The message you want to output.
	 * @return void
	 **/	
	private function writeToLog($status, $tag, $message)
	{
		if($this->first_run) $this->firstRun(); 
		$date = date('Y-m-d H:i:s');
		$msg = "$date $status	$tag: $message" . PHP_EOL;
		file_put_contents($this->log_file, $msg, FILE_APPEND);
	}

	//Function lifted from wordpress
	//see: http://core.trac.wordpress.org/browser/tags/3.3/wp-admin/includes/misc.php#L537
	private function win_is_writable( $path )
	{
		/* will work in despite of Windows ACLs bug
		 * NOTE: use a trailing slash for folders!!!
		 * see http://bugs.php.net/bug.php?id=27609
		 * see http://bugs.php.net/bug.php?id=30931
		 */
		if ($path[strlen($path) - 1] == '/') // recursively return a temporary file path
		{
			return win_is_writable($path . uniqid( mt_rand()) . '.tmp');
		}
		else if (is_dir($path))
		{
			return win_is_writable($path . '/' . uniqid(mt_rand()) . '.tmp');
		}
		// check tmp file for read/write capabilities
		$should_delete_tmp_file = !file_exists($path);
		$f = @fopen($path, 'a');
		if ($f === false)
		{
			return false;
		}
		fclose($f);

		if ($should_delete_tmp_file)
		{
			unlink($path);
		}
		return true;
	}	
}
?>