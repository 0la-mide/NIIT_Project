Anvora Cinemas – Online Movie Ticket Booking System

Overview
This is a Cinema Ticket Booking Web Application that allows customers to:
Browse movies and showtimes
Choose seats interactively
Add food & drinks
Pay and receive booking confirmation

Admins can:
Manage movies, showtimes, auditoriums (seat layouts)
Upload food items & posters
View and manage bookings
Run promotions

Features
For Customers:

✅ View latest movies and posters
✅ Pick showtime & seats (booked seats are disabled automatically)
✅ Add food & snacks to cart
✅ Make payments and get booking reference

For Admins:

✅ Secure login with password hashing
✅ Manage movies, showtimes, auditoriums, and food menus
✅ Seat map generator for auditoriums
✅ Track all bookings & payments

🛠️ Technologies Used
Frontend: HTML, CSS, JavaScript
Backend: PHP (procedural with mysqli)
Database: MySQL (anvora_cinemas.sql)
Server: Apache (XAMPP / WAMP / LAMP)
Security: Hashed passwords, session-based authentication

⚙️ Installation & Setup

Clone or Download this project into your web server folder:
For XAMPP → htdocs/
For WAMP → www/
Import Database:
Open phpMyAdmin
Create a database anvora_cinemas
Import anvora_cinemas.sql file
Configure Database Connection:
Open config.php
Update DB credentials if needed:
$conn = new mysqli("localhost", "root", "", "anvora_cinemas");

Run the Project:
Customer side: http://localhost/niit_project/
Admin side: http://localhost/niit_project/admin/

Default Admin Login:
Email: 
Password: 

📷 Uploads Folder
uploads/posters → Movie posters
uploads/food → Food & drink images
uploads/cinemas → Cinema banners
uploads/ → General uploads

📖 Usage Flow (For Customers)
Open website → Browse movies
Select movie → Pick showtime
Seat map opens → Select available seats
Add snacks → Proceed to payment
Confirm booking → Get booking reference

📖 Usage Flow (For Admins)
Login at /admin/login.php
Add or manage movies, showtimes, food items
Generate seat maps for auditoriums
Track bookings and payments

🔐 Security Notes
Passwords are hashed using PHP password_hash()
Booked seats are locked in DB to prevent double-booking
Admin panel is protected via login sessions