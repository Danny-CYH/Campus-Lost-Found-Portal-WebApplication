<?php
session_start();

// Database configuration (local settings)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_lost_found');

// Database configuration (production settings)
// define('DB_HOST', 'localhost');
// define('DB_USER', 'dramranc_campuslostfound');
// define('DB_PASS', 'W8hj8HVF#aey');
// define('DB_NAME', 'dramranc_campuslostfound');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Google Maps API Key
define('GMAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

// Local Site configuration
define('SITE_URL', 'http://localhost/campus-lost-found');

// Production site configuration
// define('SITE_URL', 'https://campuslostfound.dramran.com/');
?>