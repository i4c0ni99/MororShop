<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updatedContent = $_POST['html_content'];

    // Percorso del file da sovrascrivere
    $filePath = "skins/motor-html-package/motor/privacy-policy.html";

    // Salva il contenuto aggiornato nel file
    file_put_contents($filePath, $updatedContent);

    // Reindirizza a una pagina di conferma o ricarica la pagina
    header('Location: successo.php');
    exit;
}

?>