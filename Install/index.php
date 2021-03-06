<?php
header("Content-type: text/html; charset=utf-8");

if('5.2.0' > phpversion() ) exit('您的php版本过低，不能安装本软件，请升级到5.2.0或更高版本再安装，谢谢！');

if (file_exists('../install.lock')){
        echo '你已经安装过该系统，如果想重新安装，请先删除站点根目录下的 install.lock 文件，然后再安装。';
        exit;
}

@set_time_limit(1000);
if(phpversion() <= '5.3.0') set_magic_quotes_runtime(0);

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);

//	2015.8.12:包含安装程序公共函数库
include_once './Common/common.php';

/*
 * __FILE__:E:\wamp\www\epp\ed\Install\index.php
 * dirname(__FILE__):E:\wamp\www\epp\ed\Install
 * substr(dirname(__FILE__), 0,-8):E:\wamp\www\epp\ed
 * get_dir_path(substr(dirname(__FILE__), 0, -8)):E:/wamp/www/epp/ed/
 * */
$currentDir = substr(dirname(__FILE__), 0, -8);
define('SITEDIR', get_dir_path($currentDir));

include_once (SITEDIR."/Yourphp/Common/common.php");

$sqlFile = 'yourphp.sql';
$configFile =  'config.php';
if(!file_exists(SITEDIR.'Install/'.$sqlFile) || !file_exists(SITEDIR.'Install/'.$configFile)){
	echo '缺少必要的安装文件!';exit;
}

$steps= array(
	'1'=>'安装许可协议',
	'2'=>'运行环境检测',
	'3'=>'安装参数设置',
	'4'=>'安装详细过程',
	'5'=>'安装完成',
);

$step = isset($_GET['step'])? $_GET['step'] : 1;

