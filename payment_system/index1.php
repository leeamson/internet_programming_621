<?php

// database user
//transaction
//payment method
//request a refund
//transaction history
// account ()

// user management(add user, list users, view users)
// tranaction_management(list transaction, )
// payment_management(Credit Card, PayPal, Cryptocurrency, transaction_fee)
?>

<?php
// DELETE FROM `ticket_sales`; -> clear the DB entries
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
    // Validate inputs
    if (!isset($_POST['f_name']) || !isset($_POST['s_name']) ) {
        header('Location: index.php?error=missing_fields');
        exit;
    }

    $sql = "INSERT INTO user (f_name, s_name)
            VALUES (?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_POST['f_name'], $_POST['s_name']);
    
    if ($stmt->execute()) {
        header('Location: index.php?success=true');
        exit;
    } else {
        header('Location: index.php?error=db_error');
        exit;
    }
}

// Query data after handling POST
$sql = "SELECT * FROM user";
$result = $conn->query($sql);

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
.success { background-color: #dff0d8; color: #3c763d; }
.error { background-color: #f2dede; color: #a94442; }
</style>
</head>
<body>
<?php
// Display success/error messages
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    echo '<div class="message success">User Added Successfully!</div>';
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_fields':
            echo '<div class="message error">Please fill in all required fields.</div>';
            break;
        case 'db_error':
            echo '<div class="message error">Database error occurred. Please try again.</div>';
            break;
         case 'invalid_payment_method':
         echo '<div class="message error">Error: Invalid payment method.</div>';
            break;

         case 'paypal_blocked':
            echo '<div class="message error">Error: Payment rejected due to fraud detection.</div>';
            break;

        default:
            echo '<div class="message error">' . $_GET['error']. '</div>';

        
    }
}
?>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    
    First Name: <input type="text" name="f_name" placeholder="Enter your first name" required><br><br>
    Surname: <input type="text" name="s_name" placeholder="Enter your surname" required><br><br>
    <button type="submit">Submit</button>
</form>

<table>
    <tr>
        <th>First Name</th>
        <th>Surname</th>
        <th>Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {?>
                <tr>
                    <td><?php echo $row["f_name"] ?></td> 
                    <td><?php echo $row["s_name"] ?></td> 
                    <td><a href="http://localhost/websites/payment_system/user.php?id=<?php echo $row["id"] ?>">View Information</a></td>
                    
                </tr>
        <?php }
        
    } else { ?>
        <tr>
            <td colspan="3">There is no data on the database!!</td>
        </tr>
    <?php } ?>
</table>

<?php
$conn->close();
?>
