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
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f5f5f5;">
    <div style="width: 80%; max-width: 800px; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="color: #333; margin-top: 0; text-align: center;">User Management System</h2>

        
        <?php
        // Display success/error messages
        if (isset($_GET['success']) && $_GET['success'] == 'true') {
            echo '<div style="padding: 10px; margin: 10px 0; border-radius: 4px; background-color: #dff0d8; color: #3c763d;">User Added Successfully!</div>';
        } elseif (isset($_GET['error'])) {
            $errorStyle = 'padding: 10px; margin: 10px 0; border-radius: 4px; background-color: #f2dede; color: #a94442;';
            switch ($_GET['error']) {
                case 'missing_fields':
                    echo '<div style="'.$errorStyle.'">Please fill in all required fields.</div>';
                    break;
                case 'db_error':
                    echo '<div style="'.$errorStyle.'">Database error occurred. Please try again.</div>';
                    break;
                case 'invalid_payment_method':
                    echo '<div style="'.$errorStyle.'">Error: Invalid payment method.</div>';
                    break;
                case 'paypal_blocked':
                    echo '<div style="'.$errorStyle.'">Error: Payment rejected due to fraud detection.</div>';
                    break;
                default:
                    echo '<div style="'.$errorStyle.'">' . $_GET['error']. '</div>';
            }
        }
        ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="margin: 20px 0;">
    <div style="display: flex; margin-bottom: 15px;">
        <div style="flex: 1; margin-right: 10px;">
            <label for="f_name" style="display: block; margin-bottom: 5px;">First Name:</label>
            <input type="text" id="f_name" name="f_name" placeholder="Enter your first name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="flex: 1;">
            <label for="s_name" style="display: block; margin-bottom: 5px;">Surname:</label>
            <input type="text" id="s_name" name="s_name" placeholder="Enter your surname" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
        </div>
    </div>
    
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer;">Submit</button>
</form>


        <h3 style="margin-top: 30px; color: #333;">User List</h3>
        <table style="border: 1px solid #ddd; border-collapse: collapse; width: 100%; margin-top: 20px;">
            <tr>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left; background-color: #f2f2f2;">First Name</th>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left; background-color: #f2f2f2;">Surname</th>
                <th style="border: 1px solid #ddd; padding: 12px; text-align: left; background-color: #f2f2f2;">Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
            ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 12px; text-align: left;"><?php echo htmlspecialchars($row["f_name"]); ?></td>
                    <td style="border: 1px solid #ddd; padding: 12px; text-align: left;"><?php echo htmlspecialchars($row["s_name"]); ?></td>
                    <td style="border: 1px solid #ddd; padding: 12px; text-align: left;"><a href="http://localhost/websites/payment_system/user.php?id=<?php echo $row["id"]; ?>" style="color: #2196F3; text-decoration: none;">View Information</a></td>
                </tr>
            <?php 
                }
            } else { 
            ?>
                <tr>
                    <td colspan="3" style="border: 1px solid #ddd; padding: 12px; text-align: center;">There is no data in the database!</td>
                </tr>
            <?php 
            } 
            ?>
        </table>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
