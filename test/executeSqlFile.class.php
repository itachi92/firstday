<?php  
	/**
	* 
	*/
	class ExectueSqlFile
	{
		
		public $dbHost = "localhost";
		public $dbPort = '3306';
		public $dbUser = 'root';
		public $dbPwd = '';
		public $dbName = 'test';
		public $dbPrefix = '';
		public $conn = Null;
		public $sqlFile = '';

		function __construct()
		{
			header("Content-type:text/html;charset=utf-8");

			$dbHost = $this->dbHost. ":".$this->dbPort;
			$this->conn = mysql_connect($this->dbHost,$this->dbUser,$this->dbPwd);
			if (!$this->conn) {
				die("Connect to mysql server error o_O");
			}
		}

		public function sql_split($sqlFile,$dbPrefix)
		{
			$this->sqlFile = $sqlFile;
			$this->dbPrefix = $dbPrefix;

			$sqlFileContent = file_get_contents($this->sqlFile);

			//	替换表前缀
			if($this->dbPrefix != "yourphp_"){
				$sqlFileContent = str_replace("yourphp_", $this->dbPrefix, $sqlFileContent);
			} 

			// 替换内容
			$sqlFileContent = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8",$sqlFileContent);


			$sqlFileContent = str_replace("\r", "\n", $sqlFileContent);

			// 拆分成一个完整的create语句（SQL语句）
			$queriesarray = explode(";\n", trim($sqlFileContent));
			unset($sqlFileContent);

			$sqlsArray = array();
			$num = 0;
			foreach($queriesarray as $query)
			{
				$sqlsArray[$num] = '';

				// 拆分成行
				$queries = explode("\n", trim($query));
				//  删除$queries数组中所有等值为false的条目
				$queries = array_filter($queries);

				foreach($queries as $query)
				{
					// 	去掉sql文件中的注释部分
					$str1 = substr($query, 0, 1);
					if($str1 != '#' && $str1 != '-') 
						$sqlsArray[$num] .= $query;// 拼接所有符合条件的sql语句
				}
				$num++;
			}
			return $sqlsArray;// 可执行的sql语句，数组

		}

		public function sql_execute($type,$dbName,$sqlsArray = array())
		{

			$return = array();
			$type = intval($type);
			$this->dbName = $dbName;

			// Cretae database
			if ($type === 1) {
				$query = "CREATE DATABASE IF NOT EXISTS `".$this->dbName."`;";
				if(mysql_query($query) && mysql_select_db($this->dbName)){
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
							$bool = mysql_query($sql,$this->conn);// 执行sql文件中的单条sql语句

							$bool ? $return['create_table'] = 'ok' : $return['create_table'] = 'error';

						}else {// update/insert(others)
							$bool = mysql_query($sql,$tihs->conn);
							$bool ? $return['sql_execute'] = 'ok' : $return['sql_execute'] = 'error';
						}

					}
				}
			}

			return $return;
		}
}


	$obj  = new ExectueSqlFile();
	
	$obj->sql_execute('1','test2');// Ⅰ.创建数据库

	$sqlsArray = $obj->sql_split('yourphp.sql','tt_');// Ⅱ.格式化sql文件
	$obj->sql_execute('2','test2',$sqlsArray);// Ⅲ.执行sql文件

	var_dump($res);
?>