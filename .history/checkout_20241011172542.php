<?php

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";
include "include/utils/priceFormatter.php";

// Verifica se l'utente è loggato
if (isset($_SESSION['user']['groups'])) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    header("Location: /MotorShop/login.php");
    exit();
}

$body = new Template("skins/motor-html-package/motor/checkout.html");

// Recuperare il titolo del prodotto
function getProductTitle($subproductId) {
    global $mysqli; 

    $query = "SELECT title FROM products WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $subproductId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['title'];
    } else {
        return 'Titolo non disponibile';
    }
}

$userEmail = $mysqli->real_escape_string($_SESSION['user']['email']);

// Carica gli indirizzi di spedizione dell'utente
$addressesQuery = "SELECT * FROM shipping_address WHERE users_email = ?";
$stmtAddresses = $mysqli->prepare($addressesQuery);
$stmtAddresses->bind_param("s", $userEmail);
$stmtAddresses->execute();
$resultAddresses = $stmtAddresses->get_result();

$addresses = [];
while ($row = $resultAddresses->fetch_assoc()) {
    $addresses[] = $row;
}

$stmtAddresses->close();

foreach ($addresses as $address) {
    $body->setContent("id", $address['id']);
    $body->setContent("ADname", $address['name']);
    $body->setContent("ADsurname", $address['surname']);
    $body->setContent("ADphone", $address['phone']);
    $body->setContent("ADprovince", $address['province']);
    $body->setContent("ADcity", $address['city']);
    $body->setContent("ADstreetAddress", $address['streetAddress']);
    $body->setContent("ADcap", $address['cap']);
}

// Prodotti e prezzo totale dell'ordine effettivo
$totalPrice = 0;
$products = [];

// Verifica se l' indirizzo di spedizione è stato selezionato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['address_list'])) {
        echo "Errore: nessun indirizzo di spedizione selezionato.";
        exit();
    }

    // Recupera l'ID dell'indirizzo di spedizione
    $shippingAddressId = $mysqli->real_escape_string($_POST['address_list']);

    // Prodotti nel carrello dell'utente
    $queryCart = "SELECT c.subproduct_id, c.quantity, sp.price, sp.availability, sp. quantity AS stock 
              FROM cart c 
              INNER JOIN sub_products sp ON c.subproduct_id = sp.id 
              WHERE c.user_email = '$userEmail'";
$resultCart = $mysqli->query($queryCart);

