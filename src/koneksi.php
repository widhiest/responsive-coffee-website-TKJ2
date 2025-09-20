<?php
$host = "coffeeshop-coffeeshop-tkj.f.aivencloud.com";
$user = "avnadmin";
$pass = "AVNS_groYKXGiy9-Bpfl4xrC";
$db   = "defaultdb";
$port = "22318";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);

    // Set error mode untuk debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Enable emulate prepares for better compatibility
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // SUCCESS: Connection established (commented out to avoid output in production)
    // echo "Koneksi berhasil menggunakan PDO!";
    
} catch (PDOException $e) {
    // Log error untuk debugging (dalam production, hindari menampilkan error detail)
    error_log("Database connection error: " . $e->getMessage());
    
    // Display user-friendly error message
    die("Database connection failed. Please try again later.");
}

// Optional: Function to check database structure and fix common issues
function checkAndFixDatabaseStructure($pdo) {
    try {
        // Check if PegawaiID column allows NULL in Transaksi table
        $stmt = $pdo->query("DESCRIBE Transaksi");
        $columns = $stmt->fetchAll();
        
        $pegawaiIdColumn = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'PegawaiID') {
                $pegawaiIdColumn = $column;
                break;
            }
        }
        
        if ($pegawaiIdColumn && $pegawaiIdColumn['Null'] === 'NO') {
            // PegawaiID doesn't allow NULL, we need to modify it
            // Note: This would require ALTER privileges
            // $pdo->exec("ALTER TABLE Transaksi MODIFY PegawaiID INT NULL");
            error_log("Warning: PegawaiID column in Transaksi table doesn't allow NULL values");
        }
        
    } catch (PDOException $e) {
        error_log("Error checking database structure: " . $e->getMessage());
    }
}

// Uncomment this line if you want to check database structure on each connection
// checkAndFixDatabaseStructure($pdo);
?>