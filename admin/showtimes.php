<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle showtime deletion
if (isset($_GET['delete'])) {
    $showtime_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM showtimes WHERE showtime_id = ?");
    $stmt->bind_param("i", $showtime_id);
    $stmt->execute();
    
    $_SESSION['message'] = "Showtime deleted successfully";
    header('Location: showtimes.php');
    exit;
}

// Handle form submission for adding/editing showtimes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $showtime_id = isset($_POST['showtime_id']) ? (int)$_POST['showtime_id'] : 0;
    $movie_id = (int)$_POST['movie_id'];
    $auditorium_id = (int)$_POST['auditorium_id'];
    $start_time = $conn->real_escape_string($_POST['start_time']);
    $price = (float)$_POST['price'];
    $format = $conn->real_escape_string($_POST['format']);
    
    // Calculate end time based on movie duration
    $movie_duration = $conn->query("SELECT duration FROM movies WHERE movie_id = $movie_id")->fetch_assoc()['duration'];
    $end_time = date('Y-m-d H:i:s', strtotime($start_time) + ($movie_duration * 60));
    
    if ($showtime_id > 0) {
        // Update existing showtime
        $stmt = $conn->prepare("UPDATE showtimes SET 
            movie_id = ?, auditorium_id = ?, start_time = ?, end_time = ?, 
            price = ?, format = ?
            WHERE showtime_id = ?");
        $stmt->bind_param("iissdsi", 
            $movie_id, $auditorium_id, $start_time, $end_time, 
            $price, $format, $showtime_id);
        $message = "Showtime updated successfully";
    } else {
        // Insert new showtime
        $stmt = $conn->prepare("INSERT INTO showtimes (
            movie_id, auditorium_id, start_time, end_time, price, format
        ) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissds", 
            $movie_id, $auditorium_id, $start_time, $end_time, 
            $price, $format);
        $message = "Showtime added successfully";
    }
    
    $stmt->execute();
    $_SESSION['message'] = $message;
    header('Location: showtimes.php');
    exit;
}

// Get all showtimes with related data
$showtimes = $conn->query("
    SELECT s.*, m.title as movie_title, a.name as auditorium_name, c.name as cinema_name
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.movie_id
    JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    ORDER BY s.start_time DESC
");

// Get showtime for editing if ID is provided
$edit_showtime = null;
if (isset($_GET['edit'])) {
    $showtime_id = (int)$_GET['edit'];
    $edit_showtime = $conn->query("
        SELECT * FROM showtimes WHERE showtime_id = $showtime_id
    ")->fetch_assoc();
}

// Include header
include __DIR__ . '/header.php';

// Get fresh copies of movies and auditoriums for dropdowns
$movies = $conn->query("SELECT movie_id, title FROM movies ORDER BY title");
$auditoriums = $conn->query("
    SELECT a.auditorium_id, a.name, c.name as cinema_name 
    FROM auditoriums a
    JOIN cinemas c ON a.cinema_id = c.cinema_id
    ORDER BY c.name, a.name
");
?>

<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Showtimes</h1>
            <button onclick="document.getElementById('showtime-form').style.display='block'" 
                    class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Showtime
            </button>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Showtime Form (hidden by default) -->
        <div id="showtime-form" class="showtime-form" style="<?= $edit_showtime ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_showtime ? 'Edit Showtime' : 'Add New Showtime' ?></h2>
            <form method="POST" action="showtimes.php">
                <?php if ($edit_showtime): ?>
                    <input type="hidden" name="showtime_id" value="<?= $edit_showtime['showtime_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="movie_id">Movie</label>
                        <select id="movie_id" name="movie_id" class="form-control" required>
                            <option value="">Select Movie</option>
                            <?php 
                            // Reset pointer and loop through movies again
                            $movies->data_seek(0);
                            while ($movie = $movies->fetch_assoc()): ?>
                            <option value="<?= $movie['movie_id'] ?>" 
                                <?= $edit_showtime && $edit_showtime['movie_id'] == $movie['movie_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($movie['title']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="auditorium_id">Auditorium</label>
                        <select id="auditorium_id" name="auditorium_id" class="form-control" required>
                            <option value="">Select Auditorium</option>
                            <?php 
                            // Reset pointer and loop through auditoriums again
                            $auditoriums->data_seek(0);
                            while ($auditorium = $auditoriums->fetch_assoc()): ?>
                            <option value="<?= $auditorium['auditorium_id'] ?>" 
                                <?= $edit_showtime && $edit_showtime['auditorium_id'] == $auditorium['auditorium_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($auditorium['cinema_name']) ?> - <?= htmlspecialchars($auditorium['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" id="start_time" name="start_time" class="form-control" 
                               value="<?= $edit_showtime ? str_replace(' ', 'T', substr($edit_showtime['start_time'], 0, 16)) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₦)</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                               value="<?= $edit_showtime ? $edit_showtime['price'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="format">Format</label>
                        <select id="format" name="format" class="form-control" required>
                            <option value="2D" <?= $edit_showtime && $edit_showtime['format'] == '2D' ? 'selected' : '' ?>>2D</option>
                            <option value="3D" <?= $edit_showtime && $edit_showtime['format'] == '3D' ? 'selected' : '' ?>>3D</option>
                            <option value="4D" <?= $edit_showtime && $edit_showtime['format'] == '4D' ? 'selected' : '' ?>>4D</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('showtime-form').style.display='none'">
                                Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_showtime ? 'Update Showtime' : 'Add Showtime' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Showtimes List -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Cinema</th>
                    <th>Auditorium</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Price</th>
                    <th>Format</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($showtime = $showtimes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($showtime['movie_title']) ?></td>
                    <td><?= htmlspecialchars($showtime['cinema_name']) ?></td>
                    <td><?= htmlspecialchars($showtime['auditorium_name']) ?></td>
                    <td><?= date('M j, Y g:i a', strtotime($showtime['start_time'])) ?></td>
                    <td><?= date('M j, Y g:i a', strtotime($showtime['end_time'])) ?></td>
                    <td>₦<?= number_format($showtime['price'], 2) ?></td>
                    <td><?= htmlspecialchars($showtime['format']) ?></td>
                    <td class="actions">
                        <a href="showtimes.php?edit=<?= $showtime['showtime_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="showtimes.php?delete=<?= $showtime['showtime_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this showtime?')">
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
            document.getElementById('showtime-form').style.display = 'block';
            // Scroll to form
            document.getElementById('showtime-form').scrollIntoView({ behavior: 'smooth' });
        }
    });
</script>

</body>
</html>