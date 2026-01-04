<?php
require_once '../../models/session_helper.php';
require_once '../../models/config.php';

if (!is_logged_in() || !is_admin()) {
    header("Location: ../../controllers/login.php");
    exit();
}

$success_message = "";
$error_message = "";

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $report_id = mysqli_real_escape_string($conn, $_GET['id']);
    $admin_id = $_SESSION['user_id'];
    
    if ($action == 'approve') {
        $query = "UPDATE scam_reports SET status='verified', reviewed_by=$admin_id, 
                  reviewed_at=NOW() WHERE report_id=$report_id";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_reports.php?success=approve");
            exit();
        } else {
            header("Location: manage_reports.php?error=approve");
            exit();
        }
    }
    
    if ($action == 'investigate') {
        $query = "UPDATE scam_reports SET status='investigating', reviewed_by=$admin_id, 
                  reviewed_at=NOW() WHERE report_id=$report_id";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_reports.php?success=investigate");
            exit();
        } else {
            header("Location: manage_reports.php?error=investigate");
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'reject_report') {
        $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
        $rejection_reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
        $admin_id = $_SESSION['user_id'];
        
        $query = "UPDATE scam_reports SET status='rejected', rejection_reason='$rejection_reason', 
                  reviewed_by=$admin_id, reviewed_at=NOW() WHERE report_id=$report_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_reports.php?success=reject");
            exit();
        } else {
            header("Location: manage_reports.php?error=reject");
            exit();
        }
    }
    
    if ($action == 'delete_report') {
        $report_id = mysqli_real_escape_string($conn, $_POST['report_id']);
        
        $query = "DELETE FROM scam_reports WHERE report_id=$report_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_reports.php?success=delete");
            exit();
        } else {
            header("Location: manage_reports.php?error=delete");
            exit();
        }
    }
}

if (isset($_GET['success'])) {
    $type = $_GET['success'];
    if ($type == 'approve') $success_message = "Report approved successfully!";
    if ($type == 'investigate') $success_message = "Report marked as investigating!";
    if ($type == 'reject') $success_message = "Report rejected successfully!";
    if ($type == 'delete') $success_message = "Report deleted successfully!";
}

if (isset($_GET['error'])) {
    $type = $_GET['error'];
    if ($type == 'approve') $error_message = "Failed to approve report!";
    if ($type == 'investigate') $error_message = "Failed to mark report as investigating!";
    if ($type == 'reject') $error_message = "Failed to reject report!";
    if ($type == 'delete') $error_message = "Failed to delete report!";
}

$where_conditions = array();
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

if (!empty($search)) {
    $where_conditions[] = "(scam_title LIKE '%$search%' OR scam_description LIKE '%$search%' OR 
                           reporter_name LIKE '%$search%' OR reporter_email LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status='$status_filter'";
}

