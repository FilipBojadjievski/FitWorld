<?php

require_once('./Model/database.php');
require_once('./Model/gym_db.php'); 

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    die("Unauthorized access.");
}

// 2. Capture and sanitize form inputs
$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$name = trim(filter_input(INPUT_POST, 'name'));
$address = trim(filter_input(INPUT_POST, 'address'));
$description = trim(filter_input(INPUT_POST, 'description'));
$opening_hour = filter_input(INPUT_POST, 'opening_hour');
$closing_hour = filter_input(INPUT_POST, 'closing_hour');

// 3. Validation fallback tracking check
if (empty($name) || empty($address) || empty($description) || empty($opening_hour) || empty($closing_hour) || !$gym_id) {
    $_SESSION['error_message'] = "All fields are required to update the facility information.";
    $_SESSION['old_input'] = $_POST; // Save input data so user doesn't have to retype it
    header("Location: .?action=edit_gym_form&gym_id=" . $gym_id);
    exit();
}

try {
    // 4. Update query execution
    $sql = "UPDATE gyms 
            SET name = ?, address = ?, description = ?, opening_hour = ?, closing_hour = ? 
            WHERE id = ? AND owner_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute([
        $name, 
        $address, 
        $description, 
        $opening_hour, 
        $closing_hour, 
        $gym_id, 
        $_SESSION['user_id'] // Ensures owners can only edit their own gyms
    ]);

    if ($success) {
        $_SESSION['success_message'] = "Gym information updated successfully!";
        // Redirect back to view the updated profile overview
        header("Location: .?action=view_gym&id=" . $gym_id);
        exit();
    } else {
        throw new Exception("Database update failed.");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Something went wrong saving updates: " . $e->getMessage();
    $_SESSION['old_input'] = $_POST;
    header("Location: .?action=edit_gym_form&gym_id=" . $gym_id);
    exit();
}