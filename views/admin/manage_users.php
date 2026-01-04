<?php
require_once '../../models/session_helper.php';
require_once '../../models/config.php';

if (!is_logged_in() || !is_admin()) {
    header("Location: ../../controllers/login.php");
    exit();
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'edit_user') {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        
        $query = "UPDATE users SET username='$username', email='$email', first_name='$first_name', 
                  last_name='$last_name', phone='$phone', location='$location' WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php?success=edit");
            exit();
        } else {
            header("Location: manage_users.php?error=edit");
            exit();
        }
    }
    
    if ($action == 'change_role') {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
        
        $query = "UPDATE users SET role='$new_role' WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php?success=role");
            exit();
        } else {
            header("Location: manage_users.php?error=role");
            exit();
        }
    }
    
    if ($action == 'ban_user') {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $ban_reason = mysqli_real_escape_string($conn, $_POST['ban_reason']);
        $admin_id = $_SESSION['user_id'];
        
        $query = "UPDATE users SET is_banned=1, banned_at=NOW(), banned_by=$admin_id, 
                  ban_reason='$ban_reason' WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php?success=ban");
            exit();
        } else {
            header("Location: manage_users.php?error=ban");
            exit();
        }
    }
    
    if ($action == 'unban_user') {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        
        $query = "UPDATE users SET is_banned=0, banned_at=NULL, banned_by=NULL, 
                  ban_reason=NULL WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php?success=unban");
            exit();
        } else {
            header("Location: manage_users.php?error=unban");
            exit();
        }
    }
    
    if ($action == 'delete_user') {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        
        $query = "DELETE FROM users WHERE user_id=$user_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_users.php?success=delete");
            exit();
        } else {
            header("Location: manage_users.php?error=delete");
            exit();
        }
    }
}

if (isset($_GET['success'])) {
    $type = $_GET['success'];
    if ($type == 'edit') $success_message = "User updated successfully!";
    if ($type == 'role') $success_message = "User role changed successfully!";
    if ($type == 'ban') $success_message = "User banned successfully!";
    if ($type == 'unban') $success_message = "User unbanned successfully!";
    if ($type == 'delete') $success_message = "User deleted successfully!";
}

if (isset($_GET['error'])) {
    $type = $_GET['error'];
    if ($type == 'edit') $error_message = "Failed to update user!";
    if ($type == 'role') $error_message = "Failed to change user role!";
    if ($type == 'ban') $error_message = "Failed to ban user!";
    if ($type == 'unban') $error_message = "Failed to unban user!";
    if ($type == 'delete') $error_message = "Failed to delete user!";
}

$where_conditions = array();
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$banned_filter = isset($_GET['banned']) ? mysqli_real_escape_string($conn, $_GET['banned']) : '';

if (!empty($search)) {
    $where_conditions[] = "(username LIKE '%$search%' OR email LIKE '%$search%' OR 
                           first_name LIKE '%$search%' OR last_name LIKE '%$search%')";
}

if (!empty($role_filter)) {
    $where_conditions[] = "role='$role_filter'";
}

if ($banned_filter === '1') {
    $where_conditions[] = "is_banned=1";
} elseif ($banned_filter === '0') {
    $where_conditions[] = "is_banned=0";
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT * FROM users $where_sql ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $query);

$edit_id = isset($_GET['edit']) ? mysqli_real_escape_string($conn, $_GET['edit']) : null;
$change_role_id = isset($_GET['change_role']) ? mysqli_real_escape_string($conn, $_GET['change_role']) : null;
$ban_id = isset($_GET['ban']) ? mysqli_real_escape_string($conn, $_GET['ban']) : null;
$unban_id = isset($_GET['unban']) ? mysqli_real_escape_string($conn, $_GET['unban']) : null;
$delete_id = isset($_GET['delete']) ? mysqli_real_escape_string($conn, $_GET['delete']) : null;

$edit_user = null;
if ($edit_id) {
    $query = "SELECT * FROM users WHERE user_id=$edit_id";
    $result = mysqli_query($conn, $query);
    $edit_user = mysqli_fetch_assoc($result);
}

$role_user = null;
if ($change_role_id) {
    $query = "SELECT * FROM users WHERE user_id=$change_role_id";
    $result = mysqli_query($conn, $query);
    $role_user = mysqli_fetch_assoc($result);
}

$ban_user = null;
if ($ban_id) {
    $query = "SELECT * FROM users WHERE user_id=$ban_id";
    $result = mysqli_query($conn, $query);
    $ban_user = mysqli_fetch_assoc($result);
}

$unban_user = null;
if ($unban_id) {
    $query = "SELECT * FROM users WHERE user_id=$unban_id";
    $result = mysqli_query($conn, $query);
    $unban_user = mysqli_fetch_assoc($result);
}

