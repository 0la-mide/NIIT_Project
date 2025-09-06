<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3>Anvora Cinemas</h3>
                <p>Experience movie magic like never before at our state-of-the-art theaters.</p>
            </div>
            
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="movies.php">Movies</a></li>
                    <li><a href="cinemas.php">Cinemas</a></li>
                    <li><a href="offers.php">Offers</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h3>Contact</h3>
                <p>Email: info@anvoracinemas.com</p>
                <p>Phone: +234 123 456 7890</p>
            </div>
            
            <div class="footer-col">
                <h3>Newsletter</h3>
                <form class="newsletter-form">
                    <input type="email" placeholder="Your email" required>
                    <button type="submit">Subscribe</button>
                </form>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Anvora Cinemas. All rights reserved.</p>
        </div>
    </div>
    <style>
        .main-footer {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 60px 0 20px;
            font-family: 'Arial', sans-serif;
        }

        .main-footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .footer-col {
            padding: 0 15px;
        }

        .footer-col h3 {
            color:rgb(7, 140, 206);
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background:rgb(7, 140, 206);
        }

        .footer-col p {
            color: #b3b3b3;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col ul li a {
            color: #b3b3b3;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-col ul li a:hover {
            color: rgb(7, 140, 206);
        }

        .newsletter-form {
            display: flex;
            margin-bottom: 20px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px 0 0 4px;
            outline: none;
        }

        .newsletter-form button {
            background-color: rgb(7, 140, 206);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .newsletter-form button:hover {
            background-color: rgb(7, 140, 206);
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            color: #b3b3b3;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-links a:hover {
            color: rgb(7, 140, 206);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #b3b3b3;
            font-size: 0.9rem;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .footer-col {
                text-align: center;
            }
            
            .footer-col h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</footer>