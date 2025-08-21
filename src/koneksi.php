<?php
$host = "coffeeshop-coffeeshop-tkj.f.aivencloud.com";   // server database
$user = "avnadmin";        // user
$pass = "AVNS_groYKXGiy9-Bpfl4xrC"; // password
$db   = "defaultdb";       // nama database
$port = "22318";           // port dari Aiven (biasanya bukan 3306, cek lagi di dashboard)

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);

    // Set error mode biar gampang debug
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Koneksi berhasil menggunakan PDO!";
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