switch($step)
{
	case '1':
    include_once ("./templates/s1.html");
    break;
	
	case '2':
		//	==============服务器信息=================
		$name = $_SERVER["SERVER_NAME"];//	服务器域名
		$host = empty ($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"];//	服务器主机
		$os = php_uname();//	服务器操作系统信息
		$server = $_SERVER["SERVER_SOFTWARE"];//	服务器解析引擎
		$phpv = @ phpversion();//	PHP版本
		$max_execution_time = ini_get('max_execution_time');//	脚本最大执行时间
		//	=========================================
		
		//	==============系统环境要求================
		$tmp = function_exists('gd_info') ? gd_info() : array();
		
		$err=0;
		
		//	GD支持
		if(empty($tmp['GD Version'])){
			$gd =  '<font color=red>[×]Off</font>' ;
			$err++;
		}else{
			$gd =  '<font color=green>[√]On</font> '.$tmp['GD Version'];
		}
		
		//	Mysql支持
		if(function_exists('mysql_connect')){
			$mysql = '<font color=green>[√]On</font>';
		}else{
			$mysql = '<font color=red>[×]Off</font>';
			$err++;
		}
		
		//	Upload支持
		if(ini_get('file_uploads')){
			$uploadSize = '<font color=green>[√]On</font> 文件限制:'.ini_get('upload_max_filesize');
		}else{
			$uploadSize = '禁止上传';
		}
		
		//	Session支持
		if(function_exists('session_start')){
			$session = '<font color=green>[√]On</font>' ;
		}else{
			$session = '<font color=red>[×]Off</font>';
			$err++;
		}
		//	=======================================
	
		//	===============目录权限检测=============
		$folder = array (
				'/',
				'Uploads',
				'Public/Data',
				'Cache/Html',
				'Cache',
				'Cache/Cache',
				'Cache/Data',
				'Cache/Temp',
				'Cache/Logs'
		);
		
		foreach($folder as $dir)
		{
			$Testdir = SITEDIR.$dir;
			
			//	创建需要测试的目录
			createDir($Testdir);
		
			//	测试目录是否拥有写入权限
			if(write_dir($Testdir)){
				$w = '<font color=green>[√]写</font>';
			}else{
				$w = '<font color=red>[×]写</font>';
				$err++;
			}
		
			//	测试目录是否拥有读取权限
			if(is_readable($Testdir)){
				$r = '<font color=green>[√]读</font>' ;
			}else{
				$r = '<font color=red>[×]读</font>';
				$err++;
			}
			
			//	目录权限测试结果
			$testRes[$dir] = $r."&nbsp;".$w;
		} 
		//	=================================
		
		include_once ("./templates/s2.html");
		break;
	
	case '3':
			if($_GET['testdbpwd']){
				$dbHost = $_POST['dbHost'].':'.$_POST['dbPort'];
				$conn = @mysql_connect($dbHost, $_POST['dbUser'], $_POST['dbPwd']);
				if($conn){die("1"); }else{die("");}
			}
			
			$scriptName = !empty ($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $_SERVER["PHP_SELF"];
			$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
			$domain = empty ($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST']  : $_SERVER['SERVER_NAME'] ;
			$domain = $domain.$rootpath;
			
			include_once ("./templates/s3.html");
			break;
	case '4':
		if(intval($_GET['install'])){
				$n = intval($_GET['n']);
				$info=array();
	
				$dbHost = trim($_POST['dbHost']);
				$dbPort = trim($_POST['dbPort']);
				$dbName = trim($_POST['dbName']);
				$dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost.':'.$dbPort;
				$dbUser = trim($_POST['dbUser']);
				$dbPwd= trim($_POST['dbPwd']);
				$dbPrefix = empty($_POST['dbPrefix']) ? 'yourphp_' : trim($_POST['dbPrefix']);
	
				$username =  trim($_POST['username']);
				$password = trim($_POST['password']);
				$site_name = addslashes(trim($_POST['site_name']));
				$site_url = trim($_POST['site_url']);
				$site_email = trim($_POST['site_email']);
				$seo_description = trim($_POST['seo_description']);
				$seo_keywords = trim($_POST['seo_keywords']);
	
				$conn = mysql_connect($dbHost, $dbUser, $dbPwd);
				if(!$conn){
					$info['msg'] = "连接数据库失败!";
					echo json_encode($info);exit;
				}
				
				mysql_query("SET NAMES 'utf8'");//,character_set_client=binary,sql_mode='';
				
				$version = mysql_get_server_info($conn);
				
				if($version < 4.1){
					$info['msg'] = '数据库版本太低!';
					echo json_encode($info);exit;
				}
	
				// （1）创建数据库：导入数据前，yourphp数据库没有创建，所以会执行下面代码
				if(!mysql_select_db($dbName, $conn)){
					
					// Ⅰ.根据传入的参数dbName创建数据库
					$query = "CREATE DATABASE IF NOT EXISTS `".$dbName."`;";
					$res = mysql_query($query,$conn);
					
					if(!$res){
						$info['msg'] = '数据库 '.$dbName.' 不存在，也没权限创建新的数据库！';
						echo json_encode($info);exit;
					}else {
						$info['n']=1;
						$info['msg'] = "成功创建数据库:{$dbName}<br>";
						echo json_encode($info);exit;
					}
					
					//	Ⅱ.选择数据库
					mysql_select_db($dbName, $conn);
				}
	
				//	（2）读取数据文件
				$sqldata = file_get_contents(SITEDIR.'Install/'.$sqlFile);
				$sqlFormat = sql_split($sqldata, $dbPrefix);
				
				// exit('test');
				
				//	（3）执行SQL语句
				$counts =count($sqlFormat);
	
				for ($i=$n; $i < $counts; $i++){
					$sql = trim($sqlFormat[$i]);
					$i++;
	
					if (strstr($sql, 'CREATE TABLE')){
						preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
						mysql_query("DROP TABLE IF EXISTS `$matches[1]");
						$ret = mysql_query($sql);
						if($ret){
							$message =  '<font color="gree">成功创建数据表：'.$matches[1].'  </font><br />';
						}else{
							$message =  '<font  color="red">创建数据表失败：'.$matches[1].' </font><br />';
						}
						$info = array('n'=>$i,'msg'=>$message);
						echo json_encode($info); exit;
					}else{
						$ret = mysql_query($sql);
						$message='';
						$info=array('n'=>$i,'msg'=>$message);
						echo json_encode($info); exit;
					}
				}
	
				if($i==999999) exit;
	
	
				$sqldata =   file_get_contents(SITEDIR.'Install/yourphp_data.sql');
				sql_execute($sqldata, $dbPrefix);
				
				$sqldata = file_get_contents(SITEDIR.'Install/yourphp_area.sql');
				sql_execute($sqldata, $dbPrefix);
		 
	
				//	站点多语言设置
				if( $_POST['lang']){
					$langsql = file_get_contents(SITEDIR.'Install/yourphp_lang.sql');
					sql_execute($langsql, $dbPrefix);
				}else{
					@unlink(SITEDIR.'index.php');
					@copy(SITEDIR.'Install/index_one.php',SITEDIR.'index.php');
					mysql_query("UPDATE `{$dbPrefix}menu` SET  `status` ='0'   WHERE model='Lang' ");
				}
	
				mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_name' WHERE varname='site_name' and lang=1");
				mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_url' WHERE varname='site_url' ");
				mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$site_email' WHERE varname='site_email'");
				mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_description' WHERE varname='seo_description'  and lang=1");
				mysql_query("UPDATE `{$dbPrefix}config` SET  `value` = '$seo_keywords' WHERE varname='seo_keywords'  and lang=1");	
	 
	
				//	读取配置文件，并替换真实配置数据
				$strConfig = file_get_contents(SITEDIR.'Install/'.$configFile);
				$strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
				$strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
				$strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
				$strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
				$strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
				$strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
				
				@file_put_contents(SITEDIR.'/'.$configFile, $strConfig);//	在站点根目录生成配置文件
				
				$code=md5(time());
				$query = "UPDATE `{$dbPrefix}config` SET value='$code' WHERE varname='ADMIN_ACCESS'";//	管理员访问控制标志
				mysql_query($query);
	 
	 			//	插入管理员
				$time=time();
				$ip = get_client_ip();
				$password = hash ( sha1, $password.$code );
				$query = "INSERT INTO `{$dbPrefix}user` (`groupid`, `username`, `password`, `realname`, `email`, `createtime`, `updatetime`, `reg_ip`, `status`) VALUES( 1, '$username', '$password', '$username', '$site_email', '$time', '$time', '$ip', '1')";
				mysql_query($query);
	
				$message  = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
				$info=array('n'=>999999,'msg'=>$message);
				echo json_encode($info);exit;
		}
	
		 include_once ("./templates/s4.html");
		 break;
	
	case '5':
		delete_dir(SITEDIR.'/Cache');
		
		$scriptName = !empty ($_SERVER["REQUEST_URI"]) ?  $scriptName = $_SERVER["REQUEST_URI"] :  $scriptName = $_SERVER["PHP_SELF"];
	  $rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)/", "", $scriptName);
		$domain = empty ($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST']  : $_SERVER['SERVER_NAME'] ;
		$domain = $domain.$rootpath;
		
		include_once ("./templates/s5.html");
		@touch('../install.lock');
	    exit ();
}

?>