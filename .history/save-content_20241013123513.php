<?php

session_start();
require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    //$main = new Template("skins/motor-html-package/motor/frame-customer.html");

    $main->setContent("dynamic", $body->get());
    $main->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updatedContent = $_POST['html_content'];

    // Percorso del file da sovrascrivere
    $filePath = "skins/motor-html-package/motor/privacy-policy.html";

    // Salva il contenuto aggiornato nel file
    file_put_contents($filePath, $updatedContent);

    // Reindirizza a una pagina di conferma o ricarica la pagina
    header('Location: /MotorShop/privacy-policy.php');
    exit;
}

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>