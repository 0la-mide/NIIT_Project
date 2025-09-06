<?php
session_start();
require_once __DIR__ . '/config.php';
include __DIR__ . '/header.php';

// Function to clean image paths
function cleanImagePath($path) {
    return htmlspecialchars(str_replace(['../', 'fhiles'], ['', 'files'], $path ?? 'assets/posters/default.jpg'));
}

$sql = "SELECT movie_id, title, description, poster_img, trailer_url FROM movies ORDER BY RAND() LIMIT 10";
$result = $conn->query($sql);

if (!$result) {
    die("Database error: " . $conn->error);
}

$movies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
}

$sql = "UPDATE movies SET poster_img = REPLACE(poster_img, '../', '')";
?>

<br><br>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anvora Cinemas - Movie Theater</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
</head>
<section class="movies-slider">
    <?php if (empty($movies)): ?>
        <p class="no-movies">No movies available.</p>
    <?php else: ?>
        <div class="slider-container">
            <button class="slider-nav prev" aria-label="Previous slide">❮</button>
            <div class="slider-wrapper">
                <div class="slider">
                    <?php foreach ($movies as $movie): ?>
                        <div class="slide">
                            <a href="movie_details.php?id=<?= $movie['movie_id'] ?>" class="movie-poster-link">
                                <div class="movie-poster">
                                    <img 
                                        src="<?= cleanImagePath($movie['poster_img']) ?>" 
                                        alt="<?= htmlspecialchars($movie['title']) ?>"
                                        loading="lazy"
                                        onerror="this.src='assets/posters/default.jpg'"
                                    />
                                    <div class="movie-overlay">
                                        <span class="view-details">View Details</span>
                                    </div>
                                </div>
                            </a>
                            <div class="movie-info">
                                <h3><a href="movie_details.php?id=<?= $movie['movie_id'] ?>"><?= htmlspecialchars($movie['title']) ?></a></h3>
                                <p class="movie-description"><?= htmlspecialchars($movie['description']) ?></p>
                                <div class="movie-actions">
                                    <a href="showtimes.php?movie=<?= $movie['movie_id'] ?>" class="btn btn-tickets">Buy Tickets</a>
                                    <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" class="btn btn-trailer" target="_blank" rel="noopener noreferrer">Watch Trailer</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="slider-nav next" aria-label="Next slide">❯</button>
        </div>
    <?php endif; ?>
</section>

<?php
require_once 'config.php';

// Get current date and time
$current_datetime = date('Y-m-d H:i:s');

