<?php
// DELETE FROM `ticket_sales`; -> clear the DB entries
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "payment_system";
$paypal = 0;
$crypto = 0;
$credit_card = 0;
$transaction_fee = 0;
$paypal_status = 1;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate user_id exists and is numeric
    if (!isset($_POST["user_id"]) || !is_numeric($_POST["user_id"])) {
        header('Location: index.php?error=invalid_user');
        exit;
    }
    
    $user_id = (int)$_POST["user_id"];
    
    // Use prepared statement for security
    $sql = "SELECT * FROM account WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $paypal = $row["PayPal"];
            $crypto = $row["Cryptocurrency"];
            $credit_card = $row["CreditCard"];
        }
    }

    // Validate inputs
    if (!isset($_POST['amount']) || !isset($_POST['description']) || empty($_POST['description'])) {
        header('Location: index.php?error=missing_fields');
        exit;
    }
    
    if (!isset($_POST['payment_method']) || $_POST['payment_method'] == "") {
        header('Location: index.php?error=invalid_payment_method');
        exit;
    }
    
    $payment_method = (int)$_POST['payment_method'];
    
    if($payment_method == 1) {
        $sql = "SELECT * FROM user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $paypal_status = $row["paypal_status"];
            }
        }
        
        if($paypal_status == 0) {
            header('Location: index.php?error=paypal_blocked');
            exit;
        }
    }

    $date = date("Y-m-d");
    
    $sql = "SELECT * FROM payment_type WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payment_method);
    $stmt->execute();
    $result2 = $stmt->get_result();
    
    if ($result2->num_rows > 0) {
        while($row = $result2->fetch_assoc()) {
            $transaction_fee = $row["transaction_fee"];
        }
    }

    $amount = (float)$_POST['amount'];
    $debit_amount = $amount + $transaction_fee;
    
    if($payment_method == 1) {
        if($debit_amount > $paypal) {
            header('Location: index.php?error=Insufficient Paypal Balance. Your account has R ' . htmlspecialchars($paypal) . ' But you want to pay R ' . htmlspecialchars($amount) . " and a transaction fee of R " . htmlspecialchars($transaction_fee));
            exit;
        }
    }
    
    if($payment_method == 2) {
        if($debit_amount > $crypto) {
            header('Location: index.php?error=Insufficient Cryptocurrency Balance. Your account has R ' . htmlspecialchars($crypto) . ' But you want to pay R ' . htmlspecialchars($amount) . " and a transaction fee of R " . htmlspecialchars($transaction_fee));
            exit;
        }
    }
    
    if($payment_method == 5) {
        if($debit_amount > $credit_card) {
            header('Location: index.php?error=Insufficient Credit Card Balance. Your account has R ' . htmlspecialchars($credit_card) . ' But you want to pay R ' . htmlspecialchars($amount) . " and a transaction fee of R " . htmlspecialchars($transaction_fee));
            exit;
        }
    }

    // Debiting your account
    if($payment_method == 1) {
        $paypal = $paypal - $debit_amount;
        $sql = "UPDATE account SET PayPal = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $paypal, $user_id);
    }
    
    if($payment_method == 2) {
        $crypto = $crypto - $debit_amount;
        $sql = "UPDATE account SET Cryptocurrency = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $crypto, $user_id);
    }
    
    if($payment_method == 5) {
        $credit_card = $credit_card - $debit_amount;
        $sql = "UPDATE account SET CreditCard = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $credit_card, $user_id);
    }

    $transaction_id = date("YmdHis");
    
    if ($stmt->execute()) {
        $description = htmlspecialchars($_POST["description"]);
        
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idisss", $user_id, $amount, $payment_method, $date, $description, $transaction_id);
        $stmt->execute();
        
        $fee_description = "Transaction Fee";
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("idisss", $user_id, $transaction_fee, $payment_method, $date, $fee_description, $transaction_id);
        $stmt->execute();
        
        header('Location: index.php?success=true');
        exit;
    } else {
        header('Location: index.php?error=db_error');
        exit;
    }
}

// Query data after handling POST
$sql = "SELECT * FROM payment_type";
$result1 = $conn->query($sql);
?>

<html>
<head>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
        }
        .success { 
            background-color: #dff0d8; 
            color: #3c763d; 
        }
        .error { 
            background-color: #f2dede; 
            color: #a94442; 
        }
    </style>
</head>
<body>
<?php
// Display success/error messages
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    echo '<div class="message success">Transaction Completed Successfully!</div>';
} elseif (isset($_GET['error'])) {
    echo '<div class="message error">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>

<h1>Transaction</h1>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    Payment Method:
    <select name="payment_method" required>
        <option value="">Select a payment method</option>
        <?php if ($result1->num_rows > 0) {
            while($row = $result1->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row["id"]); ?>"><?php echo htmlspecialchars($row["payment_method"]); ?></option>
            <?php }
        } ?>
    </select>
    <br><br>
    
    Amount: <input type="number" name="amount" required> <br><br>
    Description: <input type="text" name="description" required> <br><br>
    
    <input type="hidden" name="user_id" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars((string)$_GET['user_id'], ENT_QUOTES, 'UTF-8') : ''; ?>">
    <button type="submit">Submit</button>
</form>

<?php
$conn->close();
?>
</body>
</html>
