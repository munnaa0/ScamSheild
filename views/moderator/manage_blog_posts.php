<?php
require_once '../../models/session_helper.php';
require_once '../../models/config.php';

if (!is_logged_in() || !is_moderator()) {
    header("Location: ../../controllers/login.php");
    exit();
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action == 'delete_post') {
        $post_id = mysqli_real_escape_string($conn, $_POST['post_id']);
        
        $query = "DELETE FROM blog_posts WHERE post_id=$post_id";
        
        if (mysqli_query($conn, $query)) {
            header("Location: manage_blog_posts.php?success=delete");
            exit();
        } else {
            header("Location: manage_blog_posts.php?error=delete");
            exit();
        }
    }
}

if (isset($_GET['success'])) {
    $type = $_GET['success'];
    if ($type == 'delete') $success_message = "Blog post deleted successfully!";
}

if (isset($_GET['error'])) {
    $type = $_GET['error'];
    if ($type == 'delete') $error_message = "Failed to delete blog post!";
}

$where_conditions = array();
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$author_filter = isset($_GET['author']) ? mysqli_real_escape_string($conn, $_GET['author']) : '';

if (!empty($search)) {
    $where_conditions[] = "(title LIKE '%$search%' OR content LIKE '%$search%')";
}

if (!empty($category_filter)) {
    $where_conditions[] = "category='$category_filter'";
}

if (!empty($author_filter)) {
    $where_conditions[] = "bp.user_id=$author_filter";
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$query = "SELECT bp.*, u.username, u.first_name, u.last_name 
          FROM blog_posts bp 
          LEFT JOIN users u ON bp.user_id = u.user_id 
          $where_sql 
          ORDER BY bp.created_at DESC";
$posts_result = mysqli_query($conn, $query);

$categories_query = "SELECT DISTINCT category FROM blog_posts ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);

$authors_query = "SELECT DISTINCT u.user_id, u.username, u.first_name, u.last_name 
                  FROM users u 
                  INNER JOIN blog_posts bp ON u.user_id = bp.user_id 
                  ORDER BY u.username";
$authors_result = mysqli_query($conn, $authors_query);

$view_id = isset($_GET['view']) ? mysqli_real_escape_string($conn, $_GET['view']) : null;
$delete_id = isset($_GET['delete']) ? mysqli_real_escape_string($conn, $_GET['delete']) : null;

$view_post = null;
if ($view_id) {
    $query = "SELECT bp.*, u.username, u.first_name, u.last_name, u.email 
              FROM blog_posts bp 
              LEFT JOIN users u ON bp.user_id = u.user_id 
              WHERE bp.post_id=$view_id";
    $result = mysqli_query($conn, $query);
    $view_post = mysqli_fetch_assoc($result);
}

$delete_post = null;
if ($delete_id) {
    $query = "SELECT bp.*, u.username 
              FROM blog_posts bp 
              LEFT JOIN users u ON bp.user_id = u.user_id 
              WHERE bp.post_id=$delete_id";
    $result = mysqli_query($conn, $query);
    $delete_post = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog Posts - ScamShield Moderator</title>
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
                        <li><a href="moderator_dashboard.php">Overview</a></li>
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
                        <li><a href="manage_blog_posts.php" class="active">Blog Posts</a></li>
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
                    <h2>Manage Blog Posts</h2>
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
                    <h2>Search & Filter Blog Posts</h2>
                    <form method="GET" action="manage_blog_posts.php" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" placeholder="Title or content" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category">
                                    <option value="">All Categories</option>
                                    <?php
                                    mysqli_data_seek($categories_result, 0);
                                    while ($cat = mysqli_fetch_assoc($categories_result)) {
                                        $selected = $category_filter == $cat['category'] ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($cat['category']) . "' $selected>" . 
                                             htmlspecialchars($cat['category']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Author</label>
                                <select name="author">
                                    <option value="">All Authors</option>
                                    <?php
                                    mysqli_data_seek($authors_result, 0);
                                    while ($author = mysqli_fetch_assoc($authors_result)) {
                                        $selected = $author_filter == $author['user_id'] ? 'selected' : '';
                                        $author_name = $author['first_name'] . ' ' . $author['last_name'];
                                        if (empty(trim($author_name))) {
                                            $author_name = $author['username'];
                                        }
                                        echo "<option value='" . $author['user_id'] . "' $selected>" . 
                                             htmlspecialchars($author_name) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn-primary">Filter</button>
                                <a href="manage_blog_posts.php" class="btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($view_post): ?>
                <div class="section-card">
                    <h2><?php echo htmlspecialchars($view_post['title']); ?></h2>
                    <div class="report-details">
                        <div class="detail-row">
                            <strong>Post ID:</strong> <?php echo $view_post['post_id']; ?>
                        </div>
                        <div class="detail-row">
                            <strong>Category:</strong> <?php echo htmlspecialchars($view_post['category']); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Author:</strong> 
                            <?php 
                            $author_name = $view_post['first_name'] . ' ' . $view_post['last_name'];
                            if (empty(trim($author_name))) {
                                $author_name = $view_post['username'];
                            }
                            echo htmlspecialchars($author_name); 
                            ?>
                        </div>
                        <div class="detail-row">
                            <strong>Published:</strong> <?php echo date('M d, Y H:i', strtotime($view_post['created_at'])); ?>
                        </div>
                        <div class="detail-row">
                            <strong>Content:</strong>
                            <div class="detail-text blog-content"><?php echo nl2br(htmlspecialchars($view_post['content'])); ?></div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a href="manage_blog_posts.php" class="btn-secondary">Back to List</a>
                        <a href="manage_blog_posts.php?delete=<?php echo $view_post['post_id']; ?>" class="btn-danger">Delete</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($delete_post): ?>
                <div class="section-card">
                    <h2>Delete Blog Post</h2>
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($delete_post['title']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($delete_post['category']); ?></p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($delete_post['username']); ?></p>
                    <p class="warning-text">⚠️ Warning: This action cannot be undone! The blog post will be permanently deleted.</p>
                    <form method="POST" action="manage_blog_posts.php" class="edit-form">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="post_id" value="<?php echo $delete_post['post_id']; ?>">
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">Delete Post</button>
                            <a href="manage_blog_posts.php" class="btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="section-card">
                    <h2>All Blog Posts</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                            <tr>
                                <td><?php echo $post['post_id']; ?></td>
                                <td><?php echo htmlspecialchars(substr($post['title'], 0, 60)) . (strlen($post['title']) > 60 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($post['category']); ?></td>
                                <td><?php echo htmlspecialchars($post['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="manage_blog_posts.php?view=<?php echo $post['post_id']; ?>" class="action-link">View</a> |
                                    <a href="manage_blog_posts.php?delete=<?php echo $post['post_id']; ?>" class="action-link action-delete">Delete</a>
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
