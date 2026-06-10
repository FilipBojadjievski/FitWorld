<?php

if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 600;
    session_set_cookie_params($lifetime, '/');
    session_start(); 
}

$isAdmin = ($_SESSION['is_admin'] ?? 0) === 1;
$username = $_SESSION['username'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if (!empty($_SESSION['success_message'])): ?>
    <div class="msg success-msg">
        <?php 
            echo htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']); 
        ?>
    </div>
<?php endif; ?>
<?php
$action = filter_input(INPUT_POST, 'action');
if ($action === NULL) {
    $action = filter_input(INPUT_GET, 'action');
    if ($action === NULL) {
        $action = 'show_home';
    }
}
switch ($action) {
    case 'show_home':
        include('./View/home.php');
        break;
    case 'signup':
        include('./View/signup_page.php');
        break;
    case 'login':
        include('./View/login_page.php');
        break;
    case 'logout':

        header("Location: ./Model/logout.php");
        break;
    case 'empty_cart':
        unset($_SESSION['cart']);
        include('cart_view.php');
        break;
}
?>