<?php  
	/*
		测试执行SQL文件
	 */
	function Dcup($var = NULL,$exit = FALSE){
		header("Content-type:text/html;charset=utf-8");

		if (empty($var)) {
			echo 'null';
		}else {
		// int string float...
			echo '<pre>';
			var_dump($var);
			echo '</pre>';

			if ($exit) exit();
		}
	}

	function  sql_split($sql,$tablepre) {

		//	替换表前缀
		if($tablepre != "yourphp_"){
			$sql = str_replace("yourphp_", $tablepre, $sql);
		} 

		// 
		$sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sql);


		$sql = str_replace("\r", "\n", $sql);

		$queriesarray = explode(";\n", trim($sql));// 拆分成一个完整的create语句（SQL语句）
		unset($sql);

		$ret = array();
		$num = 0;
		foreach($queriesarray as $query)
		{
			$ret[$num] = '';
			$queries = explode("\n", trim($query));// 拆分成行
			$queries = array_filter($queries);//  删除$queries数组中所有等值为false的条目
			foreach($queries as $query)
			{
				// 	去掉sql文件中的注释部分
				$str1 = substr($query, 0, 1);
				if($str1 != '#' && $str1 != '-') 
					$ret[$num] .= $query;// 拼接所有符合条件的sql语句
			}
			$num++;
		}
		return $ret;// 可执行的sql语句，数组
	}

	// create database/create table/update/insert
	function sql_execute($sql,$tablepre) {
		/*$sqls = sql_split($sql,$tablepre);
		if(is_array($sqls))
		{
			foreach($sqls as $sql)
			{
				if(trim($sql) != ''){
					mysql_query($sql);
				}
			}
		}else{
			mysql_query($sqls);
		}
		return true;*/

	}

	function execute($type,$sqlsArray)
	{
		$return = array();
		$type = intval($type);
		$dbName = 'test';

		// Cretae database
		if ($type === 1) {
			$query = "CREATE DATABASE IF NOT EXISTS `".$dbName."`;";
			if(mysql_query($query)){
				$return['create_database'] = 'ok';
			}else{
				$return['create_database'] = 'error';
			}
		}

		// Create tables/update/insert(others)
		if ($type === 2) {
			if (is_array($sqlsArray)) {
				$sqlsCount = count($sqlsArray);
				for ($i=0; $i < $sqlsCount; $i++) { 
					$sql = trim($sqlsArray[$i]);

					//  判断sql数组中是否有Create table语句
					if (strstr($sql, 'CREATE TABLE')){
						preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);

						// $matches[0] = "CREATE TABLE `your_access`";
						// $matches[1] = "your_access";
						mysql_query("DROP TABLE IF EXISTS `$matches[1]");// 如果数据表已存在，则删除
						$bool = mysql_query($sql);// 执行sql文件中的单条sql语句

						if($bool){
							$return['create_table'] = 'ok';
						}else{
							$return['create_table'] = 'error';
						}
					}else {// update/insert(others)
						$bool = mysql_query($sql);
						$bool ? $return['sql_execute'] = 'ok' : $return['sql_execute'] = 'error';
					}
				}
			}
		}

		return $return;
	}
// ==========================================================================

	$dbHost = "localhost";
	$dbPort = '3306';
	$dbUser = 'root';
	$dbPwd = '';
	$dbName = 'test';
	$dbPrefix = '';
	$n = 0;

	// Ⅰ.连接数据库服务器+选择操作的数据库（没有则创建）
	$conn = mysql_connect($dbHost.":".$dbPort,$dbUser,$dbPwd);
	if ($conn) {
		mysql_select_db($dbName,$conn);
		mysql_query('set names utf8');
	}else{
		die('connect to mysql server error o_O');
	}

	// Ⅱ.读取sql文件
	$sqlFile = 'yourphp.sql';
	$sqlFileContent = file_get_contents($sqlFile);

	// Ⅲ.替换sql文件表前缀、去掉注释
	$sqls = sql_split($sqlFileContent, $dbPrefix);

	// Ⅳ.执行sql文件
	// for ($i=$n,$counts = count($sqls); $i < $counts; $i++){
	// 	$sql = trim($sqls[$i]);
	// 	// $i++;

	// 	//  判断sql数组中是否有Create table语句
	// 	if (strstr($sql, 'CREATE TABLE')){
	// 		preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);

	// 		// $matches[0] = "CREATE TABLE `your_access`";
	// 		// $matches[1] = "your_access";
	// 		mysql_query("DROP TABLE IF EXISTS `$matches[1]");// 如果数据表已存在，则删除
	// 		$ret = mysql_query($sql);// 执行sql文件中的单条sql语句

	// 		if($ret){
	// 			$message =  '<font color="gree">成功创建数据表：'.$matches[1].'  </font><br />';
	// 		}else{
	// 			$message =  '<font  color="red">创建数据表失败：'.$matches[1].' </font><br />';
	// 		}

	// 		$info = array('n'=>$i,'msg'=>$message);
	// 		Dcup($info);
	// 	}else{
	// 		$ret = mysql_query($sql);
	// 		$message='';
	// 		$info=array('n'=>$i,'msg'=>$message);
	// 		echo json_encode($info); exit;
	// 	}
	// }

	$res = execute($sqls,'2');
	Dcup($res);
?>