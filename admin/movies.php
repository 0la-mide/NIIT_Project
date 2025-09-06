<?php
session_start();
require_once '../config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle movie deletion
if (isset($_GET['delete'])) {
    $movie_id = (int)$_GET['delete'];
    
    // First get the poster path to delete the file
    $stmt = $conn->prepare("SELECT poster_img FROM movies WHERE movie_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header('Location: movies.php');
        exit;
    }
    
    $stmt->bind_param("i", $movie_id);
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header('Location: movies.php');
        exit;
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        $_SESSION['error'] = "Database error: Failed to get results";
        header('Location: movies.php');
        exit;
    }
    
    $movie = $result->fetch_assoc();
    if ($movie && isset($movie['poster_img']) && $movie['poster_img'] && file_exists('../' . $movie['poster_img'])) {
        unlink('../' . $movie['poster_img']); // Delete the poster file
    }
    
    // Then delete the movie record
    $stmt = $conn->prepare("DELETE FROM movies WHERE movie_id = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header('Location: movies.php');
        exit;
    }
    
    $stmt->bind_param("i", $movie_id);
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Database error: " . $stmt->error;
        header('Location: movies.php');
        exit;
    }
    
    $_SESSION['message'] = "Movie deleted successfully";
    header('Location: movies.php');
    exit;
}

// Handle form submission for adding/editing movies
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : 0;
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $duration = (int)$_POST['duration'];
    $release_date = $conn->real_escape_string($_POST['release_date']);
    $age_rating = $conn->real_escape_string($_POST['age_rating']);
    $director = $conn->real_escape_string($_POST['director']);
    $cast = $conn->real_escape_string($_POST['cast']);
    $trailer_url = $conn->real_escape_string($_POST['trailer_url']);
    $genre = $conn->real_escape_string($_POST['genre']);
    $language = $conn->real_escape_string($_POST['language']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle file upload
    $poster_path = '';
    $upload_dir = '../uploads/posters/';
    $db_upload_dir = 'uploads/posters/';
    
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == UPLOAD_ERR_OK) {
        $original_filename = basename($_FILES['poster']['name']);
        
        // Sanitize filename (remove special characters)
        $sanitized_filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $original_filename);
        $target_path = $upload_dir . $sanitized_filename;
        $db_target_path = $db_upload_dir . $sanitized_filename;
        
        // Check if file already exists, if so, append a number
        $counter = 1;
        while (file_exists($target_path)) {
            $info = pathinfo($sanitized_filename);
            $target_path = $upload_dir . $info['filename'] . '_' . $counter . '.' . $info['extension'];
            $db_target_path = $db_upload_dir . $info['filename'] . '_' . $counter . '.' . $info['extension'];
            $counter++;
        }
        
        // Validate file type and size
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_ext = strtolower(pathinfo($sanitized_filename, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG, WEBP, and GIF are allowed.";
            header('Location: movies.php');
            exit;
        }
        
        if ($_FILES['poster']['size'] > $max_size) {
            $_SESSION['error'] = "File size exceeds maximum limit of 5MB";
            header('Location: movies.php');
            exit;
        }
        
        // Delete old poster if exists (for updates)
        if ($movie_id > 0) {
            $stmt = $conn->prepare("SELECT poster_img FROM movies WHERE movie_id = ?");
            $stmt->bind_param("i", $movie_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existing = $result->fetch_assoc();
            
            if ($existing && isset($existing['poster_img']) && $existing['poster_img'] && file_exists('../' . $existing['poster_img'])) {
                unlink('../' . $existing['poster_img']);
            }
        }
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $target_path)) {
            $poster_path = $db_target_path; // Store without "../" prefix
        } else {
            $_SESSION['error'] = "Failed to upload poster image";
            header('Location: movies.php');
            exit;
        }
    } elseif ($movie_id > 0) {
        // For updates, keep existing poster if no new one is uploaded
        $stmt = $conn->prepare("SELECT poster_img FROM movies WHERE movie_id = ?");
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        
        if ($existing && isset($existing['poster_img'])) {
            $poster_path = $existing['poster_img'];
        }
    }
    
    // For new movies, require a poster
    if ($movie_id == 0 && empty($poster_path)) {
        $_SESSION['error'] = "Poster image is required for new movies";
        header('Location: movies.php');
        exit;
    }
    
    if ($movie_id > 0) {
        // Update existing movie
        $stmt = $conn->prepare("UPDATE movies SET 
            title = ?, description = ?, duration = ?, release_date = ?, 
            age_rating = ?, director = ?, cast = ?, poster_img = ?, 
            trailer_url = ?, genre = ?, language = ?, is_featured = ?
            WHERE movie_id = ?");
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header('Location: movies.php');
            exit;
        }
        
        $stmt->bind_param("ssisssssssssi", 
            $title, $description, $duration, $release_date, 
            $age_rating, $director, $cast, $poster_path, 
            $trailer_url, $genre, $language, $is_featured, $movie_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Movie updated successfully";
        } else {
            $_SESSION['error'] = "Database error: " . $stmt->error;
        }
    } else {
        // Insert new movie
        $stmt = $conn->prepare("INSERT INTO movies (
            title, description, duration, release_date, 
            age_rating, director, cast, poster_img, 
            trailer_url, genre, language, is_featured
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header('Location: movies.php');
            exit;
        }
        
        $stmt->bind_param("ssisssssssss", 
            $title, $description, $duration, $release_date, 
            $age_rating, $director, $cast, $poster_path, 
            $trailer_url, $genre, $language, $is_featured);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Movie added successfully";
        } else {
            $_SESSION['error'] = "Database error: " . $stmt->error;
        }
    }
    
    header('Location: movies.php');
    exit;
}

