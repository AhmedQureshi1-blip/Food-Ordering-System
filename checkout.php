<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:home.php');
   exit;
}

$cart_messages = [];

// Set conversion rate for INR to PKR (this rate should be updated regularly)
$inr_to_pkr_rate = 3.3; // 1 INR = 3.3 PKR (example rate)

// Fetch user profile
$select_profile = $conn->prepare("SELECT name, number, email, address FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
if(!$fetch_profile){
   $cart_messages[] = 'User profile not found!';
   header('location:login.php');
   exit;
}

if(isset($_POST['submit'])){
   $name = htmlspecialchars(strip_tags($_POST['name']));
   $number = htmlspecialchars(strip_tags($_POST['number']));
   $email = htmlspecialchars(strip_tags($_POST['email']));
   $method = htmlspecialchars(strip_tags($_POST['method']));
   $address = htmlspecialchars(strip_tags($_POST['address']));
   $total_products = $_POST['total_products'];
   $total_price = filter_var($_POST['total_price'], FILTER_VALIDATE_FLOAT);
   $placed_on = date('Y-m-d H:i:s');

   // Validate inputs against schema constraints
   if(!$name || strlen($name) > 20){
      $cart_messages[] = 'Invalid name (max 20 characters)!';
   }elseif(!$number || strlen($number) > 10){
      $cart_messages[] = 'Invalid number (max 10 characters)!';
   }elseif(!$email || strlen($email) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL)){
      $cart_messages[] = 'Invalid email (max 50 characters)!';
   }elseif(!$method || strlen($method) > 50){
      $cart_messages[] = 'Invalid payment method!';
   }elseif(!$address || strlen($address) > 500){
      $cart_messages[] = 'Invalid address (max 500 characters)!';
   }elseif(!$total_products){
      $cart_messages[] = 'No products selected!';
   }elseif(!$total_price || $total_price <= 0){
      $cart_messages[] = 'Invalid total price!';
   }else{
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $check_cart->execute([$user_id]);

      if($check_cart->rowCount() > 0){
         if($address == ''){
            $cart_messages[] = 'Please add your address!';
         }else{
            // Format total_price to DECIMAL(10,2)
            $total_price = number_format($total_price, 2, '.', '');
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, payment_status, placed_on) VALUES(?,?,?,?,?,?,?,?,'pending',?)");
            $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $placed_on]);
            if($insert_order->rowCount() > 0){
               $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
               $delete_cart->execute([$user_id]);
               $cart_messages[] = 'Order placed successfully!';
               header('location:orders.php');
               exit;
            }else{
               $cart_messages[] = 'Failed to place order! SQL Error: ' . htmlspecialchars($conn->errorInfo()[2] ?? 'Unknown error');
            }
         }
      }else{
         $cart_messages[] = 'Your cart is empty!';
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Checkout</h3>
   <p><a href="home.php">Home</a> <span> / Checkout</span></p>
</div>

<?php
if (!empty($cart_messages) && is_array($cart_messages)) {
   foreach ($cart_messages as $msg) {
      echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
   }
}
?>

<section class="checkout">
   <h1 class="title">Order Summary</h1>
   <form action="" method="post">
      <div class="cart-items">
         <h3>Cart Items</h3>
         <?php
         $grand_total = 0;
         $cart_items = [];
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['name'] . ' (₨' . $fetch_cart['price'] * $inr_to_pkr_rate . ' x ' . $fetch_cart['quantity'] . ')';
               $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
         ?>
         <p><span class="name"><?= $fetch_cart['name']; ?></span><span class="price">₨<?= $fetch_cart['price'] * $inr_to_pkr_rate; ?> x <?= $fetch_cart['quantity']; ?></span></p>
         <?php
            }
            $grand_total += 25; // GST from cart.php
            $total_products = implode(', ', $cart_items);
         }else{
            echo '<p class="empty">Your cart is empty!</p>';
         }
         ?>
         <p class="grand-total"><span class="name">Grand total :</span><span class="price">₨<?= number_format($grand_total * $inr_to_pkr_rate, 2); ?></span></p>
         <a href="cart.php" class="btn">View cart</a>
      </div>

      <input type="hidden" name="total_products" value="<?= htmlspecialchars($total_products ?? ''); ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_profile['name'] ?? ''); ?>">
      <input type="hidden" name="number" value="<?= htmlspecialchars($fetch_profile['number'] ?? ''); ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($fetch_profile['email'] ?? ''); ?>">
      <input type="hidden" name="address" value="<?= htmlspecialchars($fetch_profile['address'] ?? ''); ?>">

      <div class="user-info">
         <h3>Your Info</h3>
         <p><i class="fas fa-user"></i><span><?= htmlspecialchars($fetch_profile['name'] ?? 'N/A'); ?></span></p>
         <p><i class="fas fa-phone"></i><span><?= htmlspecialchars($fetch_profile['number'] ?? 'N/A'); ?></span></p>
         <p><i class="fas fa-envelope"></i><span><?= htmlspecialchars($fetch_profile['email'] ?? 'N/A'); ?></span></p>
         <a href="update_profile.php" class="btn">Update info</a>
         <h3>Delivery Address</h3>
         <p><i class="fas fa-map-marker-alt"></i><span><?php if(empty($fetch_profile['address'])){echo 'Please enter your address';}else{echo htmlspecialchars($fetch_profile['address']);} ?></span></p>
         <a href="update_address.php" class="btn">Update address</a>
         <select name="method" class="box" required>
            <option value="" disabled selected>Select payment method</option>
            <option value="cash on delivery">Cash on delivery</option>
            <option value="credit card">Credit card</option>
         </select>
         <input type="submit" value="Place order" class="btn <?php if(empty($fetch_profile['address'])){echo 'disabled';} ?>" style="width:100%; background:var(--red); color:var(--white);" name="submit">
      </div>
   </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
