<?php
include 'components/connect.php';
session_start();

// Initialize $message as an array
$message = [];

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
}else{
    $user_id = '';
}

if(isset($_POST['send'])){
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

    $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
    $select_message->execute([$name, $email, $number, $msg]);

    if($select_message->rowCount() > 0){
        $message[] = 'already sent message!';
    }else{
        $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
        if($insert_message->execute([$user_id, $name, $email, $number, $msg])){
            $message[] = 'sent message successfully!';
        }else{
            $message[] = 'failed to send message: ' . $conn->errorInfo()[2];
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
    <title>Contact</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>
<div class="heading">
    <h3>Contact Us</h3>
    <p><a href="home.php">Home</a> <span> / Contact</span></p>
</div>
<?php
// Check if $message is an array and not empty before using foreach
if (!empty($message) && is_array($message)) {
    foreach ($message as $msg) {
        echo '<div class="message">' . htmlspecialchars($msg) . '</div>';
    }
}
?>
<section class="contact">
    <div class="row">
        <div class="image">
            <img src="images/contact-img.svg" alt="">
        </div>
        <form action="" method="post">
            <h3>Give Us A Feedback!</h3>
            <input type="text" name="name" maxlength="50" class="box" placeholder="enter your name" required>
            <input type="number" name="number" min="0" max="9999999999" class="box" placeholder="enter your number" required maxlength="10">
            <input type="email" name="email" maxlength="50" class="box" placeholder="enter your email" required>
            <textarea name="msg" class="box" required placeholder="enter your message" maxlength="500" cols="30" rows="10"></textarea>
            <input type="submit" value="send message" name="send" class="btn">
        </form>
    </div>
</section>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>