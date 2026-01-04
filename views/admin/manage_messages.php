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
    $message_id = mysqli_real_escape_string($conn, $_GET['id']);
    $admin_id = $_SESSION['user_id'];
    
    if ($action == 'mark_in_progress') {
        $query = "UPDATE contact_messages SET status='in-progress', assigned_to=$admin_id 
                  WHERE message_id=$message_id";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=in_progress");
            exit();
        } else {
            header("Location: manage_messages.php?error=in_progress");
            exit();
        }
    }
    
    if ($action == 'mark_resolved') {
        $query = "UPDATE contact_messages SET status='resolved', resolved_by=$admin_id, 
                  resolved_at=NOW() WHERE message_id=$message_id";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=resolved");
            exit();
        } else {
            header("Location: manage_messages.php?error=resolved");
            exit();
        }
    }
    
    if ($action == 'mark_closed') {
        $query = "UPDATE contact_messages SET status='closed' WHERE message_id=$message_id";
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=closed");
            exit();
        } else {
            header("Location: manage_messages.php?error=closed");
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'reply_message') {
        $message_id = mysqli_real_escape_string($conn, $_POST['message_id']);
        $admin_reply = mysqli_real_escape_string($conn, $_POST['admin_reply']);
        $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
        $admin_id = $_SESSION['user_id'];
        
        $query = "UPDATE contact_messages SET admin_reply='$admin_reply', status='$new_status', 
                  assigned_to=$admin_id WHERE message_id=$message_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=reply");
            exit();
        } else {
            header("Location: manage_messages.php?error=reply");
            exit();
        }
    }
    
    if ($action == 'change_status') {
        $message_id = mysqli_real_escape_string($conn, $_POST['message_id']);
        $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
        $admin_id = $_SESSION['user_id'];
        
        $update_fields = "status='$new_status'";
        
        if ($new_status == 'in-progress') {
            $update_fields .= ", assigned_to=$admin_id";
        } elseif ($new_status == 'resolved') {
            $update_fields .= ", resolved_by=$admin_id, resolved_at=NOW()";
        }
        
        $query = "UPDATE contact_messages SET $update_fields WHERE message_id=$message_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=status");
            exit();
        } else {
            header("Location: manage_messages.php?error=status");
            exit();
        }
    }
    
    if ($action == 'delete_message') {
        $message_id = mysqli_real_escape_string($conn, $_POST['message_id']);
        
        $query = "DELETE FROM contact_messages WHERE message_id=$message_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_messages.php?success=delete");
            exit();
        } else {
            header("Location: manage_messages.php?error=delete");
            exit();
        }
    }
}

if (isset($_GET['success'])) {
    $type = $_GET['success'];
    if ($type == 'in_progress') $success_message = "Message marked as in-progress!";
    if ($type == 'resolved') $success_message = "Message marked as resolved!";
    if ($type == 'closed') $success_message = "Message marked as closed!";
    if ($type == 'reply') $success_message = "Reply sent successfully!";
    if ($type == 'status') $success_message = "Status updated successfully!";
    if ($type == 'delete') $success_message = "Message deleted successfully!";
}

if (isset($_GET['error'])) {
    $type = $_GET['error'];
    if ($type == 'in_progress') $error_message = "Failed to mark as in-progress!";
    if ($type == 'resolved') $error_message = "Failed to mark as resolved!";
    if ($type == 'closed') $error_message = "Failed to mark as closed!";
    if ($type == 'reply') $error_message = "Failed to send reply!";
    if ($type == 'status') $error_message = "Failed to update status!";
    if ($type == 'delete') $error_message = "Failed to delete message!";
}

$where_conditions = array();
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$subject_filter = isset($_GET['subject']) ? mysqli_real_escape_string($conn, $_GET['subject']) : '';
$priority_filter = isset($_GET['priority']) ? mysqli_real_escape_string($conn, $_GET['priority']) : '';

if (!empty($search)) {
    $where_conditions[] = "(first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR 
                           email LIKE '%$search%' OR message LIKE '%$search%')";
}

if (!empty($status_filter)) {
    $where_conditions[] = "status='$status_filter'";
}

if (!empty($subject_filter)) {
    $where_conditions[] = "subject='$subject_filter'";
}

