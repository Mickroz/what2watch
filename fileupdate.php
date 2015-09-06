<?php
$file = 'cache/progress.txt';
$result = array();
clearstatcache(true, $file);

$data['time']    = filemtime($file);
$data['content'] = $_POST['time'] < $data['time']
    ? file_get_contents($file)
    : false;

echo json_encode($data);