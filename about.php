<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Anvora Cinemas</title>
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

        .about-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 120px 20px 80px;
            margin-bottom: 40px;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .about-hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
            color: var(--text-light);
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 50px;
        }

        .about-section {
            margin-bottom: 60px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .section-title h2 {
            font-size: 2rem;
            display: inline-block;
            background: var(--dark);
            padding: 0 20px;
            position: relative;
            z-index: 1;
            color: var(--primary);
        }

        .section-title::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
        }

        .story-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .story-image {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .story-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .story-content h3 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .story-content p {
            line-height: 1.8;
            margin-bottom: 20px;
            color: var(--text-light);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: white;
        }

        .feature-card p {
            color: var(--text-light);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .team-member {
            text-align: center;
        }

        .team-photo {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .team-member h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
            color: white;
        }

        .team-member p {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-links a {
            color: var(--text-light);
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: var(--primary);
        }

        .stats-container {
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('assets/hero.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 10px;
            margin: 40px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .stat-item p {
            font-size: 1.1rem;
            color: var(--text-light);
        }

        /* Responsive styles */
        @media (max-width: 968px) {
            .story-grid {
                grid-template-columns: 1fr;
            }
            
            .about-hero h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .about-hero {
                padding: 100px 20px 60px;
            }
            
            .about-hero h1 {
                font-size: 2.2rem;
            }
            
            .about-hero p {
                font-size: 1rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .about-hero h1 {
                font-size: 1.8rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .stat-item h3 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="about-hero">
        <h1>Our Story</h1>
        <p>From a single-screen theater to the region's premier cinema destination, Anvora Cinemas has been bringing stories to life for over 20 years.</p>
    </section>
    
    <div class="about-container">
        <section class="about-section">
            <div class="story-grid">
                <div class="story-image">
                    <img src="assets/Anvora cinemas.png" alt="Anvora Cinemas history">
                </div>
                <div class="story-content">
                    <h3>Our Humble Beginnings</h3>
                    <p>Founded in 2002 by film enthusiasts Sarah and Michael Anvora, our first theater was a 200-seat single-screen cinema in downtown. What began as a passion project quickly grew into a beloved community institution.</p>
                    <p>We believed in creating more than just a movie theater - we wanted to build a place where film lovers could gather, share experiences, and celebrate the magic of cinema together.</p>
                </div>
            </div>
        </section>
        
        <section class="about-section">
            <div class="section-title">
                <h2>Why Choose Anvora</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3>Premium Viewing</h3>
                    <p>State-of-the-art 4K laser projection and Dolby Atmos sound systems in every auditorium for unparalleled viewing experiences.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3>Luxury Seating</h3>
                    <p>Plush, reclining seats with ample legroom and personal tables for maximum comfort during your movie.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Gourmet Concessions</h3>
                    <p>From artisanal popcorn flavors to full meals and craft cocktails, we've redefined theater dining.</p>
                </div>
            </div>
        </section>
        
        <section class="stats-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>20+</h3>
                    <p>Years in Business</p>
                </div>
                <div class="stat-item">
                    <h3>5</h3>
                    <p>Locations</p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Screens</p>
                </div>
                <div class="stat-item">
                    <h3>10M+</h3>
                    <p>Happy Movie goers</p>
                </div>
            </div>
        </section>
        
        <section class="about-section">
            <div class="section-title">
                <h2>Meet Our Leadership</h2>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <img src="assets/001.jpg" alt="Olamide .O." class="team-photo">
                    <h3>Olamide .O.</h3>
                    <p>Founder & CEO</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="assets/001.jpg" alt="Victor .O." class="team-photo">
                    <h3>Victor .O.</h3>
                    <p>Founder & CTO</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="assets/001.jpg" alt="Andrew .A." class="team-photo">
                    <h3>Andrew .A.</h3>
                    <p>Director of Operations</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="team-member">
                    <img src="assets/001.jpg" alt="Razaq .L." class="team-photo">
                    <h3>Razaq .L.</h3>
                    <p>Head of Programming</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="about-section">
            <div class="section-title">
                <h2>Our Commitment</h2>
            </div>
            <div class="story-content" style="max-width: 800px; margin: 0 auto; text-align: center;">
                <p>At Anvora Cinemas, we're committed to more than just showing movies. We strive to create unforgettable experiences that bring people together. From our community film festivals to our support for local filmmakers, we believe in the power of cinema to inspire, educate, and entertain.</p>
                <p>We're constantly innovating to provide the best possible experience for our guests while staying true to our roots as a community-focused theater.</p>
            </div>
        </section>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Simple animation for stats counting
        document.addEventListener('DOMContentLoaded', function() {
            const statItems = document.querySelectorAll('.stat-item h3');
            
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value + (element.textContent.includes('M') ? 'M+' : '+');
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            };
            
            // Only animate when stats come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const endValue = parseInt(target.textContent);
                        animateValue(target, 0, endValue, 2000);
                        observer.unobserve(target);
                    }
                });
            }, { threshold: 0.5 });
            
            statItems.forEach(item => {
                observer.observe(item);
            });
        });
    </script>
</body>
</html>