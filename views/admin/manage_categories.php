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
    
    if ($action == 'add_category') {
        $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category_slug = strtolower(str_replace(' ', '-', $category_name));
        $category_slug = mysqli_real_escape_string($conn, $category_slug);
        
        $check_query = "SELECT * FROM scam_categories WHERE category_name='$category_name' OR category_slug='$category_slug'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            header("Location: manage_categories.php?error=duplicate");
            exit();
        }
        
        $query = "INSERT INTO scam_categories (category_name, category_slug, description) 
                  VALUES ('$category_name', '$category_slug', '$description')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_categories.php?success=add");
            exit();
        } else {
            header("Location: manage_categories.php?error=add");
            exit();
        }
    }
    
    if ($action == 'edit_category') {
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $category_slug = strtolower(str_replace(' ', '-', $category_name));
        $category_slug = mysqli_real_escape_string($conn, $category_slug);
        
        $check_query = "SELECT * FROM scam_categories 
                        WHERE (category_name='$category_name' OR category_slug='$category_slug') 
                        AND category_id != $category_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            header("Location: manage_categories.php?error=duplicate&edit=$category_id");
            exit();
        }
        
        $query = "UPDATE scam_categories SET category_name='$category_name', 
                  category_slug='$category_slug', description='$description' 
                  WHERE category_id=$category_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_categories.php?success=edit");
            exit();
        } else {
            header("Location: manage_categories.php?error=edit");
            exit();
        }
    }
    
    if ($action == 'delete_category') {
        $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
        
        $check_query = "SELECT COUNT(*) as count FROM scam_reports WHERE category_id=$category_id";
        $check_result = mysqli_query($conn, $check_query);
        $check_row = mysqli_fetch_assoc($check_result);
        
        if ($check_row['count'] > 0) {
            header("Location: manage_categories.php?error=has_reports&count=" . $check_row['count']);
            exit();
        }
        
        $query = "DELETE FROM scam_categories WHERE category_id=$category_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_categories.php?success=delete");
            exit();
        } else {
            header("Location: manage_categories.php?error=delete");
            exit();
        }
    }
}

if (isset($_GET['success'])) {
    $type = $_GET['success'];
    if ($type == 'add') $success_message = "Category added successfully!";
    if ($type == 'edit') $success_message = "Category updated successfully!";
    if ($type == 'delete') $success_message = "Category deleted successfully!";
}

if (isset($_GET['error'])) {
    $type = $_GET['error'];
    if ($type == 'add') $error_message = "Failed to add category!";
    if ($type == 'edit') $error_message = "Failed to update category!";
    if ($type == 'delete') $error_message = "Failed to delete category!";
    if ($type == 'duplicate') $error_message = "Category name already exists!";
    if ($type == 'has_reports') {
        $count = isset($_GET['count']) ? $_GET['count'] : 0;
        $error_message = "Cannot delete category! It has $count report(s) associated with it.";
    }
}

$query = "SELECT sc.*, COUNT(sr.report_id) as report_count 
          FROM scam_categories sc 
          LEFT JOIN scam_reports sr ON sc.category_id = sr.category_id 
          GROUP BY sc.category_id 
          ORDER BY sc.category_name";
$categories_result = mysqli_query($conn, $query);

$edit_id = isset($_GET['edit']) ? mysqli_real_escape_string($conn, $_GET['edit']) : null;
$delete_id = isset($_GET['delete']) ? mysqli_real_escape_string($conn, $_GET['delete']) : null;

$edit_category = null;
if ($edit_id) {
    $query = "SELECT * FROM scam_categories WHERE category_id=$edit_id";
    $result = mysqli_query($conn, $query);
    $edit_category = mysqli_fetch_assoc($result);
}

$delete_category = null;
$delete_report_count = 0;
if ($delete_id) {
    $query = "SELECT sc.*, COUNT(sr.report_id) as report_count 
              FROM scam_categories sc 
              LEFT JOIN scam_reports sr ON sc.category_id = sr.category_id 
              WHERE sc.category_id=$delete_id 
              GROUP BY sc.category_id";
    $result = mysqli_query($conn, $query);
    $delete_category = mysqli_fetch_assoc($result);
    $delete_report_count = $delete_category['report_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - ScamShield Admin</title>
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
                        <li><a href="manage_messages.php">Contact Messages</a></li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3>Content</h3>
                    <ul>
                        <li><a href="manage_blog_posts.php">Blog Posts</a></li>
                        <li><a href="manage_categories.php" class="active">Categories</a></li>
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
                    <h2>Manage Categories</h2>
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

                <?php if ($edit_category): ?>
                <div class="section-card">
                    <h2>Edit Category: <?php echo htmlspecialchars($edit_category['category_name']); ?></h2>
                    <form method="POST" action="manage_categories.php" class="edit-form">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                        
                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="category_name" value="<?php echo htmlspecialchars($edit_category['category_name']); ?>" required>
                            <small>Slug will be auto-generated from name</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4"><?php echo htmlspecialchars($edit_category['description']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Update Category</button>
                            <a href="manage_categories.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($delete_category): ?>
                <div class="section-card">
                    <h2>Delete Category: <?php echo htmlspecialchars($delete_category['category_name']); ?></h2>
                    <p><strong>Slug:</strong> <?php echo htmlspecialchars($delete_category['category_slug']); ?></p>
                    <p><strong>Reports in this category:</strong> <?php echo $delete_report_count; ?></p>
                    
                    <?php if ($delete_report_count > 0): ?>
                        <p class="warning-text">⚠️ Warning: This category has <?php echo $delete_report_count; ?> report(s)! You must reassign or delete those reports before deleting this category.</p>
                        <div class="form-actions">
                            <a href="manage_categories.php" class="btn-secondary">Back to List</a>
                        </div>
                    <?php else: ?>
                        <p class="warning-text">⚠️ Warning: This action cannot be undone! The category will be permanently deleted.</p>
                        <form method="POST" action="manage_categories.php" class="edit-form">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" name="category_id" value="<?php echo $delete_category['category_id']; ?>">
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-danger">Delete Category</button>
                                <a href="manage_categories.php" class="btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>Add New Category</h2>
                    <form method="POST" action="manage_categories.php" class="edit-form">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category Name *</label>
                                <input type="text" name="category_name" required placeholder="e.g., Phishing">
                                <small>Slug will be auto-generated (e.g., phishing)</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3" placeholder="Brief description of this scam category"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>

                <div class="section-card">
                    <h2>All Categories</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Slug</th>
                                <th>Description</th>
                                <th>Reports</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <tr>
                                <td><?php echo $cat['category_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($cat['category_slug']); ?></code></td>
                                <td>
                                    <?php 
                                    $desc = $cat['description'];
                                    if (strlen($desc) > 60) {
                                        echo htmlspecialchars(substr($desc, 0, 60)) . '...';
                                    } else {
                                        echo htmlspecialchars($desc);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="count-badge">
                                        <?php echo $cat['report_count']; ?> report<?php echo $cat['report_count'] != 1 ? 's' : ''; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($cat['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="manage_categories.php?edit=<?php echo $cat['category_id']; ?>" class="action-link">Edit</a> |
                                    <a href="manage_categories.php?delete=<?php echo $cat['category_id']; ?>" class="action-link action-delete">Delete</a>
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