// Get all movies
$movies = $conn->query("SELECT * FROM movies ORDER BY release_date DESC");

// Get movie for editing if ID is provided
$edit_movie = null;
if (isset($_GET['edit'])) {
    $movie_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_movie = $result->fetch_assoc();
}

// Include header
include __DIR__ . '/header.php';
?>


<div class="admin-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1 class="admin-title">Manage Movies</h1>
            <button onclick="document.getElementById('movie-form').style.display='block'" 
                    class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Movie
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
        
        <!-- Movie Form (hidden by default) -->
        <div id="movie-form" class="movie-form" style="<?= $edit_movie ? 'display:block' : 'display:none' ?>">
            <h2><?= $edit_movie ? 'Edit Movie' : 'Add New Movie' ?></h2>
            <form method="POST" action="movies.php" enctype="multipart/form-data">
                <?php if ($edit_movie): ?>
                    <input type="hidden" name="movie_id" value="<?= $edit_movie['movie_id'] ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" class="form-control" 
                               value="<?= $edit_movie ? $edit_movie['duration'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="release_date">Release Date</label>
                        <input type="date" id="release_date" name="release_date" class="form-control" 
                               value="<?= $edit_movie ? $edit_movie['release_date'] : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="age_rating">Age Rating</label>
                        <select id="age_rating" name="age_rating" class="form-control" required>
                            <option value="G" <?= $edit_movie && $edit_movie['age_rating'] == 'G' ? 'selected' : '' ?>>G</option>
                            <option value="PG" <?= $edit_movie && $edit_movie['age_rating'] == 'PG' ? 'selected' : '' ?>>PG</option>
                            <option value="PG-13" <?= $edit_movie && $edit_movie['age_rating'] == 'PG-13' ? 'selected' : '' ?>>PG-13</option>
                            <option value="R" <?= $edit_movie && $edit_movie['age_rating'] == 'R' ? 'selected' : '' ?>>R</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="director">Director</label>
                        <input type="text" id="director" name="director" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['director']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="cast">Cast</label>
                        <input type="text" id="cast" name="cast" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['cast']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="poster">Movie Poster</label>
                        <input type="file" id="poster" name="poster" class="form-control" accept="image/*" <?= !$edit_movie ? 'required' : '' ?>>
                        <?php if ($edit_movie && isset($edit_movie['poster_img']) && !empty($edit_movie['poster_img'])): ?>
                            <div class="current-poster">
                                <small>Current Poster:</small>
                                <img src="../<?= htmlspecialchars($edit_movie['poster_img']) ?>" 
                                    alt="Current poster" 
                                    style="max-width: 100px; display: block; margin-top: 5px;"
                                    onerror="this.style.display='none'">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="trailer_url">Trailer URL</label>
                        <input type="text" id="trailer_url" name="trailer_url" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['trailer_url']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="genre">Genre</label>
                        <input type="text" id="genre" name="genre" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['genre']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="language">Language</label>
                        <input type="text" id="language" name="language" class="form-control" 
                               value="<?= $edit_movie ? htmlspecialchars($edit_movie['language']) : 'English' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" value="1" 
                                <?= $edit_movie && $edit_movie['is_featured'] ? 'checked' : '' ?>>
                            Featured Movie
                        </label>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" required><?= 
                            $edit_movie ? htmlspecialchars($edit_movie['description']) : '' 
                        ?></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" 
                            onclick="document.getElementById('movie-form').style.display='none'">
                                Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $edit_movie ? 'Update Movie' : 'Add Movie' ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Movies List -->
        <table class="movie-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Release Date</th>
                    <th>Rating</th>
                    <th>Genre</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($movie = $movies->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (isset($movie['poster_img']) && !empty($movie['poster_img'])): ?>
                            <img src="../<?= htmlspecialchars($movie['poster_img']) ?>" 
                                alt="<?= htmlspecialchars($movie['title']) ?>" 
                                style="max-width: 60px; max-height: 90px;"
                                onerror="this.style.display='none'">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($movie['title']) ?></td>
                    <td><?= floor($movie['duration']/60) ?>h <?= $movie['duration']%60 ?>m</td>
                    <td><?= date('M j, Y', strtotime($movie['release_date'])) ?></td>
                    <td><?= htmlspecialchars($movie['age_rating']) ?></td>
                    <td><?= htmlspecialchars($movie['genre']) ?></td>
                    <td>
                        <?php if ($movie['is_featured']): ?>
                            <span class="featured-badge">Featured</span>
                        <?php endif; ?>
                    </td>
                    <td class="movie-actions">
                        <a href="movies.php?edit=<?= $movie['movie_id'] ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="movies.php?delete=<?= $movie['movie_id'] ?>" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this movie?')">
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
            document.getElementById('movie-form').style.display = 'block';
        }
    });
</script>

<style>
    /* Error message styling */
    .error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        border: 1px solid #f5c6cb;
    }
    
    /* Current poster preview */
    .current-poster {
        margin-top: 10px;
    }
    
    .current-poster img {
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    
    /* Movie Table Specific Styles */
    .movie-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-top: 20px;
        font-size: 0.9rem;
    }
    
    .movie-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        color: #343a40;
        border-bottom: 2px solid #dee2e6;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .movie-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }
    
    .movie-table tr:last-child td {
        border-bottom: none;
    }
    
    .movie-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .movie-actions {
        display: flex;
        gap: 8px;
        white-space: nowrap;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
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
        font-size: 0.8rem;
    }
    
    .featured-badge {
        display: inline-block;
        padding: 3px 8px;
        background-color: #e50914;
        color: white;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    /* Responsive Table */
    @media (max-width: 992px) {
        .movie-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        .movie-actions {
            white-space: nowrap;
        }
    }
    
    @media (max-width: 576px) {
        .movie-table td, 
        .movie-table th {
            padding: 8px 10px;
        }
        
        .btn {
            padding: 4px 8px;
            font-size: 0.8rem;
        }
        
        .btn i {
            margin-right: 3px;
        }
    }
</style>
</body>
</html>