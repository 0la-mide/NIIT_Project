<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../login.php');
    exit;
}

// Handle cinema deletion
if (isset($_GET['delete'])) {
    $cinema_id = (int)$_GET['delete'];
    
    // First get the image path to delete the file
    $stmt = $conn->prepare("SELECT image FROM cinemas WHERE cinema_id = ?");
    $stmt->bind_param("i", $cinema_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cinema = $result->fetch_assoc();
    
    if ($cinema && $cinema['image']) {
        $full_path = 'uploads/cinemas/' . $cinema['image'];
        if (file_exists($full_path)) {
            unlink($full_path); // Delete the image file
        }
    }
    
    // Then delete the cinema record
    $stmt = $conn->prepare("DELETE FROM cinemas WHERE cinema_id = ?");
    $stmt->bind_param("i", $cinema_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Cinema deleted successfully";
    header('Location: cinemas.php');
    exit;
}

// Handle form submission for adding/editing cinemas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cinema_id = isset($_POST['cinema_id']) ? (int)$_POST['cinema_id'] : 0;
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $address = $conn->real_escape_string($_POST['address']);
    $contact_phone = $conn->real_escape_string($_POST['contact_phone']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);
    $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : json_encode([]);
    $latitude = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;

    // Handle image upload
    $image_path = '';
    if ($cinema_id > 0) {
        // For updates, keep existing image if no new one is uploaded
        $existing = $conn->query("SELECT image FROM cinemas WHERE cinema_id = $cinema_id")->fetch_assoc();
        $image_path = $existing['image'];
    }
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/cinemas/';
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
        
        // Use original filename
        $filename = $_FILES['image']['name'];
        $target_path = $upload_dir . $filename;
        
        // Validate file type and size
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array(pathinfo($filename, PATHINFO_EXTENSION), $allowed_types) && $_FILES['image']['size'] <= $max_size) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $full_upload_dir . $filename)) {
                $image_path = $filename; // Store filename without path
            } else {
                $_SESSION['error'] = "Failed to upload cinema image";
                header('Location: cinemas.php');
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size (max 5MB allowed)";
            header('Location: cinemas.php');
            exit;
        }
    }
    
    if ($cinema_id > 0) {
        // Update existing cinema
        $stmt = $conn->prepare("UPDATE cinemas SET 
            name = ?, location = ?, address = ?, contact_phone = ?, 
            contact_email = ?, amenities = ?, latitude = ?, longitude = ?, image = ?
            WHERE cinema_id = ?");
        $stmt->bind_param("ssssssddsi", 
            $name, $location, $address, $contact_phone, 
            $contact_email, $amenities, $latitude, $longitude, $image_path, $cinema_id);
        $message = "Cinema updated successfully";
    } else {
        // Insert new cinema
        $stmt = $conn->prepare("INSERT INTO cinemas (
            name, location, address, contact_phone, 
            contact_email, amenities, latitude, longitude, image, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssssdds", 
            $name, $location, $address, $contact_phone, 
            $contact_email, $amenities, $latitude, $longitude, $image_path);
        $message = "Cinema added successfully";
    }
    
    $stmt->execute();
    $_SESSION['message'] = $message;
    header('Location: cinemas.php');
    exit;
}

// Get all cinemas
$cinemas = $conn->query("SELECT * FROM cinemas ORDER BY name ASC");

// Get cinema for editing if ID is provided
$edit_cinema = null;
if (isset($_GET['edit'])) {
    $cinema_id = (int)$_GET['edit'];
    $edit_cinema = $conn->query("SELECT * FROM cinemas WHERE cinema_id = $cinema_id")->fetch_assoc();
}

// Common amenities (from your database example)
$common_amenities = [
    'Food Court', 
    'Wheelchair Access', 
    'VIP Lounge', 
    'Bar', 
    'Arcade', 
    'Baby Changing'
];

