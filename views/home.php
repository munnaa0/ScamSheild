<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

$total_reports = 0;
$total_query = "SELECT COUNT(*) as total FROM scam_reports";
$total_result = mysqli_query($conn, $total_query);
if ($total_result) {
    $total_row = mysqli_fetch_assoc($total_result);
    $total_reports = $total_row['total'];
}

$verified_reports = 0;
$verified_query = "SELECT COUNT(*) as verified FROM scam_reports WHERE status = 'verified'";
$verified_result = mysqli_query($conn, $verified_query);
if ($verified_result) {
    $verified_row = mysqli_fetch_assoc($verified_result);
    $verified_reports = $verified_row['verified'];
}

$protected_users = 0;
$users_query = "SELECT COUNT(DISTINCT reporter_user_id) as users FROM scam_reports WHERE reporter_user_id IS NOT NULL";
$users_result = mysqli_query($conn, $users_query);
if ($users_result) {
    $users_row = mysqli_fetch_assoc($users_result);
    $protected_users = $users_row['users'];
}

$anon_query = "SELECT COUNT(*) as anon FROM scam_reports WHERE reporter_user_id IS NULL";
$anon_result = mysqli_query($conn, $anon_query);
if ($anon_result) {
    $anon_row = mysqli_fetch_assoc($anon_result);
    $protected_users += $anon_row['anon'];
}

$recent_query = "SELECT sr.*, sc.category_name 
                 FROM scam_reports sr 
                 JOIN scam_categories sc ON sr.category_id = sc.category_id 
                 ORDER BY sr.created_at DESC 
                 LIMIT 6";
$recent_result = mysqli_query($conn, $recent_query);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ScamShield - Fraud Reporting & Awareness Hub</title>
    <link rel="stylesheet" href="../models/css/home.css" />
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
            <li><a href="home.php" class="active">Home</a></li>
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
      <!-- Hero Section -->
      <section class="hero">
        <div class="hero-container">
          <div class="hero-content">
            <h2>Fight Fraud Together</h2>
            <p>
              Your trusted platform for reporting scams, staying informed, and
              protecting your community from fraudulent activities.
            </p>
            <div class="hero-buttons">
              <a href="../controllers/report.php" class="btn btn-primary"
                >Report a Scam Now</a
              >
              <a href="awareness.php" class="btn btn-secondary"
                >Learn Prevention Tips</a
              >
            </div>
          </div>
          <div class="hero-image">
            <img src="../images/logo.jpg" alt="ScamShield Protection" />
          </div>
        </div>
      </section>

      <!-- Features Section -->
      <section class="features">
        <div class="container">
          <h2>How ScamShield Protects You</h2>
          <div class="features-grid">
            <div class="feature-card">
              <div class="feature-icon">🛡️</div>
              <h3>Report Scams</h3>
              <p>
                Quickly report fraudulent activities with detailed information
                including images and links. Help others avoid falling victim to
                the same scams.
              </p>
              <a href="../controllers/report.php" class="feature-link">Start Reporting →</a>
            </div>
            <div class="feature-card">
              <div class="feature-icon">🔍</div>
              <h3>Verified Database</h3>
              <p>
                Browse our comprehensive database of verified scam reports.
                Search by category, keywords, or date to stay informed about
                current threats.
              </p>
              <a href="database.php" class="feature-link"
                >Explore Database →</a
              >
            </div>
            <div class="feature-card">
              <div class="feature-icon">📚</div>
              <h3>Stay Aware</h3>
              <p>
                Access the latest scam prevention tips, awareness articles, and
                news updates. Knowledge is your best defense against fraud.
              </p>
              <a href="awareness.php" class="feature-link">Learn More →</a>
            </div>
          </div>
        </div>
      </section>

      <!-- Stats Section -->
      <section class="stats">
        <div class="container">
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-number"><?php echo number_format($total_reports); ?></div>
              <div class="stat-label">Scams Reported</div>
            </div>
            <div class="stat-item">
              <div class="stat-number"><?php echo number_format($verified_reports); ?></div>
              <div class="stat-label">Verified Reports</div>
            </div>
            <div class="stat-item">
              <div class="stat-number"><?php echo number_format($protected_users); ?></div>
              <div class="stat-label">Protected Users</div>
            </div>
            <div class="stat-item">
              <div class="stat-number">24/7</div>
              <div class="stat-label">Monitoring</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Recent Reports Section -->
      <section class="recent-reports">
        <div class="container">
          <div class="section-header">
            <h2>Recent Scam Reports</h2>
            <p>Stay informed about the latest reported scams in our community</p>
          </div>
          
          <?php if ($recent_result && mysqli_num_rows($recent_result) > 0): ?>
          <div class="reports-grid">
            <?php while ($report = mysqli_fetch_assoc($recent_result)): ?>
            <div class="report-card">
              <div class="report-header">
                <span class="report-category"><?php echo htmlspecialchars($report['category_name']); ?></span>
                <span class="report-status status-<?php echo htmlspecialchars($report['status']); ?>">
                  <?php echo ucfirst(htmlspecialchars($report['status'])); ?>
                </span>
              </div>
              <h3 class="report-title"><?php echo htmlspecialchars($report['scam_title']); ?></h3>
              <p class="report-description">
                <?php 
                $desc = htmlspecialchars($report['scam_description']);
                echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc; 
                ?>
              </p>
              <div class="report-footer">
                <span class="report-date">
                  📅 <?php echo date('M d, Y', strtotime($report['created_at'])); ?>
                </span>
                <?php if ($report['amount_lost'] > 0): ?>
                <span class="report-amount">
                  💰 ৳<?php echo number_format($report['amount_lost']); ?>
                </span>
                <?php endif; ?>
                <a href="report_detail.php?id=<?php echo $report['report_id']; ?>" 
                   style="display: inline-block; padding: 5px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; font-size: 13px; margin-left: 10px;">
                  Details
                </a>
              </div>
            </div>
            <?php endwhile; ?>
          </div>
          
          <div class="view-all-container">
            <a href="database.php" class="btn btn-view-all">
              View All Reports
              <span class="arrow">→</span>
            </a>
          </div>
          
          <?php else: ?>
          <div class="no-reports">
            <p>No reports available at the moment. Be the first to report a scam!</p>
            <a href="../controllers/report.php" class="btn btn-primary">Report Now</a>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="cta">
        <div class="container">
          <div class="cta-content">
            <h2>Join the Fight Against Fraud</h2>
            <p>
              Be part of our community working to make the digital world safer
              for everyone.
            </p>
            <div class="cta-buttons">
              <a href="../controllers/report.php" class="btn btn-primary">Report a Scam</a>
              <a href="awareness.php" class="btn btn-outline">Learn Prevention Tips</a>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <h3>ScamShield</h3>
            <p>
              Protecting communities from fraud through awareness and
              collaboration.
            </p>
          </div>
          <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="about.php">About</a></li>
              <li><a href="../controllers/report.php">Report Scam</a></li>
              <li><a href="awareness.php">Prevention Tips</a></li>
              <li><a href="../controllers/contact.php">Contact</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Resources</h4>
            <ul>
              <li><a href="database.php">Scam Database</a></li>
              <li><a href="awareness.php">Awareness Center</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms of Service</a></li>
            </ul>
          </div>
          <div class="footer-section">
            <h4>Contact Info</h4>
            <p>Email: Meownna@scamshield.com</p>
            <p>Phone:01862122416</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 ScamShield. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </body>
</html>
