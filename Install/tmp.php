<?php
	// phpinfo();

	// (1)服务器信息
	// (2)系统环境要求
	// (3)目录权限检测
	// 

	/*foreach($folder as $dir)
		{
			$Testdir = SITEDIR.$dir;
			
			//	创建需要测试的目录
			create_dir($Testdir);
		
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
			
			echo "<tr>";
			echo "<td>$dir</td>";
			echo "<td>读写</td>";
			echo "<td>$r&nbsp;$w</td>";
			echo "</tr>";
		} */

?>
