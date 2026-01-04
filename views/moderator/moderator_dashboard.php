<?php
require_once '../../models/session_helper.php';
require_once '../../models/config.php';

if (!is_logged_in() || !is_moderator()) {
    header("Location: ../../controllers/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Dashboard - ScamShield</title>
    <link rel="stylesheet" href="../../models/css/admin_dashboard.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h1>ScamShield</h1>
                <p>Moderator Panel</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3>Dashboard</h3>
                    <ul>
                        <li><a href="moderator_dashboard.php" class="active">Overview</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="manage_reports.php">All Reports</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Messages</h3>
                    <ul>
                        <li><a href="manage_messages.php">Contact Messages</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Content</h3>
                    <ul>
                        <li><a href="manage_blog_posts.php">Blog Posts</a></li>
                    </ul>
                </div>
            </nav>
            <div class="sidebar-footer">
                <a href="../home.php" class="back-to-site">← Back to Site</a>
            </div>
        </aside>
        
        <div class="admin-content">
            <header class="admin-header">
                <div class="header-left">
                    <h2>ScamShield Moderator Panel</h2>
                </div>
                <div class="header-right">
                    <span class="user-info">
                        <?php echo htmlspecialchars(get_user_full_name()); ?> 
                        <span class="user-role">(Moderator)</span>
                    </span>
                    <a href="../../controllers/logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <main class="dashboard-main">
                <div class="dashboard-header">
                    <h1>Moderator Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars(get_user_display_name()); ?>!</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-info">
                            <h3>Total Reports</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM scam_reports";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $row['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3>Pending Reports</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM scam_reports WHERE status = 'pending'";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $row['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📧</div>
                        <div class="stat-info">
                            <h3>New Messages</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $row['total']; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📝</div>
                        <div class="stat-info">
                            <h3>Blog Posts</h3>
                            <?php
                            $query = "SELECT COUNT(*) as total FROM blog_posts";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            ?>
                            <p class="stat-number"><?php echo $row['total']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-sections">
                    <div class="section-card">
                        <h2>Recent Reports</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT report_id, scam_title, status, created_at 
                                         FROM scam_reports 
                                         ORDER BY created_at DESC 
                                         LIMIT 5";
                                $result = mysqli_query($conn, $query);
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['report_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars(substr($row['scam_title'], 0, 50)) . "...</td>";
                                    echo "<td><span class='status-badge status-" . $row['status'] . "'>" . ucfirst($row['status']) . "</span></td>";
                                    echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                    echo "<td><a href='manage_reports.php?view=" . $row['report_id'] . "'>View</a></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="section-card">
                        <h2>Quick Actions</h2>
                        <div class="quick-actions">
                            <a href="manage_reports.php" class="action-btn">Manage Reports</a>
                            <a href="manage_messages.php" class="action-btn">View Messages</a>
                            <a href="manage_blog_posts.php" class="action-btn">Manage Blog Posts</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
