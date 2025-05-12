<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}else{
    $user_id = '';
}

include 'components/add_cart.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Food Menu</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<div class="heading">
   <h3>Our Menu</h3>
   <p><a href="home.php">Home</a> <span> / Menu</span></p>
</div>

<?php
if (!empty($cart_messages) && is_array($cart_messages)) {
    foreach ($cart_messages as $msg) {
        echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
    }
}
?>

<section class="products">
   <h1 class="title">Latest Dishes</h1>
   <div class="box-container">
      <?php
      $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" method="post" class="box">
         <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
         <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
         <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
         <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
         <a href="quick_view.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
         <button class="fas fa-shopping-cart" type="submit" name="add_to_cart"></button>
         <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
         <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
         <div class="name"><?= $fetch_products['name']; ?></div>
         <div class="flex">
            <div class="price"><span>₨</span><?= $fetch_products['price']; ?><span>/-</span></div>
            <input type="number" name="qty" class="qty" min="1" max="99" value="1" onkeypress="if(this.value.length == 2) return false;">
         </div>
      </form>
      <?php
         }
      }else{
         echo '<p class="empty">No products added yet!</p>';
      }
      ?>
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
         <a href="https://www.google.com/maps">Sindh, Karachi - 400104</a>
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
