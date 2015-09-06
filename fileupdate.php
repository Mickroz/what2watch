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
$file = 'cache/progress.txt';
$result = array();
clearstatcache(true, $file);

$data['time']    = filemtime($file);
$data['content'] = $_POST['time'] < $data['time']
    ? file_get_contents($file)
    : false;

echo json_encode($data);