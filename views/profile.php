<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();
$user_data = null;

if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $user_data = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile - ScamShield</title>
    <link rel="stylesheet" href="../models/css/profile.css" />
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
            <li><a href="home.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="../controllers/report.php">Report Scam</a></li>
            <li><a href="database.php">Scam Database</a></li>
            <li><a href="awareness.php">Awareness</a></li>
            <li><a href="../controllers/contact.php">Contact</a></li>
            <?php if (is_logged_in()): ?>
              <li><a href="../controllers/logout.php" class="login-btn">Logout</a></li>
            <?php else: ?>
              <li><a href="../controllers/login.php" class="login-btn">Login</a></li>
            <?php endif; ?>
            <li><a href="profile.php" class="profile-link">
              <div class="profile-icon">
                <div class="profile-circle">👤</div>
                <span class="profile-text"><?php echo $display_name; ?></span>
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
      <div class="profile-container">
        <div class="profile-card">
          <div class="profile-header">
            <div class="profile-avatar">👤</div>
            <?php if (is_logged_in() && $user_data): ?>
              <h2><?php echo $user_data['first_name'] . ' ' . $user_data['last_name']; ?></h2>
              <p class="profile-subtitle"><?php echo $user_data['email']; ?></p>
            <?php else: ?>
              <h2>Guest User</h2>
              <p class="profile-subtitle">Welcome to ScamShield</p>
            <?php endif; ?>
          </div>

          <div class="profile-content">
            <?php if (is_logged_in() && $user_data): ?>
              <!-- Logged In User Info -->
              <div class="info-section">
                <h3>Profile Information</h3>
                <div class="info-item">
                  <span class="info-label">Username:</span>
                  <span class="info-value"><?php echo $user_data['username']; ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Email:</span>
                  <span class="info-value"><?php echo $user_data['email']; ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Phone:</span>
                  <span class="info-value"><?php echo $user_data['phone'] ? $user_data['phone'] : 'Not provided'; ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Location:</span>
                  <span class="info-value"><?php echo $user_data['location'] ? $user_data['location'] : 'Not provided'; ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Member Since:</span>
                  <span class="info-value"><?php echo date('F d, Y', strtotime($user_data['created_at'])); ?></span>
                </div>
                <div class="info-item">
                  <span class="info-label">Account Role:</span>
                  <span class="info-value"><?php echo ucfirst($user_data['role']); ?></span>
                </div>
              </div>

              <div class="action-section">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                  <?php if ($user_data['role'] === 'admin'): ?>
                    <a href="admin/admin_dashboard.php" class="btn btn-primary">Go to Admin Panel</a>
                  <?php elseif ($user_data['role'] === 'moderator'): ?>
                    <a href="moderator/moderator_dashboard.php" class="btn btn-primary">Go to Moderator Panel</a>
                  <?php else: ?>
                    <a href="../controllers/report.php" class="btn btn-primary">Report a Scam</a>
                  <?php endif; ?>
                  <a href="database.php" class="btn btn-secondary">View Scam Database</a>
                  <a href="../controllers/logout.php" class="btn btn-secondary">Logout</a>
                </div>
              </div>
            <?php else: ?>
              <!-- Guest User Info -->
              <div class="info-section">
                <h3>Profile Information</h3>
                <div class="info-item">
                  <span class="info-label">Status:</span>
                  <span class="info-value">Guest</span>
                </div>
                <div class="info-item">
                  <span class="info-label">Member Since:</span>
                  <span class="info-value">Not Logged In</span>
                </div>
              </div>

              <div class="action-section">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                  <a href="../controllers/login.php" class="btn btn-primary">Login to Your Account</a>
                  <a href="../controllers/register.php" class="btn btn-secondary">Create New Account</a>
                </div>
              </div>

              <div class="benefits-section">
                <h3>Member Benefits</h3>
                <ul class="benefits-list">
                  <li>✓ Track your scam reports</li>
                  <li>✓ Save searches and preferences</li>
                  <li>✓ Get personalized alerts</li>
                  <li>✓ Access advanced features</li>
                </ul>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>

    <script>
      const hamburger = document.querySelector(".hamburger");
      const navMenu = document.querySelector(".nav-menu");

      if (hamburger) {
        hamburger.addEventListener("click", () => {
          hamburger.classList.toggle("active");
          navMenu.classList.toggle("active");
        });
      }

      document.querySelectorAll(".nav-menu a").forEach((n) =>
        n.addEventListener("click", () => {
          hamburger.classList.remove("active");
          navMenu.classList.remove("active");
        })
      );
    </script>
  </body>
</html>
