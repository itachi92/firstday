<?php
function get_dir_path($path) {
	$path = str_replace('\\', '/', $path);
	if(substr($path, -1) != '/') $path = $path.'/';
	return $path;
}

function create_dir($path, $mode = 0777) {
	if(is_dir($path)) return TRUE;
	$ftp_enable = 0;
	$path = get_dir_path($path);
	$temp = explode('/', $path);
	$cur_dir = '';
	$max = count($temp) - 1;
	for($i=0; $i<$max; $i++) {
		$cur_dir .= $temp[$i].'/';
		if (@is_dir($cur_dir)) 
			continue;
		@mkdir($cur_dir, 0777,true);
		@chmod($cur_dir, 0777);
	}
	return is_dir($path);
}

function dir_create($path,$mode = 0777)
{
	if (is_dir($path)) {
		return true;
	}

	$path = get_dir_path($path);
	$item = explode('/', $path);var_dump($item);
	

	$current_dir = '';

	for ($i=0,$len=count($item)-1; $i < $len; $i++) { 
		$current_dir .= $item[$i].'/';
		if (@is_dir($current_dir)) {
			continue;
		}

		@mkdir($current_dir,0777,ture);
		@chmod($current_dir, 0777);
	}

	return is_dir($path);
}

define('SITEDIR', './');

$folder = array (
		'/',
		'Uploads',
		'Public/Data',
		'Cache',
		'Cache/Html',
		'Cache/Cache',
		'Cache/Data',
		'Cache/Temp',
		'Cache/Logs'
	);
		
/*foreach($folder as $dir)
{
	$Testdir = SITEDIR.$dir;
	
	create_dir($Testdir);

}*/

foreach($folder as $dir)
{
	$Testdir = SITEDIR.$dir;
	
	dir_create($Testdir);
	//var_dump($dir);
}

echo "dir create ok!";
?>