// Fetch movies data
try {
    // Get latest 5 movies for the slider
    $latest_movies = $conn->query("
        SELECT * FROM movies 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    // Now Showing movies (release date <= today with available showtimes)
    $now_showing_query = $conn->prepare("
        SELECT m.movie_id, m.title, m.description, m.poster_img, m.trailer_url, 
               m.age_rating, m.genre, m.duration, m.is_featured, m.release_date,
               (SELECT COUNT(*) FROM showtimes s 
                WHERE s.movie_id = m.movie_id 
                AND s.start_time >= ?) AS showtimes_count
        FROM movies m
        WHERE m.release_date <= CURDATE()
        ORDER BY m.release_date DESC
        LIMIT 8
    ");
    $now_showing_query->bind_param("s", $current_datetime);
    $now_showing_query->execute();
    $now_showing = $now_showing_query->get_result()->fetch_all(MYSQLI_ASSOC);

    // Coming Soon movies (release date > today)
    $coming_soon_query = $conn->prepare("
        SELECT movie_id, title, description, poster_img, trailer_url, 
               age_rating, genre, duration, release_date
        FROM movies 
        WHERE release_date > CURDATE()
        ORDER BY release_date ASC
        LIMIT 8
    ");
    $coming_soon_query->execute();
    $coming_soon = $coming_soon_query->get_result()->fetch_all(MYSQLI_ASSOC);

    // For quick booking widget
    $quick_book_movies = $conn->query("SELECT * FROM movies WHERE release_date <= NOW() ORDER BY title LIMIT 5");
    $quick_book_cinemas = $conn->query("SELECT * FROM cinemas ORDER BY name");
    $featured_cinemas = $conn->query("SELECT * FROM cinemas ORDER BY name LIMIT 4");

} catch (mysqli_sql_exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

</section>
    <!-- Tabbed Movie Section -->
    <section class="tabbed-movie-section">
        <div class="container">
            <div class="tabs">
                <button class="tab-btn active" data-tab="now-showing">Now Showing</button>
                <button class="tab-btn" data-tab="coming-soon">Coming Soon</button>
            </div>
            
            <div id="now-showing" class="tab-content active">
                <div class="movie-grid">
                    <?php if (empty($now_showing)): ?>
                        <div class="no-movies">
                            <p>No movies currently showing. Check back soon!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($now_showing as $movie): ?>
                            <div class="movie-card">
                                <div class="movie-poster">
                                    <img src="<?= cleanImagePath($movie['poster_img']) ?>" 
                                         alt="<?= htmlspecialchars($movie['title']) ?>"
                                         onerror="this.src='assets/posters/default.jpg'">
                                    <?php if ($movie['is_featured']): ?>
                                        <span class="featured-badge">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="movie-info">
                                    <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                                    <div class="movie-meta">
                                        <span class="age-rating <?= strtolower($movie['age_rating']) ?>">
                                            <?= htmlspecialchars($movie['age_rating']) ?>
                                        </span>
                                        <span class="genre"><?= htmlspecialchars($movie['genre']) ?></span>
                                    </div>
                                    <div class="movie-duration">
                                        <i class="fas fa-clock"></i>
                                        <?= floor($movie['duration']/60) ?>h <?= $movie['duration']%60 ?>m
                                    </div>
                                    <?php if ($movie['showtimes_count'] > 0): ?>
                                        <a href="showtimes.php?movie=<?= $movie['movie_id'] ?>" class="btn btn-primary">
                                            View Showtimes
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-disabled" disabled>No showtimes</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="coming-soon" class="tab-content">
                <div class="movie-grid">
                    <?php if (empty($coming_soon)): ?>
                        <div class="no-movies">
                            <p>No upcoming movies announced yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($coming_soon as $movie): ?>
                            <div class="movie-card">
                                <div class="movie-poster">
                                    <img src="<?= cleanImagePath($movie['poster_img']) ?>" 
                                         alt="<?= htmlspecialchars($movie['title']) ?>"
                                         onerror="this.src='assets/posters/default.jpg'">
                                    <div class="release-date">
                                        Coming <?= date('M j', strtotime($movie['release_date'])) ?>
                                    </div>
                                </div>
                                <div class="movie-info">
                                    <h3 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h3>
                                    <div class="movie-meta">
                                        <span class="age-rating <?= strtolower($movie['age_rating']) ?>">
                                            <?= htmlspecialchars($movie['age_rating']) ?>
                                        </span>
                                        <span class="genre"><?= htmlspecialchars($movie['genre']) ?></span>
                                    </div>
                                    <div class="movie-actions">
                                        <?php if (!empty($movie['trailer_url'])): ?>
                                            <a href="<?= htmlspecialchars($movie['trailer_url']) ?>" class="btn btn-outline" target="_blank">
                                                <i class="fas fa-play"></i> Trailer
                                            </a>
                                        <?php endif; ?>
                                        <!--<button class="btn btn-outline reminder-btn" 
                                                data-movie="<?= $movie['movie_id'] ?>">
                                            <i class="fas fa-bell"></i> Remind
                                        </button>-->
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Cinemas Section -->
    <section class="cinemas-section">
        <div class="container">
            <h2 class="section-title">Our Cinemas</h2>
            <div class="cinema-grid">
                <?php while ($cinema = $featured_cinemas->fetch_assoc()): ?>
                    <?php 
                    $imagePath = !empty($cinema['image']) ? 'uploads/cinemas/' . $cinema['image'] : 'assets/cinemas/default.jpg';
                    ?>
                <div class="cinema-card">
                    <div class="cinema-image-container">
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($cinema['name']) ?>" class="cinema-image">
                        <div class="cinema-overlay">
                            <!--<a href="cinema-details.php?id=<?= $cinema['cinema_id'] ?>" class="btn btn-explore">
                                <i class="fas fa-search"></i> Explore
                            </a>-->
                        </div>
                    </div>
                    <div class="cinema-info">
                        <h3><?= htmlspecialchars($cinema['name']) ?></h3>
                        <div class="cinema-meta">
                            <span class="location">
                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($cinema['location']) ?>
                            </span>
                            <?php if (!empty($cinema['contact_phone'])): ?>
                                <span class="phone">
                                    <i class="fas fa-phone"></i> <?= htmlspecialchars($cinema['contact_phone']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <!--<a href="cinema-details.php?id=<?= $cinema['cinema_id'] ?>" class="btn btn-primary">
                            View Details
                        </a>-->
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script>        
        // Tab functionality
        $(document).ready(function() {
            $('.tab-btn').click(function() {
                const tabId = $(this).data('tab');
                
                // Update active tab button
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                // Show corresponding content
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });
        });
    </script>

<?php include 'footer.php'; ?>

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
});
</script>

<style>
:root {
    --primary: rgb(7, 140, 206);
    --primary-dark: rgb(6, 89, 131);
    --secondary: #000000;
    --light: #f5f5f5;
    --dark: #0a0a0a;
    --text-light: #ccc;
    --bg-card: rgba(255, 255, 255, 0.1);
}

body {
    font-family: 'Arial', sans-serif;
    background-color: var(--dark);
    color: white;
    line-height: 1.6;
}

/* Movie Slider Styles */
.movies-slider {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    position: relative;
}

.no-movies {
    text-align: center;
    color: var(--text-light);
    font-size: 1.2rem;
    padding: 2rem;
}

.slider-container {
    position: relative;
    display: flex;
    align-items: center;
}

.slider-nav {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    z-index: 10;
    opacity: 0.8;
    transition: all 0.3s ease;
    transform: translateY(-50%);
    top: 50%;
}

.slider-nav:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
}

.slider-nav.prev {
    left: -20px;
}

.slider-nav.next {
    right: -20px;
}

.slider-wrapper {
    width: 100%;
    overflow: hidden;
    position: relative;
}

.slider {
    display: flex;
    transition: transform 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
    padding: 1rem 0;
    scroll-behavior: smooth;
}

.slide {
    flex: 0 0 calc(33.333% - 30px);
    margin: 0 15px;
    background: var(--bg-card);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    position: relative;
}

.slide:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
}

.movie-poster-link {
    display: block;
    position: relative;
    height: 0;
    padding-bottom: 150%;
    overflow: hidden;
}

.movie-poster {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.movie-poster img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.movie-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.view-details {
    color: white;
    font-weight: bold;
    background: var(--primary);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.movie-poster-link:hover .movie-overlay {
    opacity: 1;
}

.movie-poster-link:hover .view-details {
    transform: translateY(0);
}

.movie-poster-link:hover img {
    transform: scale(1.05);
}

.movie-info {
    padding: 1.25rem;
}

.movie-info h3 {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    line-height: 1.3;
    color: white;
}

.movie-info h3 a {
    color: var(--light);
    text-decoration: none;
    transition: all 0.3s ease;
}

.movie-info h3 a:hover {
    color: var(--primary);
}

.movie-description {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 3.6rem;
    line-height: 1.2;
}

.movie-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.btn {
    padding: 0.5rem 0.75rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    text-align: center;
    flex: 1;
    transition: all 0.3s ease;
    font-size: 0.8rem;
}

.btn-tickets {
    background-color: transparent;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-tickets:hover {
    background-color: var(--primary);
    color: white;
}

.btn-trailer {
    background-color: var(--primary);
    color: white;
    border: 2px solid var(--primary);
}

.btn-trailer:hover {    
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

/* Tabbed Movie Section */
.tabbed-movie-section {
    padding: 60px 0;
    background-color: var(--dark);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.tabs {
    display: flex;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 2rem;
    justify-content: center;
}

.tab-btn {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-light);
    position: relative;
    transition: all 0.3s ease;
}

.tab-btn.active {
    color: var(--primary);
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.movie-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}

.movie-card {
    background: var(--bg-card);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.movie-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
}

.movie-poster {
    position: relative;
    height: 0;
    padding-bottom: 150%;
    overflow: hidden;
}

.movie-poster img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.movie-card:hover .movie-poster img {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: var(--primary);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}

.release-date {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background-color: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.movie-info {
    padding: 1.25rem;
}

.movie-title {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    line-height: 1.3;
    color: white;
}

.movie-meta {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}

.age-rating {
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-weight: bold;
    color: white;
}

.age-rating.g, .age-rating.pg {
    background-color: #2ecc71;
}

.age-rating.pg-13 {
    background-color: #f39c12;
}

.age-rating.r, .age-rating.nc-17 {
    background-color: #e74c3c;
}

.genre {
    color: var(--text-light);
}

.movie-duration {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.movie-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: 2px solid var(--primary);
    width: 100%;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
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

.btn-disabled {
    background-color: rgba(221, 221, 221, 0.2);
    color: rgba(153, 153, 153, 0.5);
    border: 2px solid rgba(221, 221, 221, 0.2);
    cursor: not-allowed;
    width: 100%;
}

/* Cinemas Section */
.cinemas-section {
    padding: 60px 0;
    background-color: var(--dark);
}

.section-title {
    text-align: center;
    margin-bottom: 40px;
    font-size: 2.5rem;
    color: var(--primary);
    position: relative;
}

.section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background-color: var(--primary);
    margin: 15px auto 0;
}

.cinema-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;
}

.cinema-card {
    background: var(--bg-card);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.cinema-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
}

.cinema-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.cinema-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

.cinema-card:hover .cinema-image {
    transform: scale(1.05);
}

.cinema-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
}

.cinema-card:hover .cinema-overlay {
    opacity: 1;
}

.btn-explore {
    background-color: var(--primary);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 30px;
    font-weight: 600;
    border: 2px solid white;
    transition: all 0.3s ease;
}

.btn-explore:hover {
    background-color: var(--primary-dark);
    transform: scale(1.05);
}

.cinema-info {
    padding: 1.25rem;
}

.cinema-info h3 {
    margin: 0 0 0.5rem;
    font-size: 1.2rem;
    color: white;
}

.cinema-meta {
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: var(--text-light);
}

.cinema-meta span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .movie-grid,
    .cinema-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .movie-grid,
    .cinema-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
    
    .slide {
        flex: 0 0 calc(50% - 30px);
    }
}

@media (max-width: 768px) {
    .movie-grid,
    .cinema-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .slide {
        flex: 0 0 calc(100% - 30px);
    }
    
    .slider-container {
        padding: 0 2rem;
    }
    
    .slider-nav {
        width: 35px;
        height: 35px;
    }
    
    .slider-nav.prev {
        left: 0;
    }
    
    .slider-nav.next {
        right: 0;
    }
    
    .cinema-image-container {
        height: 180px;
    }
    
    .tabs {
        justify-content: center;
    }
    
    .tab-btn {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .movie-grid,
    .cinema-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .movie-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .movie-info,
    .cinema-info {
        padding: 1rem;
    }
    
    .movie-title {
        font-size: 1rem;
    }
}

@media (max-width: 400px) {
    .movie-grid,
    .cinema-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .movie-info,
    .cinema-info {
        padding: 0.75rem;
    }
    
    .movie-title {
        font-size: 0.9rem;
    }
    
    .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sliderContainer = document.querySelector('.slider-container');
    const sliderWrapper = document.querySelector('.slider-wrapper');
    const slider = document.querySelector('.slider');
    const slides = Array.from(document.querySelectorAll('.slide'));
    const prevBtn = document.querySelector('.slider-nav.prev');
    const nextBtn = document.querySelector('.slider-nav.next');
    
    if (!slider || slides.length === 0) return;
    
    const totalSlides = slides.length;
    let currentIndex = 0;
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;
    let animationID;
    let slideInterval;
    
    // Set initial positions
    slides.forEach((slide, index) => {
        slide.addEventListener('touchstart', touchStart(index));
        slide.addEventListener('touchend', touchEnd);
        slide.addEventListener('touchmove', touchMove);
        
        slide.addEventListener('mousedown', touchStart(index));
        slide.addEventListener('mouseup', touchEnd);
        slide.addEventListener('mouseleave', touchEnd);
        slide.addEventListener('mousemove', touchMove);
    });
    
    // Prevent image drag
    const images = slider.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('dragstart', (e) => e.preventDefault());
    });
    
    // Navigation buttons
    prevBtn.addEventListener('click', goPrev);
    nextBtn.addEventListener('click', goNext);
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') goPrev();
        if (e.key === 'ArrowRight') goNext();
    });
    
    // Auto-slide
    startAutoSlide();
    
    // Handle window resize
    window.addEventListener('resize', setPositionByIndex);
    
    // Functions
    function getPositionX(event) {
        return event.type.includes('mouse') ? event.pageX : event.touches[0].clientX;
    }
    
    function touchStart(index) {
        return function(event) {
            currentIndex = index;
            startPos = getPositionX(event);
            isDragging = true;
            clearInterval(slideInterval);
            
            animationID = requestAnimationFrame(animation);
            slider.classList.add('grabbing');
        };
    }
    
    function touchMove(event) {
        if (isDragging) {
            const currentPosition = getPositionX(event);
            currentTranslate = prevTranslate + currentPosition - startPos;
        }
    }
    
    function touchEnd() {
        if (isDragging) {
            isDragging = false;
            cancelAnimationFrame(animationID);
            slider.classList.remove('grabbing');
            
            const movedBy = currentTranslate - prevTranslate;
            
            if (movedBy < -100 && currentIndex < totalSlides - 1) {
                currentIndex += 1;
            }
            
            if (movedBy > 100 && currentIndex > 0) {
                currentIndex -= 1;
            }
            
            setPositionByIndex();
            startAutoSlide();
        }
    }
    
    function animation() {
        setSliderPosition();
        if (isDragging) requestAnimationFrame(animation);
    }
    
    function setSliderPosition() {
        slider.style.transform = `translateX(${currentTranslate}px)`;
    }
    
    function setPositionByIndex() {
        const slideWidth = slides[0].offsetWidth + 30; // including margin
        currentTranslate = -(currentIndex * slideWidth) + (sliderWrapper.offsetWidth / 2 - slideWidth / 2);
        prevTranslate = currentTranslate;
        setSliderPosition();
        updateActiveSlide();
    }
    
    function updateActiveSlide() {
        slides.forEach((slide, index) => {
            const distance = Math.abs(index - currentIndex);
            
            if (distance === 0) {
                slide.classList.add('center');
                slide.classList.remove('left', 'right');
            } else if (index < currentIndex) {
                slide.classList.add('left');
                slide.classList.remove('center', 'right');
            } else {
                slide.classList.add('right');
                slide.classList.remove('center', 'left');
            }
            
            // Adjust opacity and scale based on distance
            const scale = 1 - (distance * 0.1);
            const opacity = 1 - (distance * 0.3);
            slide.style.transform = `scale(${scale})`;
            slide.style.opacity = opacity > 0.5 ? opacity : 0.5;
        });
    }
    
    function goPrev() {
        if (currentIndex > 0) {
            currentIndex--;
            setPositionByIndex();
            resetAutoSlide();
        }
    }
    
    function goNext() {
        if (currentIndex < totalSlides - 1) {
            currentIndex++;
            setPositionByIndex();
            resetAutoSlide();
        }
    }
    
    function startAutoSlide() {
        slideInterval = setInterval(() => {
            if (currentIndex === totalSlides - 1) {
                currentIndex = 0;
            } else {
                currentIndex++;
            }
            setPositionByIndex();
        }, 5000);
    }
    
    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }
    
    // Initialize
    setPositionByIndex();
});

document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
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

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
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
    
});
</script>