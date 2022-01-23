<?php
$host='localhost';
$db='uno';
require_once "local_config.php";

$user=USERNAME;
$pass=DB_PWD;

if(gethostname()=='users.iee.ihu.gr') {
	$mysqli = new mysqli($host, $user, $pass, $db,null,'/home/student/it/2017/it174890/mysql/run/mysql.sock');
} else {
    $mysqli = new mysqli($host, $user, $pass, $db);
}

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . 
    $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

?>
