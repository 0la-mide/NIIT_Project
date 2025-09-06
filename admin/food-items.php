<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit;
}

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = (int)$_GET['delete'];
    
    // First get the image path to delete the file
    $stmt = $conn->prepare("SELECT image FROM food_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($item && $item['image']) {
        $full_path = '../uploads/food/' . $item['image'];
        if (file_exists($full_path)) {
            unlink($full_path); // Delete the image file
        }
    }
    
    // Then delete the item record
    $stmt = $conn->prepare("DELETE FROM food_items WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Item deleted successfully";
    header('Location: food-items.php');
    exit;
}

// Handle form submission for adding/editing items
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
    $category_id = (int)$_POST['category_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle image upload
    $image_path = '';
    if ($item_id > 0) {
        // For updates, keep existing image if no new one is uploaded
        $existing = $conn->query("SELECT image FROM food_items WHERE item_id = $item_id")->fetch_assoc();
        $image_path = $existing['image'];
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/food/';
        $full_upload_dir = '../' . $upload_dir;
        if (!file_exists($full_upload_dir)) {
            mkdir($full_upload_dir, 0777, true);
        }
        
        // Delete old image if exists
        if ($image_path) {
            $full_old_path = $upload_dir . $image_path;
            if (file_exists($full_old_path)) {
                unlink($full_old_path);
            }
        }
        
        // Generate unique filename
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $target_path = $upload_dir . $filename;
        
        // Validate file type and size
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array(strtolower($ext), $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_upload_dir . $filename)) {
                $image_path = $filename;
            } else {
                $_SESSION['error'] = "Failed to upload item image";
                header('Location: food-items.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size (max 2MB allowed)";
            header('Location: food-items.php');
            exit;
        }
    }
    
    if ($item_id > 0) {
        // Update existing item
        $stmt = $conn->prepare("UPDATE food_items SET 
            category_id = ?, name = ?, description = ?, price = ?, 
            image = ?, is_active = ?
            WHERE item_id = ?");
        $stmt->bind_param("issdsii", 
            $category_id, $name, $description, $price, 
            $image_path, $is_active, $item_id);
        $message = "Item updated successfully";
    } else {
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO food_items (
            category_id, name, description, price, image, is_active, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issdsi", 
            $category_id, $name, $description, $price, 
            $image_path, $is_active);
        $message = "Item added successfully";
    }
    
    $stmt->execute();
    $_SESSION['message'] = $message;
    header('Location: food-items.php');
    exit;
}

// Get all categories for dropdown
$categories = $conn->query("SELECT * FROM food_categories ORDER BY name ASC");

// Get all food items with category names
$food_items = $conn->query("
    SELECT f.*, c.name as category_name 
    FROM food_items f
    JOIN food_categories c ON f.category_id = c.category_id
    ORDER BY f.name ASC
");

// Get item for editing if ID is provided
$edit_item = null;
if (isset($_GET['edit'])) {
    $item_id = (int)$_GET['edit'];
    $edit_item = $conn->query("
        SELECT * FROM food_items 
        WHERE item_id = $item_id
    ")->fetch_assoc();
}

// Include header
include __DIR__ . '/header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Food & Drinks</h1>
            <button onclick="document.getElementById('food-form').style.display='block'" 
                    class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Item
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
        
        <!-- Food Form (hidden by default) -->
        <div id="food-form" class="food-form" style="<?= $edit_item ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_item ? 'Edit Item' : 'Add New Item' ?></h2>
            <form method="POST" action="food-items.php" enctype="multipart/form-data">
                <?php if ($edit_item): ?>
                    <input type="hidden" name="item_id" value="<?= $edit_item['item_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option value="<?= $category['category_id'] ?>" 
                                    <?= ($edit_item && $edit_item['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Item Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= $edit_item ? htmlspecialchars($edit_item['name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" 
                               value="<?= $edit_item ? htmlspecialchars($edit_item['price']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"><?= 
                            $edit_item ? htmlspecialchars($edit_item['description']) : '' 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="is_active">Active</label>
                        <div class="checkbox-item">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   <?= ($edit_item && $edit_item['is_active']) ? 'checked' : '' ?>>
                            <label for="is_active">Available for purchase</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Item Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <?php if ($edit_item && $edit_item['image']): ?>
                            <div class="current-image" style="margin-top: 10px;">
                                <small>Current Image:</small>
                                <img src="../uploads/food/<?= $edit_item['image'] ?>" 
                                     alt="Current item image" 
                                     style="max-width: 200px; display: block; margin-top: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('food-form').style.display='none'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_item ? 'Update Item' : 'Add Item' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Food Items List -->
        <table class="food-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $food_items->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if ($item['image']): ?>
                            <img src="../uploads/food/<?= $item['image'] ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                    <td>₦<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <span class="status-badge <?= $item['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="food-actions">
                        <a href="food-items.php?edit=<?= $item['item_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="food-items.php?delete=<?= $item['item_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this item?')">
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
            document.getElementById('food-form').style.display = 'block';
        }
    });
</script>

<style>
    /* Food-specific styles */
    .food-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .food-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .food-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
    }
    
    .food-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .food-table tr:last-child td {
        border-bottom: none;
    }
    
    .food-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .food-actions {
        display: flex;
        gap: 8px;
        white-space: nowrap;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-badge.active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-badge.inactive {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .food-table {
            display: block;
            overflow-x: auto;
        }
        
        .food-actions {
            flex-direction: column;
            gap: 5px;
        }
    }
</style>
</body>
</html>