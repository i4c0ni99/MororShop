<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/home-customizer.html");


if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $slides = $mysqli->query("SELECT * FROM slider"); 
    $slide_result = $slides;
    if($slide_result && $slide_result -> num_rows > 0) {
        foreach ($slide_result as $page) {
            $body->setContent("sliderId",$page['id']);
            $body->setContent("sliderTitle",$page['title']);
            $body->setContent("sliderDescription",$page['description']);   
    }
} 

    if (isset($_POST['submit'])) {

        // Inserisci pagina slider
        $title = $mysqli->real_escape_string($_POST['title']);
        $description = $mysqli->real_escape_string($_POST['description']);
        $insertQuery = "INSERT INTO slider (title, description, image) 
                VALUES ('$title', '$description', '$button', '$link')";
                if ($mysqli->query($insertQuery)) {
                    $product_id = $mysqli->insert_id;
                }
    }

    if (isset($_POST['delete'])) {
        if (isset($_POST['sliderId']) && is_numeric($_POST['sliderId'])) {
            $id = intval($_POST['sliderId']);
    
            // Inizia una transazione
            $mysqli->begin_transaction();
            
            try {
                // Query preparata per eliminare la slide
                $deleteSlider = $mysqli->prepare("DELETE FROM slider WHERE id = ?");
                $deleteSlider->bind_param("i", $id);  // 'i' per tipo integer
                $deleteSlider->execute();
    
                // Conferma l'eliminazione
                $mysqli->commit();
    
                // Messaggio di successo
                $_SESSION['message'] = "Slide eliminata con successo.";
            } catch (Exception $e) {
                // In caso di errore, esegui un rollback
                $mysqli->rollback();
    
                // Messaggio di errore
                $_SESSION['error'] = "Errore durante l'eliminazione della slide.";
            }
    
            // Redirect per aggiornare la pagina
            header('Location: /MotorShop/home-customizer.php');
            exit();
        }
    }    
    
    $main->setContent('user',$_SESSION['user']['name']);
    $main->setContent("body", $body->get());
    $main->close();

} else {
    
    header("Location: /MotorShop/login.php");
    exit();
    
}

?>