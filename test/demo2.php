<?php
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

/**
 * Ⅰ.根据传入的路径创建目录：传入单个字符串则创建单个目录，传入数组则循环创建多级目录
 * Ⅱ.目录模式
 * 
 */

function my_create_dir($dirs,$mode=0777)
{
	//var_dump($dirs);exit();
	

	if (is_string($dirs)) {
		if (is_dir($dirs)) return true;

		$dirs = str_replace('\\', '/', $dirs);
		if(substr($dirs, -1) != '/') $dirs = $dirs.'/';
		
		$path = explode('/', $dirs);
		$current_dir = '';

		for ($i=0,$len=count($path)-1; $i < $len; $i++) { 
			$current_dir .= $path[$i].'/';
			if (@is_dir($current_dir)) continue;
			@mkdir($current_dir,$mode,ture);
			@chmod($current_dir, $mode);
		}

		return is_dir($current_dir);
	}

	if (is_array($dirs)) {
		foreach ($dirs as $dir) {
			my_create_dir($dir);
		}
	}

	return false;
}

my_create_dir(array('test','hello/world'));


?>