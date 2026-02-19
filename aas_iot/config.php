<?php
$servername = "localhost";
$database = "aas_iot";
$username = "idaemm";
$password = "mabarpapji";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["statusCode" => 500, "message" => "Koneksi Gagal: " . $e->getMessage()]));
}
?>