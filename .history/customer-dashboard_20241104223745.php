<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']['email'])) { 

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/dashboard.html");

    $main->setContent('name', $_SESSION['user']['name']);
    $main->setContent('surname', $_SESSION['user']['surname']);
    $main->setContent('email', $_SESSION['user']['email']);

    // ordini del cliente
    $query = "SELECT id, number, state, date, paymentMethod, totalPrice, details FROM orders WHERE users_email = '{$_SESSION['user']['email']}' ORDER BY id DESC";

    $oid = $mysqli->query($query);
    $result = $oid;

    if ($result && $result->num_rows > 0) {
        foreach ($result as $order) {
            $body->setContent("ord_id", $order['id']);
            $body->setContent("ord_number", $order['number']);
            $body->setContent("ord_state", $order['state']);
            $body->setContent("ord_date", $order['date']);
            $body->setContent("ord_paymentMethod", $order['paymentMethod']);
            $body->setContent("ord_totalPrice", $order['totalPrice']);
            $body->setContent("ord_details", $order['details']);

            $body->setContent("manage", '<a href="/MotorShop/my-orders.php?id=' . $order['id'] . '" class="btn btn-primary">Apri</a>');
            
        }
    } else {
        // Nessun ordine trovato
        $body->setContent("ord_id", '');
        $body->setContent("ord_number", 'Non hai ancora effettuato un ordine.');
        $body->setContent("ord_state", '');
        $body->setContent("ord_date", '');
        $body->setContent("ord_paymentMethod", '');
        $body->setContent("ord_totalPrice", '');
        $body->setContent("ord_details", '');
        $body->setContent("manage",'');
    }
} else {
    header("location:/../MotorShop/login.php");
    exit;
}

// recupera le recensioni dell'utente
$reviews = $mysqli->query("SELECT id, products_id, rate, review, date from feedbacks WHERE users_email =
'{$_SESSION['user']['email']}'");

if ($reviews != null) {
    $feed = $reviews;

    if ($feed && $feed->num_rows > 0) {
        foreach ($feed as $f) {
            $body->setContent("f_id", $f['id']);
            $body->setContent("prod_id", $f['products_id']);
            $body->setContent("f_rate", $f['rate']);
            $body->setContent("f_review", $f['review']);
            $body->setContent("f_date", $f['date']);
            
            $info_title = $mysqli->query("SELECT title FROM products WHERE id = " . $f['products_id']);
            $prod_title = $info_title->fetch_assoc();
            $body->setContent("prod_title", $prod_title['title']);
        }
    }

// eliminazione recensione selezionata
if (isset($_GET['f_id']) && is_numeric($_GET['f_id'])) {
    $feedback_id = intval($_GET['f_id']);
    $deleteFeedbackQuery = "DELETE FROM feedbacks WHERE id = $feedback_id";
    if ($mysqli->query($deleteFeedbackQuery)) {
        header('Location: /MotorShop/customer-dashboard.php');
        exit;
    } else {
        echo "Errore durante l'eliminazione della recensione: " . $mysqli->error;
    }
}

}

$main->setContent("dynamic", $body->get());
$main->close();