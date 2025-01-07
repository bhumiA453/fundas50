<?php
// Database connection settings
$host = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'fundas';

// Create connection
$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Capture POST data
$name = $_POST['name'];
$email = $_POST['email'];
$whatsapp_no = $_POST['phone'];
$partner_accompany = $_POST['partner_accompany'] ?? null;
$partner_name = $_POST['partner_name'] ?? null;
$arrival_date = $_POST['arrival_date'] ?? null;
$departure_date = $_POST['departure_date'] ?? null;
$room_type = $_POST['h_room_type'];
$tripExtensionNights = $_POST['tripExtensionNights'] ?? null;
$tripExtensionAmount = $_POST['tripExtensionAmount'];
$conferenceAmount = $_POST['conferenceAmount'];
$programCharges = $_POST['programCharges'];
$totalSterlingAmount = $_POST['totalSterlingAmount'];
$amountRameshwar = $_POST['amountRameshwar'];
$totalExpense = $_POST['totalExpense'];
$bank_ref_no = $_POST['bank_ref_no'] ?? null;
$card_ref_no = $_POST['card_ref_no'] ?? null;
$card_name = $_POST['card_name'] ?? null;
$payment_date = $_POST['payment_date'] ?? null;
// (Capture other form fields as needed)

// Insert data into the register table
$sql = "INSERT INTO register (name, email, whatsapp_no, partner_accompany, partner_name, arrival_date, departure_date,room_type,tripExtensionNights,tripExtensionAmount,conferenceAmount,programCharges,totalSterlingAmount,amountRameshwar,totalExpense,payment_date,card_name,card_ref_no,bank_ref_no)
VALUES ('$name', '$email', '$whatsapp_no', '$partner_accompany', '$partner_name', '$arrival_date', '$departure_date','$room_type','$tripExtensionNights','$tripExtensionAmount','$conferenceAmount','$programCharges','$totalSterlingAmount','$amountRameshwar','$totalExpense','$payment_date','$card_name','$card_ref_no','$bank_ref_no')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}


// Close connection
$conn->close();
?>