$delete_user = null;
if ($delete_id) {
    $query = "SELECT * FROM users WHERE user_id=$delete_id";
    $result = mysqli_query($conn, $query);
    $delete_user = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - ScamShield Admin</title>
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
                        <li><a href="manage_users.php" class="active">Manage Users</a></li>
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
                    <h2>Manage Users</h2>
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
                    <h2>Search & Filter Users</h2>
                    <form method="GET" action="manage_users.php" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" placeholder="Username, email, or name" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role">
                                    <option value="">All Roles</option>
                                    <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="moderator" <?php echo $role_filter == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="banned">
                                    <option value="">All Status</option>
                                    <option value="0" <?php echo $banned_filter === '0' ? 'selected' : ''; ?>>Active</option>
                                    <option value="1" <?php echo $banned_filter === '1' ? 'selected' : ''; ?>>Banned</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn-primary">Filter</button>
                                <a href="manage_users.php" class="btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($edit_user): ?>
                <div class="section-card">
                    <h2>Edit User: <?php echo htmlspecialchars($edit_user['username']); ?></h2>
                    <form method="POST" action="manage_users.php" class="edit-form">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" value="<?php echo $edit_user['user_id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($edit_user['first_name']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($edit_user['last_name']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" value="<?php echo htmlspecialchars($edit_user['location']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Update User</button>
                            <a href="manage_users.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($role_user): ?>
                <div class="section-card">
                    <h2>Change Role: <?php echo htmlspecialchars($role_user['username']); ?></h2>
                    <p>Current Role: <strong><?php echo ucfirst(htmlspecialchars($role_user['role'])); ?></strong></p>
                    <form method="POST" action="manage_users.php" class="edit-form">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="user_id" value="<?php echo $role_user['user_id']; ?>">
                        
                        <div class="form-group">
                            <label>New Role *</label>
                            <select name="new_role" required>
                                <option value="user" <?php echo $role_user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="moderator" <?php echo $role_user['role'] == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                <option value="admin" <?php echo $role_user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Change Role</button>
                            <a href="manage_users.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($ban_user): ?>
                <div class="section-card">
                    <h2>Ban User: <?php echo htmlspecialchars($ban_user['username']); ?></h2>
                    <p>Email: <?php echo htmlspecialchars($ban_user['email']); ?></p>
                    <form method="POST" action="manage_users.php" class="edit-form">
                        <input type="hidden" name="action" value="ban_user">
                        <input type="hidden" name="user_id" value="<?php echo $ban_user['user_id']; ?>">
                        
                        <div class="form-group">
                            <label>Ban Reason *</label>
                            <textarea name="ban_reason" rows="4" required placeholder="Enter reason for banning this user..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Ban User</button>
                            <a href="manage_users.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($unban_user): ?>
                <div class="section-card">
                    <h2>Unban User: <?php echo htmlspecialchars($unban_user['username']); ?></h2>
                    <p>Email: <?php echo htmlspecialchars($unban_user['email']); ?></p>
                    <p>Ban Reason: <?php echo htmlspecialchars($unban_user['ban_reason']); ?></p>
                    <form method="POST" action="manage_users.php" class="edit-form">
                        <input type="hidden" name="action" value="unban_user">
                        <input type="hidden" name="user_id" value="<?php echo $unban_user['user_id']; ?>">
                        
                        <p>Are you sure you want to unban this user?</p>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Unban User</button>
                            <a href="manage_users.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($delete_user): ?>
                <div class="section-card">
                    <h2>Delete User: <?php echo htmlspecialchars($delete_user['username']); ?></h2>
                    <p>Email: <?php echo htmlspecialchars($delete_user['email']); ?></p>
                    <p class="warning-text">⚠️ Warning: This action cannot be undone! All user data including reports and blog posts will be deleted.</p>
                    <form method="POST" action="manage_users.php" class="edit-form">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?php echo $delete_user['user_id']; ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Delete User</button>
                            <a href="manage_users.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>All Users</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <span class="status-badge status-banned">Banned</span>
                                    <?php else: ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    if ($user['last_login']) {
                                        echo date('M d, Y', strtotime($user['last_login']));
                                    } else {
                                        echo 'Never';
                                    }
                                    ?>
                                </td>
                                <td class="actions">
                                    <a href="manage_users.php?edit=<?php echo $user['user_id']; ?>" class="action-link">Edit</a> |
                                    <a href="manage_users.php?change_role=<?php echo $user['user_id']; ?>" class="action-link">Change Role</a> |
                                    <?php if ($user['is_banned']): ?>
                                        <a href="manage_users.php?unban=<?php echo $user['user_id']; ?>" class="action-link">Unban</a> |
                                    <?php else: ?>
                                        <a href="manage_users.php?ban=<?php echo $user['user_id']; ?>" class="action-link">Ban</a> |
                                    <?php endif; ?>
                                    <a href="manage_users.php?delete=<?php echo $user['user_id']; ?>" class="action-link action-delete">Delete</a>
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
