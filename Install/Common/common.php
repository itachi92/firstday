<?php
/*
 *	安装程序公共函数库
 * */

/**
 * write_dir原先Install目录下index.php中的test_write函数，用于测试是否拥有目录写入权限
 * @param string $d，路径字符串
 * @return boolean  
 * */
function write_dir( $d )
{
	$tfile = "_test.txt";
	$fp = @fopen( $d."/".$tfile, "w" );
	if ( !$fp )
	{
		return false;
	}
	fclose( $fp );
	$rs = @unlink( $d."/".$tfile );
	if ( $rs )
	{
		return true;
	}
	return false;
}

function sql_execute($sql,$tablepre) {
	$sqls = sql_split($sql,$tablepre);
	if(is_array($sqls))
	{
		foreach($sqls as $sql)
		{
			if(trim($sql) != '')
			{
				mysql_query($sql);
			}
		}
	}
	else
	{
		mysql_query($sqls);
	}
	return true;
}

function  sql_split($sql,$tablepre) {

	if($tablepre != "yourphp_") $sql = str_replace("yourphp_", $tablepre, $sql);
	$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sql);

	if($r_tablepre != $s_tablepre) $sql = str_replace($s_tablepre, $r_tablepre, $sql);
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query)
	{
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		$queries = array_filter($queries);
		foreach($queries as $query)
		{
			$str1 = substr($query, 0, 1);
			if($str1 != '#' && $str1 != '-') $ret[$num] .= $query;
		}
		$num++;
	}
	return $ret;
}

/**
 * 获取目录的路径
 * 	将路径中的"\\"转换成"/"，末尾没有"/"的话添加"/"
 * @param string $path，路径字符串
 * @example E:\wamp\www\epp\ed 经转换后 E:/wamp/www/epp/ed/
 * @return  string $path
 * */
function get_dir_path($path) {
	$path = str_replace('\\', '/', $path);
	if(substr($path, -1) != '/') $path = $path.'/';
	return $path;
}

/**
 * create_dir原先是在yourphp/Common/common.php中的dir_create函数
 * @param string $path，路径字符串
 * @param number $mode，模式
 * @return boolean  
 * */
function create_dir($path, $mode = 0777) {
	if(is_dir($path)) return TRUE;
	$ftp_enable = 0;
	$path = dir_path($path);
	$temp = explode('/', $path);
	$cur_dir = '';
	$max = count($temp) - 1;
	for($i=0; $i<$max; $i++) {
		$cur_dir .= $temp[$i].'/';
		if (@is_dir($cur_dir)) continue;
		@mkdir($cur_dir, 0777,true);
		@chmod($cur_dir, 0777);
	}
	return is_dir($path);
}

// 获取客户端IP地址
function get_client_ip() {
	static $ip = NULL;
	if ($ip !== NULL) return $ip;
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$pos =  array_search('unknown',$arr);
		if(false !== $pos) unset($arr[$pos]);
		$ip   =  trim($arr[0]);
	}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif (isset($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// IP地址合法验证
	$ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
	return $ip;
}

/**
 * Ⅰ.根据传入的路径创建目录：传入单个字符串则创建单个目录，传入数组则循环创建多级目录
 * Ⅱ.目录模式
 * @param array&string $dirs
 * @param number $mode
 * @example createDir(array('test','hello/world'));或者createDir('demo');
 * @return boolean  
 * */
function createDir($dirs,$mode=0777)
{
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
			createDir($dir);
		}
	}

	return false;
}