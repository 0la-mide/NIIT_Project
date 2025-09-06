<?php
session_start();
require_once __DIR__ . '/config.php';
include __DIR__ . '/header.php';

// Function to clean image paths
function cleanImagePath($path) {
    return htmlspecialchars(str_replace(['../', 'fhiles'], ['', 'files'], $path ?? 'assets/posters/default.jpg'));
}

// Check if movie_id is set
if (!isset($_GET['movie'])) {
    die("Movie ID is required.");
}

$movieId = $_GET['movie'];

// Current datetime
$current_datetime = date('Y-m-d H:i:s');

// Fetch only showtimes that haven't started yet
$sql = "SELECT 
            s.showtime_id, 
            s.movie_id, 
            s.auditorium_id, 
            s.start_time, 
            s.end_time, 
            s.price, 
            s.format, 
            s.is_special_event,
            a.name AS auditorium_name,
            c.name AS cinema_name,
            c.location AS cinema_location
        FROM showtimes s
        JOIN auditoriums a ON s.auditorium_id = a.auditorium_id
        JOIN cinemas c ON a.cinema_id = c.cinema_id
        WHERE s.movie_id = ?
        AND s.start_time > NOW()  -- only future showtimes";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$result = $stmt->get_result();


if (!$result) {
    die("Database error: " . $conn->error);
}

$showtimes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $showtimes[] = $row;
    }
}

// Fetch movie details
$sql = "SELECT title, description, poster_img, trailer_url FROM movies WHERE movie_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieId);
$stmt->execute();
$movieResult = $stmt->get_result();

if (!$movieResult) {
    die("Database error: " . $conn->error);
}

$movie = $movieResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anvora Cinemas - Showtimes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <style>
        :root {
            --primary: rgb(7, 140, 206);
            --secondary: #000000;
            --light: #f5f5f5;
            --dark: #222222;
            --shadow: 0 5px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Movie Details Section */
        .movie-details {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0;
        }
        
        .movie-details .container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: flex-start;
        }
        
        .movie-poster {
            flex: 0 0 300px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .movie-poster img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .movie-info {
            flex: 1;
            min-width: 300px;
        }
        
        .movie-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: white;
        }
        
        .movie-description {
            margin-bottom: 30px;
            font-size: 1.1rem;
            line-height: 1.8;
            max-width: 800px;
        }
        
        .movie-actions {
            display: flex;
            gap: 20px;
        }
        
        /* Showtimes Section */
        .showtimes-section {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            font-size: 2rem;
            color: var(--secondary);
        }
        
        .section-title h2 {
            display: inline-block;
            background: white;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .showtimes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .showtime-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        
        .showtime-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .showtime-info {
            padding: 25px;
            flex: 1;
        }
        
        .showtime-info h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--secondary);
        }
        
        .showtime-info p {
            margin-bottom: 10px;
            color: #555;
        }
        
        .showtime-actions {
            padding: 0 25px 25px;
        }
        
        /* Location Info */
        .location-info {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }
        
        .location-details {
            display: flex;
            flex-direction: column;
        }
        
        .cinema-name {
            font-weight: bold;
            color: var(--dark);
        }
        
        .cinema-location {
            font-size: 0.9em;
            color: #666;
            font-style: italic;
        }
        
        .auditorium-name {
            font-size: 0.9em;
            color: #666;
            margin-left: 20px;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .btn-trailer {
            background-color: var(--primary);
            color: white;
            border: 2px solid var(--primary);
        }
        
        .btn-trailer:hover {
            background-color: transparent;
            color: var(--primary);
        }
        
        .btn-tickets {
            background-color: var(--primary);
            color: white;
            width: 100%;
            display: block;
            text-align: center;
        }
        
        .btn-tickets:hover {
            background-color: #0568a7;
        }
        
        /* Special Event */
        .special-event {
            color: #e67e22;
            font-weight: bold;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .movie-details .container {
                flex-direction: column;
                align-items: center;
            }
            
            .movie-poster {
                flex: 0 0 auto;
                width: 100%;
                max-width: 300px;
            }
            
            .movie-title {
                font-size: 2rem;
            }
            
            .showtimes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body><br><br>
    <section class="movie-details">
        <div class="container">
            <div class="movie-poster">
                <img src="<?= cleanImagePath($movie['poster_img']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" loading="lazy">
            </div>
            <div class="movie-info">
                <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>
                <p class="movie-description"><?= htmlspecialchars($movie['description']) ?></p>
                <div class="movie-actions">
                    <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" class="btn btn-trailer" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-play"></i> Watch Trailer
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="showtimes-section">
        <div class="container">
            <h2 class="section-title">Showtimes for <?= htmlspecialchars($movie['title']) ?></h2>
            <div class="showtimes-grid">
                <?php if (empty($showtimes)): ?>
                    <p class="no-showtimes">No showtimes available.</p>
                <?php else: ?>
                    <?php foreach ($showtimes as $showtime): ?>
                        <div class="showtime-card">
                            <div class="showtime-info">
                                <h3><i class="far fa-clock"></i> <?= date('g:i A', strtotime($showtime['start_time'])) ?> - <?= date('g:i A', strtotime($showtime['end_time'])) ?></h3>
                                <p><i class="fas fa-calendar"></i> Date: <?= date('F j, Y', strtotime($showtime['start_time'])) ?></p>
                                <p class="location-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="location-details">
                                        <span class="cinema-name"><?= htmlspecialchars($showtime['cinema_name']) ?></span>
                                        <span class="cinema-location"><?= htmlspecialchars($showtime['cinema_location']) ?></span>
                                    </span>
                                </p>
                                <p class="auditorium-name">
                                    Auditorium: <?= htmlspecialchars($showtime['auditorium_name']) ?>
                                </p>
                                <p><i class="fas fa-ticket-alt"></i> Price: ₦<?= number_format($showtime['price'], 2) ?></p>
                                <p><i class="fas fa-film"></i> Format: <?= htmlspecialchars($showtime['format']) ?></p>
                                <?php if ($showtime['is_special_event']): ?>
                                    <p class="special-event"><i class="fas fa-star"></i> Special Event</p>
                                <?php endif; ?>
                            </div>
                            <div class="showtime-actions">
                                <a href="booking.php?showtime_id=<?= $showtime['showtime_id'] ?>" class="btn btn-tickets">
                                    <i class="fas fa-shopping-cart"></i> Buy Tickets
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Update active tab button
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active tab content
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Reminder button functionality
            document.querySelectorAll('.reminder-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const movieId = this.getAttribute('data-movie');
                    alert('We will remind you when this movie is available! Movie ID: ' + movieId);
                });
            });
        });
    </script>
</body>
</html>
<?php include 'footer.php'; ?>