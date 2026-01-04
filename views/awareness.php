<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_post'])) {
        if (is_logged_in()) {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $category = trim($_POST['category']);
            $user_id = $_SESSION['user_id'];
            
            if (!empty($title) && !empty($content)) {
                $query = "INSERT INTO blog_posts (user_id, title, content, category) 
                          VALUES ('$user_id', '$title', '$content', '$category')";
                
                $conn->query($query);
                header("Location: awareness.php");
                exit();
            }
        }
    }
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
          ORDER BY blog_posts.created_at DESC";

$posts_result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Awareness & Prevention - ScamShield</title>
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
      <!-- Hero Section -->
      <section class="hero">
        <div class="container">
          <div class="hero-content">
            <h1>Community Awareness Hub</h1>
            <p>Share your knowledge and learn from others about scam prevention</p>
          </div>
        </div>
      </section>

      <!-- Create Post Section (Only for logged-in users) -->
      <?php if (is_logged_in()): ?>
      <section class="create-post">
        <div class="container">
          <div class="post-form-card">
            <h2>✍️ Share Your Knowledge</h2>
            <form method="POST" action="awareness.php">
              <div class="form-group">
                <input type="text" name="title" placeholder="Post Title" required />
              </div>
              <div class="form-group">
                <select name="category" required>
                  <option value="General">General</option>
                  <option value="Phishing">Phishing</option>
                  <option value="Investment Scams">Investment Scams</option>
                  <option value="Romance Scams">Romance Scams</option>
                  <option value="Tech Support">Tech Support</option>
                  <option value="Prevention Tips">Prevention Tips</option>
                </select>
              </div>
              <div class="form-group">
                <textarea name="content" rows="5" placeholder="Share your experience, tips, or advice..." required></textarea>
              </div>
              <button type="submit" name="create_post" class="submit-btn">Post</button>
            </form>
          </div>
        </div>
      </section>
      <?php else: ?>
      <section class="login-prompt">
        <div class="container">
          <div class="prompt-card">
            <p>🔒 <a href="../controllers/login.php">Login</a> or <a href="../controllers/register.php">Register</a> to share your knowledge and help others!</p>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <!-- Blog Posts Section -->
      <section class="blog-posts">
        <div class="container">
          <h2>Latest Posts from Our Community</h2>
          <div class="posts-grid">
            <?php if ($posts_result->num_rows > 0): ?>
              <?php while($post = $posts_result->fetch_assoc()): ?>
                <div class="post-card">
                  <div class="post-header">
                    <div class="author-info">
                      <div class="author-avatar">👤</div>
                      <div class="author-details">
                        <h4><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h4>
                        <span class="post-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                      </div>
                    </div>
                    <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                  </div>
                  <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                  <p><?php echo htmlspecialchars(substr($post['content'], 0, 150)) . '...'; ?></p>
                  <a href="blog_detail.php?id=<?php echo $post['post_id']; ?>" class="read-more">Read Full Post →</a>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="no-posts">
                <p>No posts yet. Be the first to share your knowledge!</p>
              </div>
            <?php endif; ?>
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
