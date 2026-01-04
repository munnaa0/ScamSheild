<?php
session_start();
require_once '../models/config.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['firstName'];
    $last_name = $_POST['lastName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $location = $_POST['location'];
    
    if ($password != $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        $check_query = "SELECT * FROM users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Email already registered!";
        } else {
            $username = explode('@', $email)[0];
            $check_username = "SELECT * FROM users WHERE username = '$username'";
            $username_result = mysqli_query($conn, $check_username);
            
            if (mysqli_num_rows($username_result) > 0) {
                $username = $username . rand(100, 999);
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password_hash, first_name, last_name, phone, location) 
                           VALUES ('$username', '$email', '$password_hash', '$first_name', '$last_name', '$phone', '$location')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success_message = "Registration successful! Please login.";
                header("refresh:2;url=login.php");
            } else {
                $error_message = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - ScamShield</title>
    <link rel="stylesheet" href="../models/css/register.css" />
    <link rel="stylesheet" href="../models/css/common.css" />
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
            <li><a href="login.php" class="login-btn">Login</a></li>
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
      <div class="register-container">
        <div class="register-card">
          <div class="register-header">
            <h2>Join ScamShield</h2>
            <p>Create your account to help fight fraud</p>
          </div>

          <?php if ($error_message): ?>
            <div class="error-message" style="background-color: #fee; color: #c00; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #c00;">
              <?php echo $error_message; ?>
            </div>
          <?php endif; ?>

          <?php if ($success_message): ?>
            <div class="success-message" style="background-color: #efe; color: #060; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #060;">
              <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <form class="register-form" action="register.php" method="POST">
            <div class="form-row">
              <div class="form-group">
                <label for="firstName">First Name</label>
                <input
                  type="text"
                  id="firstName"
                  name="firstName"
                  placeholder="Enter your first name"
                  required
                />
              </div>

              <div class="form-group">
                <label for="lastName">Last Name</label>
                <input
                  type="text"
                  id="lastName"
                  name="lastName"
                  placeholder="Enter your last name"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email Address</label>
              <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email address"
                required
              />
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="Enter your phone number"
                required
              />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="password">Password</label>
                <input
                  type="password"
                  id="password"
                  name="password"
                  placeholder="Create a password"
                  required
                />
              </div>

              <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input
                  type="password"
                  id="confirmPassword"
                  name="confirmPassword"
                  placeholder="Confirm your password"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="location">Location (Optional)</label>
              <input
                type="text"
                id="location"
                name="location"
                placeholder="City, State/Province"
              />
            </div>

            <div class="form-options">
              <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required />
                <label for="terms">
                  I agree to the <a href="#" class="terms-link">Terms of Service</a> 
                  and <a href="#" class="terms-link">Privacy Policy</a>
                </label>
              </div>

              <div class="checkbox-group">
                <input type="checkbox" id="newsletter" name="newsletter" />
                <label for="newsletter">
                  Subscribe to fraud alerts and security updates
                </label>
              </div>
            </div>

            <button type="submit" class="register-btn-submit">Create Account</button>
          </form>

          <div class="register-footer">
            <p>
              Already have an account?
              <a href="login.php" class="login-link">Sign in here</a>
            </p>
          </div>
        </div>

        <div class="register-info">
          <div class="info-card">
            <h3>🛡️ Enhanced Protection</h3>
            <p>
              Join our community of fraud fighters and help protect others from 
              scams in your area.
            </p>
          </div>

          <div class="info-card">
            <h3>📊 Track Your Impact</h3>
            <p>
              Monitor your reported scams, see their verification status, and 
              track how you're helping the community.
            </p>
          </div>

          <div class="info-card">
            <h3>🚨 Real-time Alerts</h3>
            <p>
              Get instant notifications about new scams in your area and 
              personalized fraud prevention tips.
            </p>
          </div>

          <div class="info-card">
            <h3>🤝 Community Support</h3>
            <p>
              Connect with other users, share experiences, and learn from 
              collective fraud prevention knowledge.
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

      const password = document.getElementById("password");
      const confirmPassword = document.getElementById("confirmPassword");

      function validatePassword() {
        if (password.value !== confirmPassword.value) {
          confirmPassword.setCustomValidity("Passwords don't match");
        } else {
          confirmPassword.setCustomValidity("");
        }
      }

      password.addEventListener("change", validatePassword);
      confirmPassword.addEventListener("keyup", validatePassword);

      document.querySelector(".register-form").addEventListener("submit", (e) => {
        const termsCheckbox = document.getElementById("terms");
        if (!termsCheckbox.checked) {
          e.preventDefault();
          
          let errorDiv = document.querySelector(".terms-error");
          if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.className = "error-message terms-error";
            errorDiv.textContent = "Please agree to the Terms of Service and Privacy Policy to continue.";
            termsCheckbox.closest(".checkbox-group").after(errorDiv);
          }
          
          termsCheckbox.scrollIntoView({ behavior: "smooth", block: "center" });
          termsCheckbox.focus();
          return;
        }
        
        const existingError = document.querySelector(".terms-error");
        if (existingError) {
          existingError.remove();
        }
        
        validatePassword();
      });
    </script>
  </body>
</html>