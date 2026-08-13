<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 600;
    session_set_cookie_params($lifetime, '/');
    session_start(); 
}

$isAdmin = ($_SESSION['is_admin'] ?? 0) === 1;
$username = $_SESSION['username'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

if (!empty($_SESSION['success_message'])):
    $successMessageTimeout = (int) ($_SESSION['success_message_timeout'] ?? 0);
    unset($_SESSION['success_message_timeout']);
?>
    <div class="msg success-msg"<?= $successMessageTimeout > 0 ? ' data-auto-dismiss="' . $successMessageTimeout . '"' : '' ?>>
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

$secure_actions = ['signup', 'login', 'register_new_gym', 'upload_gym_photo', 'my_reservations'];
if (in_array($action, $secure_actions)) {
    require_once('./Util/secure_conn.php');
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

 
    case 'my_gyms':
    case 'register_new_gym':
    case 'edit_gym_form':
    case 'update_gym':
    case 'add_event_form':
    case 'delete_event':
    case 'update_event':
        if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
            $_SESSION['error_message'] = "Access denied. Administrator privileges are required.";
            header("Location: .?action=login");
            exit();
        }
        
        if ($action === 'my_gyms') {
            require_once('./Model/database.php');
            require_once('./Model/gym_db.php'); 
            $gyms = get_gyms_by_owner($pdo, $_SESSION['user_id']);
            include("./View/mygyms_page.php");
        } elseif ($action === 'register_new_gym') {
            include('./View/register_gym.php');
        } elseif ($action === 'edit_gym_form') {
            include('./View/edit_gym_form.php');
        } elseif ($action === 'update_gym') {
            include('./Model/process_gym_update.php');
        } elseif ($action === 'add_event_form') {
            include('./View/add_event_form.php');
        } elseif ($action === 'edit_event_form') {
            include('./View/edit_event_form.php');
        } elseif ($action === 'update_event') {
            include('./Model/process_event_update.php');
        } else {
            include('./Model/process_event_deletion.php');
        }
        break;

  
    case 'my_reservations':
    case 'reserve_spot':
    case 'cancel_reservation':
    case 'reserve_general_training':
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Please log in to manage your session bookings.";
            header("Location: .?action=login");
            exit();
        }

        if ($action === 'my_reservations') {
            include('./Model/get_reservations.php');   
        } elseif ($action === 'reserve_spot') {
            include('./Model/process_reservation.php');
        } elseif ($action === 'reserve_general_training') {
            include('./Model/process_general_training.php');
        } else {
            include('./Model/process_cancellation.php');
        }
        break;
    case 'submit_gym_comment':
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Please log in to share a review.";
        header("Location: .?action=login");
        exit();
    }
    include('./Model/process_gym_comment.php');
    break;
    case 'delete_gym_comment':
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Unauthorized access.";
        header("Location: .?action=login");
        exit();
    }
    include('./Model/delete_gym_comment.php');
    break;

    case 'view_gym':
        include('./View/view_gym.php');
        break;

    case 'show_gyms':
        include('./Model/get_available_gyms.php');
        break;
    case 'contact_gym':
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = "Please log in before contacting a gym.";
        header("Location: .?action=login");
        exit();
    }

    include('./Model/contact_gym.php');
    break;

    case 'upload_gym_photo':
    if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
        $_SESSION['error_message'] = "Unauthorized access.";
        header("Location: .?action=login");
        exit();
    }

    include('./Model/process_gym_photo_upload.php');
    break;
}
?>
