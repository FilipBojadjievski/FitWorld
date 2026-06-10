<?php
include('./View/header.php'); 

if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); 
        ?>
    </div>
    
<?php endif; 
$username = isset($_SESSION['form_input']['username']) ? $_SESSION['form_input']['username'] : '';
$email    = isset($_SESSION['form_input']['email']) ? $_SESSION['form_input']['email'] : '';
$is_admin = isset($_SESSION['form_input']['is_admin']) ? $_SESSION['form_input']['is_admin'] : 0;
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Create an Account</h2>
        <p class="auth-subtitle">Join FitWorld and start booking your gym timeslots.</p>

        <form action="./Model/signup.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <input type="checkbox" id="is_admin" name="is_admin" <?php echo ($is_admin == 1) ? 'checked' : ''; ?>>
            <label for="terms">I wish to post my own gym</label>
            <button type="submit" class="btn btn-block">Sign Up</button>
        </form>

        <p class="auth-footer">Already have an account? <a href=".?action=login">Log In here</a></p>
    </div>
</div>

<?php 
// Include your standard footer layout
include('./View/footer.php'); 
?>