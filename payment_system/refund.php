
<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
    $user_id = $_GET["user_id"];
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

    // $sql = "SELECT * FROM account WHERE user_id = ". $user_id;
    // // print_r($sql);
    // $result = $conn->query($sql);
    
    //$sql = "SELECT t.amount,t.date_created,t.descriptions,p.payment_method  FROM transactions t JOIN payment_type p ON p.id=t.payment_method WHERE user_id = ". $user_id. "ORDER_BY t.id desc";  
    // print_r($sql);

    // $sql = "SELECT sum(t.amount) as t_amount, t.transaction_id, t.payment_method
    // FROM transactions t 
    // WHERE user = " . $user_id. "GROUP BY t.transaction_id, t.payment_method";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST["user_id"];
        if (!isset($_POST['transaction_id'])) {
            header('Location: index.php?error=missing_fields');
            exit;
        }
        $date = date("Y-m-d");  
        $description = "Refund of transaction_id ". $_POST['transaction_id'];
        
        $sql = "SELECT sum(t.amount) as t_amount, t.transaction_id, t.payment_method FROM transactions t WHERE t.transaction_id=" . $_POST['transaction_id'];
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc())
        
        {
            $amount = $row["t_amount"];
            $payment_method = $row["payment_method"];
        }}
        
        $sql = "SELECT * FROM account WHERE user_id=" . $user_id;
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc())
        
        {
            $paypal = $row["PayPal"];
            $creditcard = $row["CreditCard"];
            $crypto = $row["Cryptocurrency"];
           
        }}

        $transaction_id = date("YmdHis");

        $sql = "INSERT INTO transactions (user, amount, payment_method, date_created, descriptions, transaction_id)
            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiisss", $_POST['user_id'], $amount, $payment_method, $date, $description, $transaction_id);
             if($stmt->execute()){
                if($payment_method==1){

                    $paypal = $paypal + $amount;

                    $sql = "UPDATE account  SET PayPal = ?  WHERE user_id = ?";
        
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di",  $paypal, $_POST['user_id']);


                }
                if($payment_method==2){

                    $creditcard = $creditcard + $amount;

                    $sql = "UPDATE account  SET CreditCard = ?  WHERE user_id = ?";
        
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di",  $creditcard, $_POST['user_id']);

                }

                if($payment_method==5){

                    $crypto = $crypto + $amount;

                    $sql = "UPDATE account  SET Cryptocurrency = ?  WHERE user_id = ?";
        
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di",  $crypto, $_POST['user_id']);

                }
             }

             if ($stmt->execute()) {
        
                header('Location: user.php?id='.$_POST['user_id'].'&refund=true');
                exit;
            } else {
                header('Location: index.php?error=db_error');
                exit;
            }
    };
    $sql = "SELECT 
          sum(t.amount) as t_amount,
          t.transaction_id,
          t.payment_method
        FROM transactions t
        WHERE descriptions!='Deposit' AND user = " . $user_id . "
        GROUP BY t.transaction_id, t.payment_method";
   
    $result1 = $conn->query($sql);  

?>

<h1>Refund</h1>
<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
    Transaction: <select name="transaction_id" id="" required>
        <option value="">Select a transaction id</option>
        <?php if ($result1->num_rows > 0) {
      while($row = $result1->fetch_assoc()) {?>
      <option value="<?php echo $row["transaction_id"]?>"><?php echo "Amount (" . $row["t_amount"]. ") - " . $row["transaction_id"]  ;?></option>
        
        
        <?php }
    }?>
    </select><br> <br>
    
    <input type="hidden" name="user_id" value="<?php echo $_GET['user_id']?>" >

    <button type="submit">Submit</button>
</form>

<?php
$conn->close();
?>