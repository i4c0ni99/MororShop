<?php 

session_start();

require_once "include/template2.inc.php";
require_once "include/dbms.inc.php";

// Elimina variabili di sessione
$_SESSION = array();

// Cancella il cookie di sessione 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Reindirizza allogin
header("Location: /MotorShop/login.php");
exit();
?>