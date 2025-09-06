<?php
session_start();
require_once 'config.php';

// Function to clean image paths
function cleanImagePath($path) {
    return htmlspecialchars(str_replace(['../', 'fhiles'], ['', 'files'], $path ?? 'assets/posters/default.jpg'));
}

// Get movie details
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($movie_id == 0) {
    die("Movie ID is required.");
}

$movie = [];
try {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $movie = $result->fetch_assoc();
    } else {
        die("Movie not found.");
    }
} catch (Exception $e) {
    error_log("Movie details error: " . $e->getMessage());
    die("Database error");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['title']) ?> - Movie Details</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: rgb(7, 140, 206);
            --primary-dark: rgb(6, 89, 131);
            --dark: #221f1f;
            --light: #f5f5f1;
            --gray: #857f7f;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .movie-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        
        .movie-title {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 700;
        }
        
        .movie-subtitle {
            color: var(--gray);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            max-width: 600px;
        }
        
        .movie-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .movie-content {
                grid-template-columns: 1fr;
            }
        }
        
        .movie-poster {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .movie-poster img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .movie-poster:hover img {
            transform: scale(1.03);
        }
        
        .movie-poster-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 1.5rem 1rem 1rem;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .movie-details {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }
        
        .movie-description {
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .movie-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray);
        }
        
        .meta-item i {
            color: var(--primary);
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .social-share {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
        }
        
        .twitter {
            background-color: #1DA1F2;
        }
        
        .facebook {
            background-color: #3b5998;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="movie-header">
            <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>
            <p class="movie-subtitle"><?= htmlspecialchars($movie['tagline'] ?? 'Experience this incredible story in theaters now') ?></p>
        </header>
        
        <div class="movie-content">
            <div class="movie-poster">
                <img src="<?= cleanImagePath($movie['poster_img']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                <div class="movie-poster-overlay">
                    <!--<a href="booking.php?movie_id=<?= $movie['movie_id'] ?>" class="btn btn-primary">
                        <i class="fas fa-ticket-alt"></i> Book Now
                    </a>-->
                </div>
            </div>
            
            <div class="movie-details">
                <h2 class="section-title">About the Movie</h2>
                <p class="movie-description"><?= htmlspecialchars($movie['description']) ?></p>
                
                <div class="movie-meta">
                    <?php if (!empty($movie['duration'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= htmlspecialchars($movie['duration']) ?> min</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($movie['release_date'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?= date('F j, Y', strtotime($movie['release_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($movie['genre'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-film"></i>
                        <span><?= htmlspecialchars($movie['genre']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="action-buttons">
                    <?php if (!empty($movie['trailer_url'])): ?>
                        <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" class="btn btn-outline" target="_blank">
                            <i class="fas fa-play"></i> Watch Trailer
                        </a>
                    <?php endif; ?>
                    
                    <a href="showtimes.php?movie=<?= $movie['movie_id'] ?>" class="btn btn-primary">
                        <i class="fas fa-calendar"></i> View Showtimes
                    </a>
                </div>
                
                <div class="social-share">
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode("Watch " . htmlspecialchars($movie['title']) . " now at Anvora Cinemas!") ?>" 
                       class="social-btn twitter" target="_blank" title="Share on Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer.php?u=<?= urlencode("https://yourwebsite.com/movie_details.php?id=" . $movie['movie_id']) ?>" 
                       class="social-btn facebook" target="_blank" title="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>