if (!empty($priority_filter)) {
    $where_conditions[] = "priority='$priority_filter'";
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT cm.*, u.username as assigned_username, r.username as resolved_username 
          FROM contact_messages cm 
          LEFT JOIN users u ON cm.assigned_to = u.user_id 
          LEFT JOIN users r ON cm.resolved_by = r.user_id 
          $where_sql 
          ORDER BY cm.created_at DESC";
$messages_result = mysqli_query($conn, $query);

$view_id = isset($_GET['view']) ? mysqli_real_escape_string($conn, $_GET['view']) : null;
$reply_id = isset($_GET['reply']) ? mysqli_real_escape_string($conn, $_GET['reply']) : null;
$change_status_id = isset($_GET['change_status']) ? mysqli_real_escape_string($conn, $_GET['change_status']) : null;
$delete_id = isset($_GET['delete']) ? mysqli_real_escape_string($conn, $_GET['delete']) : null;

$view_message = null;
if ($view_id) {
    $query = "SELECT cm.*, u.username as assigned_username, r.username as resolved_username 
              FROM contact_messages cm 
              LEFT JOIN users u ON cm.assigned_to = u.user_id 
              LEFT JOIN users r ON cm.resolved_by = r.user_id 
              WHERE cm.message_id=$view_id";
    $result = mysqli_query($conn, $query);
    $view_message = mysqli_fetch_assoc($result);
}

$reply_message = null;
if ($reply_id) {
    $query = "SELECT * FROM contact_messages WHERE message_id=$reply_id";
    $result = mysqli_query($conn, $query);
    $reply_message = mysqli_fetch_assoc($result);
}

$status_message = null;
if ($change_status_id) {
    $query = "SELECT * FROM contact_messages WHERE message_id=$change_status_id";
    $result = mysqli_query($conn, $query);
    $status_message = mysqli_fetch_assoc($result);
}

$delete_message = null;
if ($delete_id) {
    $query = "SELECT * FROM contact_messages WHERE message_id=$delete_id";
    $result = mysqli_query($conn, $query);
    $delete_message = mysqli_fetch_assoc($result);
}

function format_subject($subject) {
    $subjects = array(
        'general-inquiry' => 'General Inquiry',
        'report-issue' => 'Report Issue',
        'verification-question' => 'Verification Question',
        'media-inquiry' => 'Media Inquiry',
        'partnership' => 'Partnership',
        'technical-support' => 'Technical Support',
        'feedback' => 'Feedback',
        'other' => 'Other'
    );
    return isset($subjects[$subject]) ? $subjects[$subject] : ucfirst($subject);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - ScamShield Admin</title>
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
                        <li><a href="manage_reports.php">All Reports</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Messages</h3>
                    <ul>
                        <li><a href="manage_messages.php" class="active">Contact Messages</a></li>
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
                    <h2>Manage Contact Messages</h2>
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
                    <h2>Search & Filter Messages</h2>
                    <form method="GET" action="manage_messages.php" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" placeholder="Name, email, or message" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status">
                                    <option value="">All Status</option>
                                    <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="in-progress" <?php echo $status_filter == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="normal" <?php echo $priority_filter == 'normal' ? 'selected' : ''; ?>>Normal</option>
                                    <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo $priority_filter == 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn-primary">Filter</button>
                                <a href="manage_messages.php" class="btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($view_message): ?>
                <div class="section-card">
                    <h2>Message Details</h2>
                    <div class="report-details">
                        <div class="detail-row">
                            <strong>Message ID:</strong> <?php echo $view_message['message_id']; ?>
                        </div>
                        <div class="detail-row">
                            <strong>Status:</strong> 
                            <span class="status-badge status-<?php echo $view_message['status']; ?>">
                                <?php echo ucfirst($view_message['status']); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <strong>Priority:</strong> 
                            <span class="priority-badge priority-<?php echo $view_message['priority']; ?>">
                                <?php echo ucfirst($view_message['priority']); ?>
                            </span>
                        </div>
                        <div class="detail-row">
                            <strong>Subject:</strong> <?php echo format_subject($view_message['subject']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>From:</strong> 
                            <?php echo htmlspecialchars($view_message['first_name'] . ' ' . $view_message['last_name']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Email:</strong> <?php echo htmlspecialchars($view_message['email']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($view_message['phone']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Message:</strong>
                            <div class="detail-text"><?php echo nl2br(htmlspecialchars($view_message['message'])); ?></div>
                        </div>
                        <?php if ($view_message['admin_reply']): ?>
                        <div class="detail-row">
                            <strong>Admin Reply:</strong>
                            <div class="detail-text admin-reply"><?php echo nl2br(htmlspecialchars($view_message['admin_reply'])); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <strong>Newsletter:</strong> <?php echo $view_message['newsletter_subscription'] ? 'Yes' : 'No'; ?>
                        </div>
                        <div class="detail-row">
                            <strong>Received:</strong> <?php echo date('M d, Y H:i', strtotime($view_message['created_at'])); ?>
                        </div>
                        <?php if ($view_message['assigned_to']): ?>
                        <div class="detail-row">
                            <strong>Assigned To:</strong> <?php echo htmlspecialchars($view_message['assigned_username']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($view_message['resolved_at']): ?>
                        <div class="detail-row">
                            <strong>Resolved:</strong> 
                            <?php echo date('M d, Y H:i', strtotime($view_message['resolved_at'])); ?>
                            by <?php echo htmlspecialchars($view_message['resolved_username']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-actions">
                        <a href="manage_messages.php" class="btn-secondary">Back to List</a>
                        <a href="manage_messages.php?reply=<?php echo $view_message['message_id']; ?>" class="btn-primary">Reply</a>
                        <a href="manage_messages.php?change_status=<?php echo $view_message['message_id']; ?>" class="btn-primary">Change Status</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($reply_message): ?>
                <div class="section-card">
                    <h2>Reply to Message</h2>
                    <div class="message-preview">
                        <p><strong>From:</strong> <?php echo htmlspecialchars($reply_message['first_name'] . ' ' . $reply_message['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($reply_message['email']); ?></p>
                        <p><strong>Subject:</strong> <?php echo format_subject($reply_message['subject']); ?></p>
                        <p><strong>Original Message:</strong></p>
                        <div class="detail-text"><?php echo nl2br(htmlspecialchars($reply_message['message'])); ?></div>
                    </div>
                    <form method="POST" action="manage_messages.php" class="edit-form">
                        <input type="hidden" name="action" value="reply_message">
                        <input type="hidden" name="message_id" value="<?php echo $reply_message['message_id']; ?>">
                        
                        <div class="form-group">
                            <label>Your Reply *</label>
                            <textarea name="admin_reply" rows="8" required placeholder="Type your reply here..."><?php echo htmlspecialchars($reply_message['admin_reply']); ?></textarea>
                            <small>Note: This reply is stored in the system. You'll need to send it via email separately.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Update Status *</label>
                            <select name="new_status" required>
                                <option value="in-progress" <?php echo $reply_message['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $reply_message['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $reply_message['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Save Reply</button>
                            <a href="manage_messages.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($status_message): ?>
                <div class="section-card">
                    <h2>Change Message Status</h2>
                    <p><strong>Message from:</strong> <?php echo htmlspecialchars($status_message['first_name'] . ' ' . $status_message['last_name']); ?></p>
                    <p><strong>Current Status:</strong> 
                        <span class="status-badge status-<?php echo $status_message['status']; ?>">
                            <?php echo ucfirst($status_message['status']); ?>
                        </span>
                    </p>
                    <form method="POST" action="manage_messages.php" class="edit-form">
                        <input type="hidden" name="action" value="change_status">
                        <input type="hidden" name="message_id" value="<?php echo $status_message['message_id']; ?>">
                        
                        <div class="form-group">
                            <label>New Status *</label>
                            <select name="new_status" required>
                                <option value="new" <?php echo $status_message['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                                <option value="in-progress" <?php echo $status_message['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="resolved" <?php echo $status_message['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="closed" <?php echo $status_message['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Update Status</button>
                            <a href="manage_messages.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($delete_message): ?>
                <div class="section-card">
                    <h2>Delete Message</h2>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($delete_message['first_name'] . ' ' . $delete_message['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($delete_message['email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo format_subject($delete_message['subject']); ?></p>
                    <p class="warning-text">⚠️ Warning: This action cannot be undone! The message will be permanently deleted.</p>
                    <form method="POST" action="manage_messages.php" class="edit-form">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="message_id" value="<?php echo $delete_message['message_id']; ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Delete Message</button>
                            <a href="manage_messages.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>All Contact Messages</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>From</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($msg = mysqli_fetch_assoc($messages_result)): ?>
                            <tr>
                                <td><?php echo $msg['message_id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo format_subject($msg['subject']); ?></td>
                                <td>
                                    <span class="priority-badge priority-<?php echo $msg['priority']; ?>">
                                        <?php echo ucfirst($msg['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="manage_messages.php?view=<?php echo $msg['message_id']; ?>" class="action-link">View</a> |
                                    <a href="manage_messages.php?reply=<?php echo $msg['message_id']; ?>" class="action-link">Reply</a> |
                                    <?php if ($msg['status'] == 'new'): ?>
                                        <a href="manage_messages.php?action=mark_in_progress&id=<?php echo $msg['message_id']; ?>" class="action-link">Mark In Progress</a> |
                                    <?php endif; ?>
                                    <?php if ($msg['status'] != 'resolved'): ?>
                                        <a href="manage_messages.php?action=mark_resolved&id=<?php echo $msg['message_id']; ?>" class="action-link">Mark Resolved</a> |
                                    <?php endif; ?>
                                    <a href="manage_messages.php?change_status=<?php echo $msg['message_id']; ?>" class="action-link">Change Status</a> |
                                    <a href="manage_messages.php?delete=<?php echo $msg['message_id']; ?>" class="action-link action-delete">Delete</a>
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
