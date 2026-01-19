<?php
$servername = "mysql-service";
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');
$dbname = getenv('MYSQL_DATABASE');

$pod_ip = $_SERVER['SERVER_ADDR'];
$version = "v2.0";
$bg_color = "#a08cbb";

echo "<html><head><title>BrilliantApp</title></head><body style='background-color: $bg_color; font-family: Arial;'>";
echo "<div style='text-align: center; padding: 50px;'>";
echo "<h1>BrilliantApp - LAMP na Minikube</h1>";
echo "<h3>Wersja: $version</h3>";
echo "<p>Pod IP: <strong>$pod_ip</strong></p>";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<h2 style='color:red'>Błąd połączenia z bazą!</h2>";
    echo "<p>" . $conn->connect_error . "</p>";
} else {
    echo "<h2 style='color:green'>Połączenie z bazą udane!</h2>";
    echo "<p>Host: $servername | Użytkownik: $username</p>";
}
echo "</div></body></html>";
?>