<?php
require_once '../models/session_helper.php';
require_once '../models/config.php';

$display_name = get_user_display_name();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

$sql = "SELECT sr.*, sc.category_name 
        FROM scam_reports sr 
        JOIN scam_categories sc ON sr.category_id = sc.category_id 
        WHERE 1=1";

if ($search != '') {
    $sql .= " AND (sr.scam_title LIKE '%$search%' 
                   OR sr.scam_description LIKE '%$search%' 
                   OR sr.scammer_email LIKE '%$search%' 
                   OR sr.scammer_phone LIKE '%$search%' 
                   OR sr.scammer_website LIKE '%$search%')";
}

if ($category_filter != '') {
    $sql .= " AND sc.category_slug = '$category_filter'";
}

if ($status_filter != '') {
    $sql .= " AND sr.status = '$status_filter'";
}

if ($date_filter == 'today') {
    $sql .= " AND DATE(sr.created_at) = CURDATE()";
} elseif ($date_filter == 'week') {
    $sql .= " AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
} elseif ($date_filter == 'month') {
    $sql .= " AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($date_filter == 'quarter') {
    $sql .= " AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
} elseif ($date_filter == 'year') {
    $sql .= " AND sr.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}

$sql .= " ORDER BY sr.created_at DESC";

$count_result = mysqli_query($conn, $sql);
$total_reports = mysqli_num_rows($count_result);
$total_pages = ceil($total_reports / 5);

$start = ($page - 1) * 5;
$sql .= " LIMIT $start, 5";
$result = mysqli_query($conn, $sql);
$stats_verified = 0;
$stats_pending = 0;
$stats_investigating = 0;
$stats_total_loss = 0;

$stats_query = "SELECT 
    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating,
    SUM(amount_lost) as total_loss
    FROM scam_reports";

$stats_result = mysqli_query($conn, $stats_query);
if ($stats_result) {
    $stats = mysqli_fetch_assoc($stats_result);
    $stats_verified = $stats['verified'] ? $stats['verified'] : 0;
    $stats_pending = $stats['pending'] ? $stats['pending'] : 0;
    $stats_investigating = $stats['investigating'] ? $stats['investigating'] : 0;
    $stats_total_loss = $stats['total_loss'] ? $stats['total_loss'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Scam Database - ScamShield</title>
    <link rel="stylesheet" href="../models/css/database.css" />
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
      <!-- Hero Section -->
      <section class="hero">
        <div class="container">
          <div class="hero-content">
            <h1>Scam Database</h1>
            <p>
              Browse our comprehensive database of verified scam reports.
              Search, filter, and stay informed about current fraud threats.
            </p>
          </div>
        </div>
      </section>

      <!-- Search and Filter Section -->
      <section class="search-section">
        <div class="container">
          <div class="search-container">
            <div class="search-header">
              <h2>Search Scam Reports</h2>
              <p>
                Find specific scams or browse by category to stay informed about
                current threats.
              </p>
            </div>

            <form class="search-form" method="GET" action="database.php">
              <div class="search-bar">
                <input
                  type="text"
                  name="search"
                  id="searchInput"
                  value="<?php echo $search; ?>"
                  placeholder="Search by keywords, website, phone number, or description..."
                />
                <button type="submit" class="search-btn">🔍</button>
              </div>

              <div class="filters">
                <div class="filter-group">
                  <label for="categoryFilter">Category:</label>
                  <select name="category" id="categoryFilter" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="phishing" <?php if($category_filter == 'phishing') echo 'selected'; ?>>Phishing/Email</option>
                    <option value="financial" <?php if($category_filter == 'financial') echo 'selected'; ?>>Financial/Investment</option>
                    <option value="romance" <?php if($category_filter == 'romance') echo 'selected'; ?>>Romance Scams</option>
                    <option value="tech-support" <?php if($category_filter == 'tech-support') echo 'selected'; ?>>Tech Support</option>
                    <option value="online-shopping" <?php if($category_filter == 'online-shopping') echo 'selected'; ?>>Online Shopping</option>
                    <option value="cryptocurrency" <?php if($category_filter == 'cryptocurrency') echo 'selected'; ?>>Cryptocurrency</option>
                    <option value="employment" <?php if($category_filter == 'employment') echo 'selected'; ?>>Employment</option>
                    <option value="social-media" <?php if($category_filter == 'social-media') echo 'selected'; ?>>Social Media</option>
                    <option value="phone" <?php if($category_filter == 'phone') echo 'selected'; ?>>Phone/SMS</option>
                    <option value="identity-theft" <?php if($category_filter == 'identity-theft') echo 'selected'; ?>>Identity Theft</option>
                    <option value="charity" <?php if($category_filter == 'charity') echo 'selected'; ?>>Charity</option>
                    <option value="other" <?php if($category_filter == 'other') echo 'selected'; ?>>Other</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label for="statusFilter">Status:</label>
                  <select name="status" id="statusFilter" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="verified" <?php if($status_filter == 'verified') echo 'selected'; ?>>Verified</option>
                    <option value="pending" <?php if($status_filter == 'pending') echo 'selected'; ?>>Under Review</option>
                    <option value="investigating" <?php if($status_filter == 'investigating') echo 'selected'; ?>>Investigating</option>
                  </select>
                </div>

                <div class="filter-group">
                  <label for="dateFilter">Date Range:</label>
                  <select name="date" id="dateFilter" onchange="this.form.submit()">
                    <option value="">All Time</option>
                    <option value="today" <?php if($date_filter == 'today') echo 'selected'; ?>>Today</option>
                    <option value="week" <?php if($date_filter == 'week') echo 'selected'; ?>>Last Week</option>
                    <option value="month" <?php if($date_filter == 'month') echo 'selected'; ?>>Last Month</option>
                    <option value="quarter" <?php if($date_filter == 'quarter') echo 'selected'; ?>>Last 3 Months</option>
                    <option value="year" <?php if($date_filter == 'year') echo 'selected'; ?>>Last Year</option>
                  </select>
                </div>

                <a href="database.php" class="clear-filters" style="text-decoration: none;">Clear Filters</a>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Results Section -->
      <section class="results-section">
        <div class="container">
          <div class="results-header">
            <div class="results-info">
              <h3>Search Results</h3>
              <span class="results-count">Showing <?php echo $total_reports; ?> scam reports</span>
            </div>
          </div>

          <!-- Statistics Bar -->
          <div class="stats-bar">
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats_verified; ?></span>
              <span class="stat-label">Verified Reports</span>
            </div>
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats_pending; ?></span>
              <span class="stat-label">Under Review</span>
            </div>
            <div class="stat-item">
              <span class="stat-number"><?php echo $stats_investigating; ?></span>
              <span class="stat-label">Investigating</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">$<?php echo number_format($stats_total_loss, 2); ?></span>
              <span class="stat-label">Total Reported Losses</span>
            </div>
          </div>
          
          <!-- Display Scam Reports -->
          <div class="scam-reports">
            <?php
            if ($result && $total_reports > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $title = $row['scam_title'];
                    $category = $row['category_name'];
                    $description = $row['scam_description'];
                    $amount = $row['amount_lost'];
                    $status = $row['status'];
                    $date = date('F j, Y', strtotime($row['created_at']));
                    $scammer_email = $row['scammer_email'];
                    $scammer_phone = $row['scammer_phone'];
                    $scammer_website = $row['scammer_website'];
                    
                    echo '<div class="scam-card" style="border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 8px; background: white;">';
                    echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">';
                    echo '<h3 style="margin: 0; color: #333;">' . $title . '</h3>';
                    echo '<span class="status-badge ' . strtolower($status) . '">' . ucfirst($status) . '</span>';
                    echo '</div>';
                    
                    echo '<p style="color: #666; margin: 5px 0;"><strong>Category:</strong> ' . $category . '</p>';
                    echo '<p style="color: #666; margin: 5px 0;"><strong>Date:</strong> ' . $date . '</p>';
                    
                    if ($amount > 0) {
                        echo '<p style="color: #d9534f; margin: 5px 0;"><strong>Amount Lost:</strong> $' . number_format($amount, 2) . '</p>';
                    }
                    
                    echo '<p style="margin: 10px 0; line-height: 1.6;">' . substr($description, 0, 200) . '...</p>';
                    
                    if ($scammer_email) {
                        echo '<p style="color: #666; margin: 5px 0;"><strong>Email:</strong> ' . $scammer_email . '</p>';
                    }
                    if ($scammer_phone) {
                        echo '<p style="color: #666; margin: 5px 0;"><strong>Phone:</strong> ' . $scammer_phone . '</p>';
                    }
                    if ($scammer_website) {
                        echo '<p style="color: #666; margin: 5px 0;"><strong>Website:</strong> ' . $scammer_website . '</p>';
                    }
                    
                    echo '<a href="report_detail.php?id=' . $row['report_id'] . '" style="display: inline-block; margin-top: 10px; padding: 5px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 3px; font-size: 13px;">Details</a>';
                    
                    echo '</div>';
                }
            } else {
                echo '<div style="text-align: center; padding: 40px; color: #666;">';
                echo '<p style="font-size: 18px;">No scam reports found matching your search.</p>';
                echo '</div>';
            }
            ?>
          </div>
          
          <?php if ($total_pages > 1): ?>
          <div class="pagination-container">
            <?php if ($page > 1): ?>
              <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
            <?php endif; ?>
            
            <span class="pagination-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            
            <?php if ($page < $total_pages): ?>
              <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
            <?php endif; ?>
            
            <span class="page-jump-container">
              <input type="number" id="gotoPage" min="1" max="<?php echo $total_pages; ?>" placeholder="Page" class="page-jump-input">
              <button onclick="window.location.href='?page='+document.getElementById('gotoPage').value" class="page-jump-btn">Go</button>
            </span>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Report CTA Section -->
      <section class="report-cta">
        <div class="container">
          <div class="cta-content">
            <h2>Don't See Your Scam Listed?</h2>
            <p>
              Help protect others by reporting new scams to our database. Every
              report helps build a stronger defense against fraud.
            </p>
            <a href="../controllers/report.php" class="btn btn-primary">Report a New Scam</a>
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