if ($resultCart) {

    $products = []; // Array per salvare i dettagli dei prodotti

     // Calcola importo totale e salva i dati dei prodotti nell'array
     while ($row = $resultCart->fetch_assoc()) {
        
        // Dati del carrello
        $subproductId = $row['subproduct_id'];
        $quantity = $row['quantity'];
        // Dati del sottoprodotto
        $price = $row['price'];
        $availability = $row['availability'];
        $stockQuantity = $row['stock'];

         // Verifica che il prodotto sia disponibile e che la quantità richiesta sia disponibile in stock
         if ($availability == 1 && $cartQuantity <= $stockQuantity) {
            
        // Calcola il totale parziale per ciascun sottoprodotto nel carrello
        $subtotal = $price * $quantity;
        $totalPrice += $subtotal; // Somma i prezzi dei prodotti

        $productTitle = getProductTitle($subproductId);

        // Aggiungi dettagli del prodotto all'array
        $products[] = [
            'title' => $productTitle,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    } else {
            // Prodotto non disponibile o quantità richiesta non in stock
            echo "Prodotto con ID $subproductId non disponibile o quantità insufficiente. Sarà escluso dall'ordine.<br>";
        }
    }

    // Generazione di un numero casuale univoco per l'ordine
    $uniqueOrderNumber = generateUniqueOrderNumber($mysqli);

    // Dettagli dell'ordine (presi dalla form)
    $orderDetails = $mysqli->real_escape_string($_POST['order_details']);

    // Metodo di pagamento selezionato dalla form
    $paymentMethod = $mysqli->real_escape_string($_POST['payment_method']);

    // Data corrente
    $currentDate = date('Y-m-d H:i:s');

    // Stato dell'ordine (impostato su "pending")
    $orderState = "pending";

    // Query per inserire l'ordine nella tabella orders
    $insertOrderQuery = "INSERT INTO orders (shipping_address_id, totalPrice, details, paymentMethod, date, state, number, users_email) 
                     VALUES ('$shippingAddressId', $totalPrice, '$orderDetails', '$paymentMethod', '$currentDate', '$orderState', '$uniqueOrderNumber', '$userEmail')";



















";




    // Invio email, svuotamento carrello, ecc.
    
} else {
    echo "Errore durante l'inserimento dell'ordine: " . $mysqli->error;
}

if ($mysqli->query($insertOrderQuery)) {
    // Ordine inserito con successo
    echo "Ordine inserito con successo! Numero ordine: " . $uniqueOrderNumber;
    // Recupera i dettagli dell'indirizzo di spedizione selezionato
    $addressQuery = "SELECT * FROM shipping_address WHERE id = ?";
    $stmtAddress = $mysqli->prepare($addressQuery);
    $stmtAddress->bind_param("i", $shippingAddressId);
    $stmtAddress->execute();
    $resultAddress = $stmtAddress->get_result();
    $address = $resultAddress->fetch_assoc();
    $stmtAddress->close();
    // Corpo email
    $to = $userEmail;
    $subject = 'Conferma Ordine #' . $uniqueOrderNumber;
    $message = '<html><body>';
    $message .= '<h2>Gentile cliente,</h2>';
    $message .= '<p>Grazie per il tuo ordine! Ecco i dettagli:</p>';
    $message .= '<h3>Indirizzo di Spedizione</h3>';
    $message .= '<p>';
    $message .= 'Nome: ' . $address['name'] . '<br>';
    $message .= 'Cognome: ' . $address['surname'] . '<br>';
    $message .= 'Telefono: ' . $address['phone'] . '<br>';
    $message .= 'Provincia: ' . $address['province'] . '<br>';
    $message .= 'Città: ' . $address['city'] . '<br>';
    $message .= 'Indirizzo: ' . $address['streetAddress'] . '<br>';
    $message .= 'CAP: ' . $address['cap'] . '<br>';
    $message .= '</p>';
    $message .= '<h3>Riepilogo Ordine</h3>';
    $message .= '<table border="1">';
    $message .= '<tr><th>Prodotto</th><th>Quantità</th><th>Subtotale</th></tr>';
    foreach ($products as $product) {
        $message .= '<tr>';
        $message .= '<td>' . htmlspecialchars($product['title']) . '</td>';
        $message .= '<td>' . $product['quantity'] . '</td>';
        $message .= '<td>' . priceFormatter($product['subtotal']) . '</td>';
        $message .= '</tr>';
    }
    $message .= '</table>';
    $message .= '<p><strong>Totale Ordine:</strong> ' . priceFormatter($totalPrice) . '</p>';
    $message .= '<p>Dettagli aggiuntivi: ' . $orderDetails . '</p>';
    $message .= '<p>Metodo di Pagamento: ' . $paymentMethod . '</p>';
    $message .= '<p>Grazie per aver scelto il nostro negozio!</p>';
    $message .= '</body></html>';
    // Intestazioni dell'email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <noreply@motorshop.com>' . "\r\n";
    // Invio dell'email
    if (mail($to, $subject, $message, $headers)) {
        echo "Email di conferma inviata con successo.";
    } else {
        echo "Errore durante l'invio dell'email di conferma.";
    }

            // Ora puoi svuotare il carrello eliminando le righe associate all'utente
            $deleteCartQuery = "DELETE FROM cart WHERE user_email = '$userEmail'";
            if ($mysqli->query($deleteCartQuery)) {
                echo "Carrello svuotato con successo.";
            } else {
                echo "Errore durante l'eliminazione dei prodotti dal carrello: " . $mysqli->error;
            }
        } else {
            echo "Errore durante l'inserimento dell'ordine: " . $mysqli->error;
        }
    } else {
        echo "Errore durante la query del carrello: " . $mysqli->error;
    }
}

// Funzione per generare un numero casuale univoco di 5 cifre per l'ordine
function generateUniqueOrderNumber($mysqli) {
    $uniqueNumber = mt_rand(10000, 99999);
    $query = "SELECT number FROM orders WHERE number = '$uniqueNumber'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        // Se il numero è già presente, richiama ricorsivamente la funzione per generare un altro numero
        return generateUniqueOrderNumber($mysqli);
    } else {
        // Se il numero non è presente nella tabella, restituiscilo
        return $uniqueNumber;
    }
}

// Funzione per formattare il prezzo
function priceFormatter($price) {
    // Personalizza la formattazione del prezzo come necessario (esempio: € 200.00)
    return '€ ' . number_format($price, 2);
}

$main->setContent("dynamic", $body->get());
$main->close();

?>