<?php
session_start();
require_once '../models/config.php';

$error_message = "";

if (isset($_SESSION['user_id'])) {
    header("Location: ../views/home.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];
            
            $update_login = "UPDATE users SET last_login = NOW() WHERE user_id = " . $user['user_id'];
            mysqli_query($conn, $update_login);
            
            if ($user['role'] == 'admin') {
                header("Location: ../views/admin/admin_dashboard.php");
                exit();
            } elseif ($user['role'] == 'moderator') {
                header("Location: ../views/moderator/moderator_dashboard.php");
                exit();
            } else {
                header("Location: ../views/home.php");
                exit();
            }
        } else {
            $error_message = "Invalid email or password!";
        }
    } else {
        $error_message = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - ScamShield</title>
    <link rel="stylesheet" href="../models/css/login.css" />
  </head>
  <body>
    <header>
      <nav class="navbar">
        <div class="nav-container">
          <div class="logo">
            <h1>ScamShield</h1>
            <span class="tagline">Protecting You from Fraud</span>
          </div>
          <ul class="nav-menu">
            <li><a href="../views/home.php">Home</a></li>
            <li><a href="../views/about.php">About</a></li>
            <li><a href="report.php">Report Scam</a></li>
            <li><a href="../views/database.php">Scam Database</a></li>
            <li><a href="../views/awareness.php">Awareness</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="register.php" class="register-btn">Register</a></li>
            <li><a href="../views/profile.php" class="profile-link">
              <div class="profile-icon">
                <div class="profile-circle">👤</div>
                <span class="profile-text">Guest</span>
              </div>
            </a></li>
          </ul>
          <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <div class="login-container">
        <div class="login-card">
          <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your ScamShield account</p>
          </div>

          <?php if ($error_message): ?>
            <div class="error-message" style="background-color: #fee; color: #c00; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #c00;">
              <?php echo $error_message; ?>
            </div>
          <?php endif; ?>

          <form class="login-form" action="login.php" method="POST">
            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                required
              />
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
              />
            </div>

            <div class="form-options">
              <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" />
                <label for="remember">Remember me</label>
              </div>
              <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn-submit">Sign In</button>
          </form>

          <div class="login-footer">
            <p>
              Don't have an account?
              <a href="register.php" class="signup-link">Sign up here</a>
            </p>
          </div>
        </div>

        <div class="login-info">
          <div class="info-card">
            <h3>🛡️ Secure Access</h3>
            <p>
              Your account helps us track and prevent fraud more effectively in
              your community.
            </p>
          </div>

          <div class="info-card">
            <h3>📊 Personal Dashboard</h3>
            <p>
              Access your reported scams, track their status, and get
              personalized fraud alerts.
            </p>
          </div>

          <div class="info-card">
            <h3>🚨 Priority Reporting</h3>
            <p>
              Logged-in users get faster response times and detailed feedback on
              their reports.
            </p>
          </div>
        </div>
      </div>
    </main>

    <script>
      const hamburger = document.querySelector(".hamburger");
      const navMenu = document.querySelector(".nav-menu");

      hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        navMenu.classList.toggle("active");
      });

      document.querySelectorAll(".nav-menu a").forEach((n) =>
        n.addEventListener("click", () => {
          hamburger.classList.remove("active");
          navMenu.classList.remove("active");
        })
      );
    </script>
  </body>
</html>
