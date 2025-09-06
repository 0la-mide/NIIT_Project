<?php
session_start();
require_once 'config.php';

// Get all cinemas with their amenities
$cinemas = [];
try {
    $result = $conn->query("SELECT * FROM cinemas ORDER BY name ASC");
    if ($result) {
        $cinemas = $result->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $error = "Error loading cinemas. Please try again later.";
    error_log("Cinema loading error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Cinemas - Anvora Cinemas</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: rgb(7, 140, 206);
            --primary-dark: rgb(6, 89, 131);
            --secondary: #000000;
            --light: #f5f5f5;
            --dark: #0a0a0a;
            --text-light: #ccc;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--dark);
            color: white;
            line-height: 1.6;
        }
        
        .cinemas-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 20px 80px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .cinemas-title {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .cinemas-header p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            color: var(--text-light);
        }
        
        .cinemas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 50px;
        }
        
        .cinema-card {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .cinema-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }
        
        .cinema-image {
            flex: 0 0 40%;
            min-height: 300px;
            background-size: cover;
            background-position: center;
            background-color: #333; /* Fallback color */
        }
        
        .cinema-details {
            flex: 1;
            padding: 30px;
            position: relative;
        }
        
        .cinema-name {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .cinema-location {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: var(--text-light);
        }
        
        .cinema-location i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        .cinema-address {
            margin-bottom: 20px;
            line-height: 1.6;
            color: var(--text-light);
        }
        
        .cinema-contact {
            margin-bottom: 20px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--text-light);
        }
        
        .contact-item i {
            margin-right: 10px;
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        .amenities-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .amenities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .amenity-tag {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            transition: background-color 0.3s;
        }
        
        .amenity-tag:hover {
            background-color: rgba(7, 140, 206, 0.3);
        }
        
        .amenity-tag i {
            margin-right: 8px;
            color: var(--primary);
            font-size: 0.9rem;
        }
        
        .view-showtimes {
            display: inline-flex;
            align-items: center;
            padding: 12px 25px;
            background-color: var(--primary-dark);
            color: #000;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }
        
        .view-showtimes:hover {
            background-color: #fff;
            transform: translateY(-2px);
        }
        
        .view-showtimes i {
            margin-left: 8px;
            transition: transform 0.3s;
        }
        
        .view-showtimes:hover i {
            transform: translateX(3px);
        }
        
        .error-message, .no-cinemas {
            background: rgba(255, 0, 0, 0.1);
            border-left: 4px solid #ff4d4d;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
            color: #ff6b6b;
        }
        
        .no-cinemas {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid var(--primary);
            color: var(--text-light);
            text-align: center;
            padding: 30px;
        }
        
        /* Responsive styles */
        @media (max-width: 968px) {
            .cinema-card {
                flex-direction: column;
            }
            
            .cinema-image {
                flex: 0 0 250px;
                width: 100%;
            }
            
            .cinemas-title {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .cinemas-header {
                padding: 100px 20px 60px;
            }
            
            .cinema-details {
                padding: 20px;
            }
            
            .cinema-name {
                font-size: 1.5rem;
            }
            
            .amenities-list {
                gap: 8px;
            }
            
            .amenity-tag {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .cinemas-title {
                font-size: 1.8rem;
            }
            
            .cinemas-header p {
                font-size: 1rem;
            }
            
            .cinema-image {
                flex: 0 0 200px;
            }
            
            .amenities-list {
                flex-direction: column;
                gap: 10px;
            }
            
            .amenity-tag {
                width: 100%;
            }
        }
    </style>
</head>
<?php include 'header.php'; ?>
    
    <header class="cinemas-header">
        <h1 class="cinemas-title">Our Cinema Locations</h1>
        <p>Experience premium movie watching at our state-of-the-art theaters</p>
    </header>
    
    <div class="cinemas-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (empty($cinemas)): ?>
            <div class="no-cinemas">
                <p>No cinemas available at the moment. Please check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach ($cinemas as $cinema): 
                $amenities = json_decode($cinema['amenities'], true);
                
                //image path handling
                $image_path = '';
                if (!empty($cinema['image'])) {
                    $image_path = 'uploads/cinemas/' . $cinema['image'];
                } else {
                    $image_path = 'assets/cinemas/default.jpg';
                }
            ?>
                <div class="cinema-card">
                    <!-- Correct image path usage -->
                    <div class="cinema-image" style="background-image: url('<?= htmlspecialchars($image_path) ?>')"
                         data-fallback="assets/cinemas/default.jpg">
                    </div>
                    <div class="cinema-details">
                        <h2 class="cinema-name"><?= htmlspecialchars($cinema['name']) ?></h2>
                        
                        <div class="cinema-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($cinema['location']) ?></span>
                        </div>
                        
                        <p class="cinema-address"><?= htmlspecialchars($cinema['address']) ?></p>
                        
                        <div class="cinema-contact">
                            <?php if (!empty($cinema['contact_phone'])): ?>
                                <div class="contact-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?= htmlspecialchars($cinema['contact_phone']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($cinema['contact_email'])): ?>
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <span><?= htmlspecialchars($cinema['contact_email']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($amenities)): ?>
                            <div class="cinema-amenities">
                                <h4 class="amenities-title">Amenities & Services</h4>
                                <div class="amenities-list">
                                    <?php foreach ($amenities as $amenity): 
                                        $icon = match($amenity) {
                                            'Food Court' => 'utensils',
                                            'Wheelchair Access' => 'wheelchair',
                                            'VIP Lounge' => 'couch',
                                            'Bar' => 'glass-martini-alt',
                                            'Arcade' => 'gamepad',
                                            'Baby Changing' => 'baby',
                                            default => 'check-circle'
                                        };
                                    ?>
                                        <span class="amenity-tag">
                                            <i class="fas fa-<?= $icon ?>"></i>
                                            <?= htmlspecialchars($amenity) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!--<a href="showtimes.php?cinema=<?= $cinema['cinema_id'] ?>" class="view-showtimes">
                            View Showtimes <i class="fas fa-chevron-right"></i>
                        </a>-->
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Add image error handling
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.cinema-image').forEach(imgEl => {
                // Test if image loads
                const imgUrl = imgEl.style.backgroundImage.replace(/url\(['"]?(.*?)['"]?\)/, '$1');
                const testImg = new Image();
                testImg.src = imgUrl;
                testImg.onerror = () => {
                    const fallback = imgEl.getAttribute('data-fallback');
                    if (fallback) {
                        imgEl.style.backgroundImage = `url('${fallback}')`;
                    }
                };
            });
        });
    </script>
</body>
</html>