if (!empty($category_filter)) {
    $where_conditions[] = "category_id=$category_filter";
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT sr.*, sc.category_name, u.username as reporter_username 
          FROM scam_reports sr 
          LEFT JOIN scam_categories sc ON sr.category_id = sc.category_id 
          LEFT JOIN users u ON sr.reporter_user_id = u.user_id 
          $where_sql 
          ORDER BY sr.created_at DESC";
$reports_result = mysqli_query($conn, $query);

$categories_query = "SELECT * FROM scam_categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

$view_id = isset($_GET['view']) ? mysqli_real_escape_string($conn, $_GET['view']) : null;
$reject_id = isset($_GET['reject']) ? mysqli_real_escape_string($conn, $_GET['reject']) : null;
$delete_id = isset($_GET['delete']) ? mysqli_real_escape_string($conn, $_GET['delete']) : null;

$view_report = null;
if ($view_id) {
    $query = "SELECT sr.*, sc.category_name, u.username as reporter_username, u.email as reporter_user_email,
              admin.username as reviewed_by_username
              FROM scam_reports sr 
              LEFT JOIN scam_categories sc ON sr.category_id = sc.category_id 
              LEFT JOIN users u ON sr.reporter_user_id = u.user_id 
              LEFT JOIN users admin ON sr.reviewed_by = admin.user_id
              WHERE sr.report_id=$view_id";
    $result = mysqli_query($conn, $query);
    $view_report = mysqli_fetch_assoc($result);
}

$reject_report = null;
if ($reject_id) {
    $query = "SELECT * FROM scam_reports WHERE report_id=$reject_id";
    $result = mysqli_query($conn, $query);
    $reject_report = mysqli_fetch_assoc($result);
}

$delete_report = null;
if ($delete_id) {
    $query = "SELECT * FROM scam_reports WHERE report_id=$delete_id";
    $result = mysqli_query($conn, $query);
    $delete_report = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - ScamShield Admin</title>
    <link rel="stylesheet" href="../../models/css/admin_dashboard.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">
                <h1>ScamShield</h1>
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3>Dashboard</h3>
                    <ul>
                        <li><a href="admin_dashboard.php">Overview</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.php">Manage Users</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="manage_reports.php" class="active">All Reports</a></li>
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
                        <li><a href="manage_categories.php">Categories</a></li>
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
                    <h2>Manage Reports</h2>
                </div>
                <div class="header-right">
                    <span class="user-info">
                        <?php echo htmlspecialchars(get_user_full_name()); ?> 
                        <span class="user-role">(Admin)</span>
                    </span>
                    <a href="../../controllers/logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <main class="dashboard-main">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>Search & Filter Reports</h2>
                    <form method="GET" action="manage_reports.php" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" placeholder="Title, description, or reporter" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="verified" <?php echo $status_filter == 'verified' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="investigating" <?php echo $status_filter == 'investigating' ? 'selected' : ''; ?>>Investigating</option>
                                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category">
                                    <option value="">All Categories</option>
                                    <?php
                                    mysqli_data_seek($categories_result, 0);
                                    while ($cat = mysqli_fetch_assoc($categories_result)) {
                                        $selected = $category_filter == $cat['category_id'] ? 'selected' : '';
                                        echo "<option value='" . $cat['category_id'] . "' $selected>" . 
                                             htmlspecialchars($cat['category_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn-primary">Filter</button>
                                <a href="manage_reports.php" class="btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($view_report): ?>
                <div class="section-card">
                    <h2>Report Details: <?php echo htmlspecialchars($view_report['scam_title']); ?></h2>
                    <div class="report-details">
                        <div class="detail-row">
                            <strong>Report ID:</strong> <?php echo $view_report['report_id']; ?>
                        </div>
                        <div class="detail-row">
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?php echo $view_report['status']; ?>">
                                <?php echo ucfirst($view_report['status']); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <strong>Category:</strong> <?php echo htmlspecialchars($view_report['category_name']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Reporter:</strong> 
                            <?php 
                            if ($view_report['make_anonymous']) {
                                echo "Anonymous";
                            } else {
                                echo htmlspecialchars($view_report['reporter_name']);
                                if ($view_report['reporter_username']) {
                                    echo " (" . htmlspecialchars($view_report['reporter_username']) . ")";
                                }
                            }
                            ?>
                        </div>
                        <div class="detail-row">
                            <strong>Reporter Email:</strong> <?php echo htmlspecialchars($view_report['reporter_email']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Date Occurred:</strong> 
                            <?php echo $view_report['date_occurred'] ? date('M d, Y', strtotime($view_report['date_occurred'])) : 'N/A'; ?>
                        </div>
                        <div class="detail-row">
                            <strong>Amount Lost:</strong> $<?php echo number_format($view_report['amount_lost'], 2); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Scammer Email:</strong> <?php echo htmlspecialchars($view_report['scammer_email']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Scammer Phone:</strong> <?php echo htmlspecialchars($view_report['scammer_phone']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Scammer Website:</strong> <?php echo htmlspecialchars($view_report['scammer_website']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Reporter Location:</strong> <?php echo htmlspecialchars($view_report['reporter_location']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Scammer Location:</strong> <?php echo htmlspecialchars($view_report['scammer_location']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Reported Elsewhere:</strong> <?php echo ucfirst($view_report['reported_elsewhere']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Description:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_report['scam_description'])); ?></div>
                        </div>
                        <?php if ($view_report['evidence_description']): ?>
                        <div class="detail-row">
                            <strong>Evidence Description:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_report['evidence_description'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($view_report['additional_notes']): ?>
                        <div class="detail-row">
                            <strong>Additional Notes:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_report['additional_notes'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($view_report['admin_notes']): ?>
                        <div class="detail-row">
                            <strong>Admin Notes:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_report['admin_notes'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if ($view_report['rejection_reason']): ?>
                        <div class="detail-row">
                            <strong>Rejection Reason:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_report['rejection_reason'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($view_report['created_at'])); ?>
                        </div>
                        <?php if ($view_report['reviewed_at']): ?>
                        <div class="detail-row">
                            <strong>Reviewed:</strong> 
                            <?php echo date('M d, Y H:i', strtotime($view_report['reviewed_at'])); ?>
                            by <?php echo htmlspecialchars($view_report['reviewed_by_username']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <a href="manage_reports.php" class="btn-secondary">Back to List</a>
                        <?php if ($view_report['status'] != 'verified'): ?>
                            <a href="manage_reports.php?action=approve&id=<?php echo $view_report['report_id']; ?>" class="btn-primary">Approve</a>
                        <?php endif; ?>
                        <?php if ($view_report['status'] != 'investigating'): ?>
                            <a href="manage_reports.php?action=investigate&id=<?php echo $view_report['report_id']; ?>" class="btn-primary">Mark Investigating</a>
                        <?php endif; ?>
                        <?php if ($view_report['status'] != 'rejected'): ?>
                            <a href="manage_reports.php?reject=<?php echo $view_report['report_id']; ?>" class="btn-danger">Reject</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($reject_report): ?>
                <div class="section-card">
                    <h2>Reject Report: <?php echo htmlspecialchars($reject_report['scam_title']); ?></h2>
                    <p>Report ID: <?php echo $reject_report['report_id']; ?></p>
                    <form method="POST" action="manage_reports.php" class="edit-form">
                        <input type="hidden" name="action" value="reject_report">
                        <input type="hidden" name="report_id" value="<?php echo $reject_report['report_id']; ?>">
                        
                        <div class="form-group">
                            <label>Rejection Reason *</label>
                            <textarea name="rejection_reason" rows="4" required placeholder="Explain why this report is being rejected..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Reject Report</button>
                            <a href="manage_reports.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($delete_report): ?>
                <div class="section-card">
                    <h2>Delete Report: <?php echo htmlspecialchars($delete_report['scam_title']); ?></h2>
                    <p>Report ID: <?php echo $delete_report['report_id']; ?></p>
                    <p class="warning-text">⚠️ Warning: This action cannot be undone! The report and all associated evidence will be permanently deleted.</p>
                    <form method="POST" action="manage_reports.php" class="edit-form">
                        <input type="hidden" name="action" value="delete_report">
                        <input type="hidden" name="report_id" value="<?php echo $delete_report['report_id']; ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Delete Report</button>
                            <a href="manage_reports.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>All Reports</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Reporter</th>
                                <th>Amount Lost</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($report = mysqli_fetch_assoc($reports_result)): ?>
                            <tr>
                                <td><?php echo $report['report_id']; ?></td>
                                <td><?php echo htmlspecialchars(substr($report['scam_title'], 0, 50)) . (strlen($report['scam_title']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($report['category_name']); ?></td>
                                <td>
                                    <?php 
                                    if ($report['make_anonymous']) {
                                        echo "Anonymous";
                                    } else {
                                        echo htmlspecialchars($report['reporter_name']);
                                    }
                                    ?>
                                </td>
                                <td>$<?php echo number_format($report['amount_lost'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $report['status']; ?>">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="manage_reports.php?view=<?php echo $report['report_id']; ?>" class="action-link">View</a> |
                                    <?php if ($report['status'] != 'verified'): ?>
                                        <a href="manage_reports.php?action=approve&id=<?php echo $report['report_id']; ?>" class="action-link">Approve</a> |
                                    <?php endif; ?>
                                    <?php if ($report['status'] != 'investigating'): ?>
                                        <a href="manage_reports.php?action=investigate&id=<?php echo $report['report_id']; ?>" class="action-link">Investigate</a> |
                                    <?php endif; ?>
                                    <?php if ($report['status'] != 'rejected'): ?>
                                        <a href="manage_reports.php?reject=<?php echo $report['report_id']; ?>" class="action-link">Reject</a> |
                                    <?php endif; ?>
                                    <a href="manage_reports.php?delete=<?php echo $report['report_id']; ?>" class="action-link action-delete">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
