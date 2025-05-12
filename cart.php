<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}else{
    $user_id = '';
    header('location:home.php');
    exit;
}

$cart_messages = []; // Unique variable to avoid conflicts

if(isset($_POST['delete'])){
    $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
    if($cart_id === false || $cart_id <= 0){
        $cart_messages[] = 'Invalid cart ID!';
    }else{
        $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
        $delete_cart_item->execute([$cart_id]);
        if($delete_cart_item->rowCount() > 0){
            $cart_messages[] = 'Cart item deleted!';
        }else{
            $cart_messages[] = 'No cart item found with ID: ' . $cart_id;
        }
    }
    header('location:cart.php');
    exit;
}

if(isset($_POST['delete_all'])){
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    $cart_messages[] = 'Deleted all from cart!';
    header('location:cart.php');
    exit;
}

if(isset($_POST['update_qty'])){
    $cart_id = filter_var($_POST['cart_id'], FILTER_VALIDATE_INT);
    $qty = filter_var($_POST['qty'], FILTER_VALIDATE_INT);
    if($cart_id === false || $cart_id <= 0){
        $cart_messages[] = 'Invalid cart ID!';
    }elseif($qty === false || $qty < 1 || $qty > 99){
        $cart_messages[] = 'Invalid quantity!';
    }else{
        $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
        $update_qty->execute([$qty, $cart_id]);
        if($update_qty->rowCount() > 0){
            $cart_messages[] = 'Cart quantity updated';
        }else{
            $cart_messages[] = 'No cart item found with ID: ' . $cart_id;
        }
    }
    header('location:cart.php');
    exit;
}

$grand_total = 0;
$gst = 25; // Fixed GST as per cart.html; adjust if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Cart</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<div class="heading">
   <h3>Shopping Cart</h3>
   <p><a href="home.php">Home</a> <span> / Cart</span></p>
</div>

<?php
if (!empty($cart_messages) && is_array($cart_messages)) {
    foreach ($cart_messages as $msg) {
        echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
    }
}
?>

<section class="products">
   <h1 class="title">Your Cart</h1>
   <div class="cart-total">
      <p>gst : <span>₨<?= $gst; ?>/-</span></p>
      <p>grand total : <span>₨<?= $grand_total + $gst; ?>/-</span></p>
      <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">checkout orders</a>
   </div>
   <div class="box-container">
      <?php
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
            $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
            $grand_total += $sub_total;
      ?>
      <form action="" method="post" class="box">
         <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
         <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
         <button type="submit" class="fas fa-times" name="delete" onclick="return confirm('delete this item?');"></button>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="name"><?= $fetch_cart['name']; ?></div>
         <div class="flex">
            <div class="price"><span>₨</span><?= $fetch_cart['price']; ?></div>
            <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
            <button type="submit" class="fas fa-edit" name="update_qty"></button>
         </div>
         <div class="sub-total">sub total : <span>₨<?= $sub_total; ?>/-</span></div>
      </form>
      <?php
         }
      }else{
         echo '<p class="empty">your cart is empty</p>';
      }
      ?>
   </div>
   <div class="more-btn">
      <form action="" method="post">
         <button type="submit" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" name="delete_all" onclick="return confirm('delete all from cart?');">delete all</button>
      </form>
   </div>
</section>

<footer class="footer">
   <section class="box-container">
      <div class="box">
         <img src="images/email-icon.png" alt="">
         <h3>Our Email</h3>
         <a href="mailto:info@yumyum.in">info@yumyum.in</a>
         <a href="mailto:support@yumyum.in">support@yumyum.in</a>
      </div>
      <div class="box">
         <img src="images/clock-icon.png" alt="">
         <h3>Opening Hours</h3>
         <p>07:00am to 10:00pm</p>
         <p>Our services are functional 24/7.</p>
      </div>
      <div class="box">
         <img src="images/map-icon.png" alt="">
         <h3>Our Address</h3>
         <a href="https://www.google.com/maps">Near Apex Mall, Karachi,</a>
         <a href="https://www.google.com/maps">Sindh,Karachi - 400104</a>
      </div>
      <div class="box">
         <img src="images/phone-icon.png" alt="">
         <h3>Our Number</h3>
         <a href="tel:1234567890">+123-456-7890</a>
         <a href="tel:1112223333">+111-222-3333</a>
      </div>
   </section>
   <div class="credit">© copyright @ 2024 by <span>yum-yum</span> | all rights reserved!</div>
</footer>

<div class="loader">
   <img src="images/loader.gif" alt="">
</div>

<script src="js/script.js"></script>
</body>
</html>
