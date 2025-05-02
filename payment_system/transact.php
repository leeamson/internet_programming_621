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
    $sql = "SELECT * FROM account WHERE user_id = ". $_POST["user_id"];
    // print_r($sql);
    $result = $conn->query($sql);
    print_r($result->num_rows );
    // exit;
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $paypal = $row["PayPal"];
            $crypto = $row["Cryptocurrency"];
            $credit_card = $row["CreditCard"];
        }
    }

    // Validate inputs
    if (!isset($_POST['amount']) || !isset($_POST['description'])) {
        header('Location: index.php?error=missing_fields');
        exit;
    }

    if (!isset($_POST['payment_method']) || $_POST['payment_method']=="" ) {
        header('Location: user.php?id='.$_POST["user_id"].'&error=invalid_payment_method');
        exit;
    }

    if($_POST['payment_method']==1){
        $sql = "SELECT * FROM user WHERE id = ". $_POST["user_id"];
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $paypal_status = $row["paypal_status"];
            }
        }
        if($paypal_status==0){
            header('Location: user.php?id='.$_POST["user_id"].'&error=paypal_blocked');
            exit;
        }
    }

    $date = date("Y-m-d");
    $sql = "SELECT * FROM payment_type WHERE id=".$_POST['payment_method'];
    $result2 = $conn->query($sql);
    if ($result2->num_rows > 0) {
        while($row = $result2->fetch_assoc()) {
            $transaction_fee = $row["transaction_fee"];
        }
    }

    if($_POST['payment_method'] == 1 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        if($debit_amount > $paypal) {
            header('Location: index.php?error=Insufficient Paypal Balance. Your account has R ' . $paypal . ' But you want to pay R ' . $_POST['amount']. " and a transaction fee of R ". $transaction_fee);
            exit;
        }
    }

    if($_POST['payment_method'] == 2 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        if($debit_amount > $crypto) {
            header('Location: index.php?error=Insufficient Paypal Balance. Your account has R ' . $crypto . ' But you want to pay R ' . $_POST['amount']. " and a transaction fee of R ". $transaction_fee);
            exit;
        }
    }

    if($_POST['payment_method'] == 5 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        if($debit_amount > $credit_card) {
            header('Location: index.php?error=Insufficient Paypal Balance. Your account has R ' . $credit_card . ' But you want to pay R ' . $_POST['amount'] . " and a transaction fee of R ". $transaction_fee);
            exit;
        }
    }

    // Debiting your account
    if($_POST['payment_method'] == 1 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        $paypal=$paypal - $debit_amount;
        $sql = "UPDATE account
                SET PayPal = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $paypal, $_POST['user_id']);
        print_r($stmt);
    }

    if($_POST['payment_method'] == 2 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        $credit_card=$credit_card - $debit_amount;
        $sql = "UPDATE account
                SET CreditCard = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $credit_card, $_POST['user_id']);
        print_r($stmt);
    }

    if($_POST['payment_method'] == 5 ) {
        $debit_amount=$_POST['amount']+ $transaction_fee;
        $crypto=$crypto - $debit_amount;
        $sql = "UPDATE account
                SET Cryptocurrency = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $crypto, $_POST['user_id']);
        print_r($stmt);
    }

    $transaction_id = date("YmdHis");
    if ($stmt->execute()) {
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $_POST['user_id'], $_POST["amount"], $_POST["payment_method"], $date, $_POST["description"], $transaction_id);
        $stmt->execute();

        $descriptions="Transaction Fee";
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $_POST['user_id'], $transaction_fee, $_POST["payment_method"], $date, $descriptions, $transaction_id);
        $stmt->execute();

        header('Location: user.php?id='. $_POST["user_id"].'&success=true');
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
<!DOCTYPE html>
<html>
<head>
    <title>Payment System</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f5f5f5;">
    <div style="width: 80%; max-width: 600px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center;">
        <h1 style="color: #333; text-align: center; margin-bottom: 30px;">Transaction Form</h1>
        
        <?php
        // Display success/error messages
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            echo '<div style="padding: 15px; margin: 15px 0; border-radius: 4px; text-align: center; background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6;">Transaction completed successfully!</div>';
        } elseif (isset($_GET['error'])) {
            echo '<div style="padding: 15px; margin: 15px 0; border-radius: 4px; text-align: center; background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1;">';
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo 'Please fill in all required fields.';
                    break;
                case 'db_error':
                    echo 'Database error occurred. Please try again.';
                    break;
                case 'invalid_payment_method':
                    echo 'Please select a valid payment method.';
                    break;
                case 'paypal_blocked':
                    echo 'Your PayPal account is blocked.';
                    break;
                default:
                    echo htmlspecialchars($_GET['error']);
            }
            echo '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: inline-block; text-align: left; width: 100%; max-width: 400px;">
            <div style="margin-bottom: 15px;">
                <label for="payment_method" style="font-weight: bold; display: block; margin-bottom: 5px;">Payment Method:</label>
                <select name="payment_method" id="payment_method"  style="width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Select a payment method</option>
                    <?php 
                    if ($result1->num_rows > 0) {
                        while($row = $result1->fetch_assoc()) { 
                    ?>
                    <option value="<?php echo htmlspecialchars($row["id"]); ?>"><?php echo htmlspecialchars($row["payment_method"]); ?></option>
                    <?php 
                        }
                    } 
                    ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="amount" style="font-weight: bold; display: block; margin-bottom: 5px;">Amount:</label>
                <input type="number" id="amount" name="amount" required style="width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="description" style="font-weight: bold; display: block; margin-bottom: 5px;">Description:</label>
                <input type="text" id="description" name="description" required style="width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            
            <input type="hidden" name="user_id" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars((string)$_GET['user_id'], ENT_QUOTES, 'UTF-8') : ''; ?>">
            
            <button type="submit" style="background-color: #4CAF50; color: white; padding: 12px 20px; margin: 10px 0; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px;">Submit Payment</button>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
