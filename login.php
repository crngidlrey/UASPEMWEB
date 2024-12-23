<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="auth-container">
        <h2>Student Login</h2>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="notification error">
                    <span>' . $_SESSION['error'] . '</span>
                    <span class="close" onclick="this.parentElement.style.display=\'none\'">&times;</span>
                  </div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="notification success">
                    <span>' . $_SESSION['success'] . '</span>
                    <span class="close" onclick="this.parentElement.style.display=\'none\'">&times;</span>
                  </div>';
            unset($_SESSION['success']);
        }
        ?>
        <form action="proses.php" method="post">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label for="username">Username or NIM</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
        <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>

</html>