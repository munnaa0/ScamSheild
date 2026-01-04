<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

if (isset($_GET['id'])) {
    $report_id = $_GET['id'];
} else {
    header("Location: database.php");
    exit();
}

$query = "SELECT sr.*, sc.category_name 
          FROM scam_reports sr 
          JOIN scam_categories sc ON sr.category_id = sc.category_id 
          WHERE sr.report_id = '$report_id'";

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $report = mysqli_fetch_assoc($result);
} else {
    header("Location: database.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($report['scam_title']); ?> - ScamShield</title>
    <link rel="stylesheet" href="../models/css/awareness.css" />
    <link rel="stylesheet" href="../models/css/report_detail.css" />
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
            <li><a href="database.php" class="active">Scam Database</a></li>
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
      <section class="blog-detail">
        <div class="container">
          <a href="database.php" class="back-link">← Back to Scam Database</a>
          
          <article class="detail-card">
            <div class="detail-header">
              <span class="detail-category"><?php echo htmlspecialchars($report['category_name']); ?></span>
              <h2><?php echo htmlspecialchars($report['scam_title']); ?></h2>
              <div class="detail-meta">
                <span class="detail-date">
                  <?php echo date('M j, Y', strtotime($report['created_at'])); ?>
                </span>
                <span class="status-badge status-<?php echo $report['status']; ?>">
                  <?php echo ucfirst($report['status']); ?>
                </span>
              </div>
            </div>
            
            <div class="detail-content">
              <p class="description-text"><?php echo nl2br(htmlspecialchars($report['scam_description'])); ?></p>
              
              <?php if (!empty($report['scammer_email']) || !empty($report['scammer_phone']) || !empty($report['scammer_website'])): ?>
              <div class="scammer-info-box">
                <strong>Scammer Info:</strong>
                <?php if (!empty($report['scammer_email'])): ?>
                <span class="info-item">Email: <?php echo htmlspecialchars($report['scammer_email']); ?></span>
                <?php endif; ?>
                <?php if (!empty($report['scammer_phone'])): ?>
                <span class="info-item">Phone: <?php echo htmlspecialchars($report['scammer_phone']); ?></span>
                <?php endif; ?>
                <?php if (!empty($report['scammer_website'])): ?>
                <span class="info-item">Website: <?php echo htmlspecialchars($report['scammer_website']); ?></span>
                <?php endif; ?>
              </div>
              <?php endif; ?>
              
              <?php if ($report['amount_lost'] > 0): ?>
              <div class="amount-box">
                <strong>Amount Lost:</strong> ৳<?php echo number_format($report['amount_lost'], 2); ?>
              </div>
              <?php endif; ?>
              
              <?php if (!empty($report['screenshot_url'])): ?>
              <div class="evidence-img">
                <img src="<?php echo htmlspecialchars($report['screenshot_url']); ?>" alt="Evidence">
              </div>
              <?php endif; ?>
            </div>
          </article>
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
