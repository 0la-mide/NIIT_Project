<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit;
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Check if category has items
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM food_items WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete category with items assigned to it";
        header('Location: food-categories.php');
        exit;
    }
    
    // Delete the category
    $stmt = $conn->prepare("DELETE FROM food_categories WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Category deleted successfully";
    header('Location: food-categories.php');
    exit;
}

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name = $conn->real_escape_string($_POST['name']);
    $slug = $conn->real_escape_string(strtolower(str_replace(' ', '-', $_POST['name'])));
    
    if ($category_id > 0) {
        // Update existing category
        $stmt = $conn->prepare("UPDATE food_categories SET name = ?, slug = ? WHERE category_id = ?");
        $stmt->bind_param("ssi", $name, $slug, $category_id);
        $message = "Category updated successfully";
    } else {
        // Insert new category
        $stmt = $conn->prepare("INSERT INTO food_categories (name, slug) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $slug);
        $message = "Category added successfully";
    }
    
    $stmt->execute();
    $_SESSION['message'] = $message;
    header('Location: food-categories.php');
    exit;
}

// Get all categories
$categories = $conn->query("SELECT * FROM food_categories ORDER BY name ASC");

// Get category for editing if ID is provided
$edit_category = null;
if (isset($_GET['edit'])) {
    $category_id = (int)$_GET['edit'];
    $edit_category = $conn->query("SELECT * FROM food_categories WHERE category_id = $category_id")->fetch_assoc();
}

// Include header
include __DIR__ . '/header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Food Categories</h1>
            <button onclick="document.getElementById('category-form').style.display='block'" 
                    class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Category Form (hidden by default) -->
        <div id="category-form" class="category-form" style="<?= $edit_category ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_category ? 'Edit Category' : 'Add New Category' ?></h2>
            <form method="POST" action="food-categories.php">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?= $edit_category['category_id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= $edit_category ? htmlspecialchars($edit_category['name']) : '' ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('category-form').style.display='none'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_category ? 'Update Category' : 'Add Category' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Categories List -->
        <table class="category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= htmlspecialchars($category['slug']) ?></td>
                    <td class="category-actions">
                        <a href="food-categories.php?edit=<?= $category['category_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="food-categories.php?delete=<?= $category['category_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this category?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Close form when clicking cancel or after submission
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('edit')) {
            document.getElementById('category-form').style.display = 'block';
        }
    });
</script>

<style>
    /* Category-specific styles */
    .category-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .category-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .category-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
    }
    
    .category-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .category-table tr:last-child td {
        border-bottom: none;
    }
    
    .category-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .category-actions {
        display: flex;
        gap: 8px;
        white-space: nowrap;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .category-table {
            display: block;
            overflow-x: auto;
        }
        
        .category-actions {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>
</body>
</html>