<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
} else {
    $post_id = 0;
}

$query = "SELECT 
            blog_posts.post_id,
            blog_posts.title,
            blog_posts.content,
            blog_posts.category,
            blog_posts.created_at,
            users.username,
            users.first_name,
            users.last_name
          FROM blog_posts
          JOIN users ON blog_posts.user_id = users.user_id
          WHERE blog_posts.post_id = '$post_id'";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
} else {
    header("Location: awareness.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($post['title']); ?> - ScamShield</title>
    <link rel="stylesheet" href="../models/css/awareness.css" />
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
            <li><a href="awareness.php" class="active">Awareness</a></li>
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
      <!-- Blog Detail Section -->
      <section class="blog-detail">
        <div class="container">
          <a href="awareness.php" class="back-link">← Back to All Posts</a>
          
          <article class="detail-card">
            <div class="detail-header">
              <span class="detail-category"><?php echo htmlspecialchars($post['category']); ?></span>
              <h1><?php echo htmlspecialchars($post['title']); ?></h1>
              <div class="author-info">
                <div class="author-avatar">👤</div>
                <div class="author-details">
                  <h4><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
                  <!-- Format: January 15, 2025 at 2:30 PM -->
                  <span class="post-date"><?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?></span>
                </div>
              </div>
            </div>
            
            <div class="detail-content">
              <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
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
