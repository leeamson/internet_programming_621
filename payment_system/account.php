<?php
// DELETE FROM `ticket_sales`; -> clear the DB entries
error_reporting(E_ALL);
ini_set('display_errors', 'On');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "payment_system";

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
    } else {
        $paypal = 0;
        $credit_card = 0;
        $crypto = 0;
    }

    // Validate inputs
    if (!isset($_POST['PayPal']) || !isset($_POST['Cryptocurrency']) || !isset($_POST['CreditCard'])) {
        header('Location: index.php?error=missing_fields');
        exit;
    }

    $date = date("Y-m-d"); // Store date in a variable
    $description = "Deposit"; // Store description in a variable
    $transaction_id = date("YmdHis");

    if($_POST["PayPal"]>0){
        $payment_method = 1;
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $_POST['user_id'], $_POST["PayPal"], $payment_method, $date, $description, $transaction_id);
        $stmt->execute();
    }

    if($_POST["Cryptocurrency"]>0){
        $payment_method = 2;
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $_POST['user_id'], $_POST["Cryptocurrency"], $payment_method, $date, $description, $transaction_id);
        $stmt->execute();
    }

    if($_POST["CreditCard"]>0){
        $payment_method = 5;
        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiisss", $_POST['user_id'], $_POST["CreditCard"], $payment_method, $date, $description, $transaction_id);
        $stmt->execute();
    }

    $paypal = $paypal + $_POST["PayPal"];
    $credit_card = $credit_card + $_POST["CreditCard"];
    $crypto = $crypto + $_POST["Cryptocurrency"];

    if ($result->num_rows < 1) {
        $sql = "INSERT INTO account (user_id, PayPal, Cryptocurrency, CreditCard)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dddd", $_POST['user_id'], $paypal, $crypto, $credit_card);
    } else {
        $sql = "UPDATE account
                SET PayPal = ?,
                    Cryptocurrency = ?,
                    CreditCard = ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dddi", $paypal, $crypto, $credit_card, $_POST['user_id']);
        print_r($stmt);
        // exit;
    }

    if ($stmt->execute()) {
        header('Location: index.php?success=true');
        exit;
    } else {
        header('Location: index.php?error=db_error');
        exit;
    }
}

// Query data after handling POST
$sql = "SELECT * FROM payment_type";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund User Account</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh;">
    <div style="background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 30px; width: 100%; max-width: 600px; text-align: center;">
        <?php
        // Display success/error messages
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            echo '<div style="padding: 15px; margin: 20px 0; border-radius: 4px; text-align: center; background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6;">User Account Funded Successfully!</div>';
        } elseif (isset($_GET['error'])) {
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo '<div style="padding: 15px; margin: 20px 0; border-radius: 4px; text-align: center; background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1;">Please fill in all required fields.</div>';
                    break;
                case 'db_error':
                    echo '<div style="padding: 15px; margin: 20px 0; border-radius: 4px; text-align: center; background-color: #f2dede; color: #a94442; border: 1px solid #ebccd1;">Database error occurred. Please try again.</div>';
                    break;
            }
        }
        ?>
        
        <h1 style="color: #333; margin-bottom: 30px;">Fund User Account</h1>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <label for="<?php echo $row["payment_method"]?>" style="font-weight: bold;"><?php echo $row["payment_method"]?>:</label>
                    <input type="number" id="<?php echo $row["payment_method"]?>" name="<?php echo $row["payment_method"]?>" required style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                </div>
            <?php 
                }
            } else { 
            ?>
                <p style="text-align: center; color: #666;">No payment methods available</p>
            <?php 
            } 
            ?>
            
            <input type="hidden" name="user_id" value="<?php echo isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id']) : ''; ?>">
            <button type="submit" style="background-color: #4CAF50; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px;">Submit</button>
        </form>
    </div>
    
    <?php $conn->close(); ?>
</body>
</html>
