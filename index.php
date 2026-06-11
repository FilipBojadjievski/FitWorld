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

    case 'my_gyms':
        // FIXED: Load database connections and fetch gyms before rendering the view
        require_once('./Model/database.php');
        require_once('./Model/gym_db.php'); // Or use gym_db.php depending on your exact filename
        
        $gyms = get_gyms_by_owner($pdo, $_SESSION['user_id']);
        include("./View/mygyms_page.php");
        break;

    case 'upload_gym_photo':
        if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
            die("Unauthorized access.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['gym_photo'])) {
            $gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
            $file = $_FILES['gym_photo'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                die("File upload error code: " . $file['error']);
            }

            if ($gym_id) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($file['type'], $allowed_types)) {
                    die("Invalid file type.");
                }
                
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'gym_' . $gym_id . '_' . time() . '.' . $ext;
                
                // FIXED: Using dynamic directory mapping relative to index.php
                $current_dir = dirname(__FILE__);
                $upload_dir = $current_dir . '/uploads/'; 

                // Ensure the path strings use clean slashes for the server environment
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
    require_once('./Model/database.php');
    require_once('./Model/gym_db.php');
    
    // 1. Fetch the exact unique filename currently registered in the database
    $old_filename = get_gym_photo_by_id($pdo, $gym_id);
    
    // 2. Try to delete it ONLY if it isn't empty
    if (!empty($old_filename)) {
        $old_file_path = $upload_dir . $old_filename; // Combines absolute path + filename
        
        if (file_exists($old_file_path)) {
            unlink($old_file_path); // Physically deletes the old unique file
        }
    }
    
    // 3. Finally, overwrite the DB record with the brand new unique filename
    update_gym_photo($pdo, $gym_id, $new_filename);
    
    header("Location: .?action=my_gyms");
    exit;
} else {
                    // Let's print out exactly what XAMPP thinks the path is internally
                    die("XAMPP Internal Path Error. Tried moving to: " . htmlspecialchars($upload_dir . $new_filename));
                }
            }
        }
        die("Invalid request.");
}
?>