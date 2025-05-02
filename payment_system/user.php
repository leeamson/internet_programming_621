
<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
    $user_id = $_GET["id"];
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

    $sql = "SELECT * FROM account WHERE user_id = ". $user_id;
    // print_r($sql);
    $result = $conn->query($sql);
    
    //$sql = "SELECT t.amount,t.date_created,t.descriptions,p.payment_method  FROM transactions t JOIN payment_type p ON p.id=t.payment_method WHERE user_id = ". $user_id. "ORDER_BY t.id desc";  
    // print_r($sql);

    $sql = "SELECT t.amount, t.date_created, t.descriptions, p.payment_method, t.transaction_id 
    FROM transactions t 
    JOIN payment_type p ON p.id = t.payment_method 
    WHERE user = " . $user_id . " ORDER BY t.id DESC";  

    $result1 = $conn->query($sql);  
    $sql = "SELECT * FROM user WHERE id = ". $user_id;
    
    $result2 = $conn->query($sql);
    if ($result2->num_rows > 0) {
        while($row = $result2->fetch_assoc()) {
            $user_name = $row["f_name"].' '. $row["s_name"];
        
        }
        
    }
    

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
if (isset($_GET['success']) && $_GET['success'] == 'true') {
            echo '<div style="padding: 15px; margin: 15px 0; border-radius: 4px; text-align: center; background-color: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6;">Payment Successful!</div>';
        } 
        elseif (isset($_GET['refund']) && $_GET['refund'] == 'true') {
            echo '<div style="padding: 15px; margin: 15px 0; border-radius: 4px; text-align: center; background-color: #f2dede; color:rgb(152, 212, 249); border: 1px solid #ebccd1;">Refund issued. amount added back to balance!</div>';
        }
        elseif (isset($_GET['error'])) {
            $errorStyle = 'padding: 10px; margin: 10px 0; border-radius: 4px; text-align: center; background-color: #f2dede; color: #a94442;';
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
<div style="text-align: center;">
    <h1 style="margin-bottom: 20px;">Account For <?php echo $user_name ?></h1>
    <a href="http://localhost/websites/payment_system/account.php?user_id=<?php echo $user_id?>" style="display: inline-block; margin-bottom: 20px; padding: 8px 16px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Fund account</a>
    <a href="http://localhost/websites/payment_system/transact.php?user_id=<?php echo $user_id?>" style="display: inline-block; margin-bottom: 20px; padding: 8px 16px; background-color:rgb(235, 170, 66); color: white; text-decoration: none; border-radius: 4px;">Transact</a>
    <a href="http://localhost/websites/payment_system/refund.php?user_id=<?php echo $user_id?>" style="display: inline-block; margin-bottom: 20px; padding: 8px 16px; background-color:rgb(241, 138, 97); color: white; text-decoration: none; border-radius: 4px;">Refund</a>
    <table style="border-collapse: collapse; margin-left: auto; margin-right: auto; max-width: 800px; width: 100%;">
        <tr>
            <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">PayPal</th>
            <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Cryptocurrency</th>
            <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Credit Card</th>
        </tr>
        <?php
        if ($result->num_rows > 0) { 
            while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td style="border: 2px solid black; padding: 8px; text-align: center;">R <?php echo htmlspecialchars(number_format($row["PayPal"],2)) ?></td>
                    <td style="border: 2px solid black; padding: 8px; text-align: center;">R <?php echo htmlspecialchars(number_format($row["Cryptocurrency"],2)) ?></td>
                    <td style="border: 2px solid black; padding: 8px; text-align: center;">R <?php echo htmlspecialchars(number_format($row["CreditCard"],2)) ?></td>
                </tr>
            <?php }
        } else { ?>
            <tr>
                <td style="text-align: center; padding: 20px; border: 2px solid black;" colspan="3">There is no data on the database!!</td>
            </tr>
        <?php } ?>
    </table>
</div>


<h3 style="text-align: center;">Transaction History</h3>
    <!-- Center the tables-->
    <div style="width: 100%; text-align: center;">
    <div style="display: inline-block; max-width: 800px; width: 100%;">
        <table style="border-collapse: collapse; margin-left: auto; margin-right: auto;">
            <tr>
                <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Amount</th>
                <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Payment Type</th>
                <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Description</th>
                <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Date</th>
                <th style="background-color: #f0f0f0; font-weight: bold; padding: 12px; border: 2px solid black; text-align: center;">Transaction ID</th>
            </tr>
            <?php
            if ($result1->num_rows > 0) {
                while($row = $result1->fetch_assoc()) { ?>
                    <tr>
                        <td style="border: 2px solid black; padding: 8px; text-align: center;">R <?php echo htmlspecialchars(number_format($row["amount"],2)) ?></td>
                        <td style="border: 2px solid black; padding: 8px; text-align: center;"><?php echo htmlspecialchars($row["payment_method"]) ?></td>
                        <td style="border: 2px solid black; padding: 8px; text-align: center;"><?php echo htmlspecialchars($row["descriptions"]) ?></td>
                        <td style="border: 2px solid black; padding: 8px; text-align: center;"><?php echo htmlspecialchars($row["date_created"]) ?></td>
                        <td style="border: 2px solid black; padding: 8px; text-align: center;"><?php echo htmlspecialchars($row["transaction_id"]) ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px; border: 2px solid black;">There is no data on the database!!</td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

    

    <!--End of table center-->

<table style="border-collapse: collapse;">
    <tr>
        <th>Amount</th>
        <th>Payment Type</th>
        <th>Description</th>
        <th>Date</th>
    </tr>
    <?php
    if ($result1->num_rows > 0) {
      while($row = $result1->fetch_assoc()) {?>
                <tr>
                    <!-- <td><?php echo $row["amount"] ?></td> 
                    <td><?php echo $row["payment_method"] ?></td> 
                    <td><?php echo $row["descriptions"] ?></td> 
                    <td><?php echo $row["date_created"] ?></td>  -->


                    <td style="border: 2px solid black; padding: 8px;"><?php echo htmlspecialchars($row["amount"]) ?></td>
                     <td style="border: 2px solid black; padding: 8px;"><?php echo htmlspecialchars($row["payment_method"]) ?></td>
                    <td style="border: 2px solid black; padding: 8px;"><?php echo htmlspecialchars($row["descriptions"]) ?></td>
                    <td style="border: 2px solid black; padding: 8px;"><?php echo htmlspecialchars($row["date_created"]) ?></td>
                    
                    
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
</body>
</html>
