<?php

include('./View/header.php'); 

if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); 
        ?>
    </div>
    
<?php endif;$username = isset($_SESSION['login_input']) ? $_SESSION['login_input'] : ''; 
unset($_SESSION['login_input']);
?>
<div class="auth-container">
    <div class="auth-box">
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Log in to manage your gym or book your next session.</p>


        <form action="./Model/login.php" method="POST">
            <div class="form-group">
                <label for="username_or_email">Username or Email Address</label>
                <input type="text" id="username_or_email" name="username_or_email" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-block">Log In</button>
        </form>

        <p class="auth-footer">Don't have an account yet? <a href=".?action=signup">Sign up here</a></p>
    </div>
</div>

<?php 
include('./View/footer.php'); 
?>