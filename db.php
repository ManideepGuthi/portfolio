<?php
// $host = "localhost";
// $user = "root";
// $pass = "manu";
// $dbname = "portfolio_db";

// $conn = new mysqli($host, $user, $pass, $dbname);

// if ($conn->connect_error) {
//  die("Connection failed: " . $conn->connect_error);
// }



// $host = 'localhost';
// $dbname = 'portfolio_db';
// $username = 'root';
// $password = 'manu';

// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }


$host = 'localhost';
$dbname = 'portfolio_db';
$user = 'root';
$pass = 'manu';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("DB.PHP: Connection failed (connect_error): " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn; // Return the connection object

} catch (Exception $e) {
    die("DB.PHP: Exception caught: " . $e->getMessage());
    return false; // Return false on failure
}


?>