// Include header
include __DIR__ . '/header.php';
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Cinemas</h1>
            <button onclick="document.getElementById('cinema-form').style.display='block'" 
                    class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Cinema
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
        
        <!-- Cinema Form (hidden by default) -->
        <div id="cinema-form" class="cinema-form" style="<?= $edit_cinema ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_cinema ? 'Edit Cinema' : 'Add New Cinema' ?></h2>
            <form method="POST" action="cinemas.php" enctype="multipart/form-data">
                <?php if ($edit_cinema): ?>
                    <input type="hidden" name="cinema_id" value="<?= $edit_cinema['cinema_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['name']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['location']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" required><?= 
                            $edit_cinema ? htmlspecialchars($edit_cinema['address']) : '' 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['contact_phone']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['contact_email']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="number" step="0.000001" id="latitude" name="latitude" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['latitude']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="number" step="0.000001" id="longitude" name="longitude" class="form-control" 
                               value="<?= $edit_cinema ? htmlspecialchars($edit_cinema['longitude']) : '' ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label for="image">Cinema Image</label>
                        <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        <?php if ($edit_cinema && $edit_cinema['image']): ?>
                            <div class="current-image" style="margin-top: 10px;">
                                <small>Current Image:</small>
                                <img src="/<?= $edit_cinema['image'] ?>" 
                                     alt="Current cinema image" 
                                     style="max-width: 200px; display: block; margin-top: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label>Amenities</label>
                        <div class="checkbox-group">
                            <?php 
                            $current_amenities = $edit_cinema ? json_decode($edit_cinema['amenities'], true) : [];
                            foreach ($common_amenities as $amenity): 
                            ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="amenity_<?= preg_replace('/[^a-z0-9]/', '_', strtolower($amenity)) ?>" 
                                           name="amenities[]" value="<?= htmlspecialchars($amenity) ?>"
                                           <?= in_array($amenity, $current_amenities) ? 'checked' : '' ?>>
                                    <label for="amenity_<?= preg_replace('/[^a-z0-9]/', '_', strtolower($amenity)) ?>">
                                        <?= htmlspecialchars($amenity) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('cinema-form').style.display='none'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_cinema ? 'Update Cinema' : 'Add Cinema' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Cinemas List -->
        <table class="cinema-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Amenities</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cinema = $cinemas->fetch_assoc()): 
                    $cinema_amenities = json_decode($cinema['amenities'], true);
                ?>
                <tr>
                    <td><?= htmlspecialchars($cinema['name']) ?></td>
                    <td><?= htmlspecialchars($cinema['location']) ?></td>
                    <td>
                        <?php if (!empty($cinema_amenities)): ?>
                            <div class="amenities-list">
                                <?php foreach ($cinema_amenities as $amenity): ?>
                                    <span class="amenity-tag"><?= htmlspecialchars($amenity) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span>-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($cinema['contact_phone'])): ?>
                            <div><i class="fas fa-phone"></i> <?= htmlspecialchars($cinema['contact_phone']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($cinema['contact_email'])): ?>
                            <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($cinema['contact_email']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="cinema-actions">
                        <a href="cinemas.php?edit=<?= $cinema['cinema_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="cinemas.php?delete=<?= $cinema['cinema_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this cinema?')">
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
            document.getElementById('cinema-form').style.display = 'block';
        }
    });
</script>

<style>
    /* Admin Container Styles */
    .admin-container {
        display: flex;
        min-height: 100vh;
    }
    
    .admin-content {
        flex: 1;
        padding: 20px;
    }
    
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .admin-title {
        margin: 0;
        color: #343a40;
    }
    
    /* Form Styles */
    .cinema-form {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 14px;
    }
    
    textarea.form-control {
        min-height: 80px;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    
    /* Table Styles */
    .cinema-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    
    .cinema-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
    }
    
    .cinema-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .cinema-table tr:last-child td {
        border-bottom: none;
    }
    
    .cinema-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .cinema-actions {
        display: flex;
        gap: 8px;
        white-space: nowrap;
    }
    
    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary {
        background-color: #e50914;
        color: white;
        border: none;
    }
    
    .btn-primary:hover {
        background-color: #c40812;
    }
    
    .btn-outline {
        background-color: transparent;
        border: 1px solid #ced4da;
        color: #495057;
    }
    
    .btn-outline:hover {
        background-color: #f1f3f5;
        border-color: #adb5bd;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: white;
        border: none;
    }
    
    .btn-danger:hover {
        background-color: #c82333;
    }
    
    .btn i {
        margin-right: 5px;
        font-size: 14px;
    }
    
    /* Amenities Styles */
    .amenities-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .amenity-tag {
        display: inline-block;
        padding: 3px 8px;
        background-color: #e9ecef;
        border-radius: 20px;
        font-size: 12px;
    }
    
    /* Checkbox Group */
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
    }
    
    .checkbox-item input {
        margin-right: 5px;
    }
    
    /* Current Image Styles */
    .current-image img {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        margin-top: 5px;
    }
    
    /* Message Styles */
    .message {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        background-color: #d4edda;
        color: #155724;
    }
    
    .error {
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .cinema-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</body>
</html>