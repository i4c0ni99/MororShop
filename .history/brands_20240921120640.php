<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

if (isset($_SESSION['user'])) {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/brands.html");

    // Carica lista marche
$brands = $mysqli->query("SELECT * FROM brands ORDER BY name ASC");
if ($brands) {
    while ($brand = $categories->fetch_assoc()) {
        $body->setContent('id', $brand['id']);
        $body->setContent('brand', $brand['id']);
    }
}

    // Eliminazione marca selezionata
    if (isset($_GET['elimina']) && is_numeric($_GET['elimina'])) {
        $category_id = intval($_GET['elimina']);

        $deleteCategoryQuery = "DELETE FROM categories WHERE id = $category_id";
        if ($mysqli->query($deleteCategoryQuery)) {
            echo "Categoria eliminata con successo.";
            // Redirect dopo l'eliminazione
            header('Location: /MotorShop/cbrands.php');
            exit;
        } else {
            echo "Errore durante l'eliminazione della categoria: " . $mysqli->error;
        }
    }

    // Inserimento nuova categoria
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category'])) {
        $newCategoryName = $mysqli->real_escape_string($_POST['category']);
        
        // Controlla se esiste già una categoria con lo stesso nome
        $checkCategoryQuery = "SELECT id FROM categories WHERE name = '$newCategoryName'";
        $checkCategoryResult = $mysqli->query($checkCategoryQuery);

        if ($checkCategoryResult->num_rows > 0) {
            echo "Errore: esiste già una categoria con lo stesso nome.";
        } else {
            $insertCategoryQuery = "INSERT INTO categories (name) VALUES ('$newCategoryName')";
            if ($mysqli->query($insertCategoryQuery)) {
                echo "Categoria inserita con successo.";
                // Redirect dopo l'inserimento
                header('Location: /MotorShop/create-category.php');
                exit;
            } else {
                echo "Errore durante l'inserimento della categoria: " . $mysqli->error;
            }
        }
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent('body', $body->get());
$main->close();

?>