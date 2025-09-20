<?php
session_start();
include "koneksi.php";

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $nomorHP = trim($_POST['nomorHP'] ?? '');

        if (empty($username) || empty($email) || empty($password)) {
            $response['message'] = 'Semua field wajib diisi.';
            echo json_encode($response);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Format email tidak valid.';
            echo json_encode($response);
            exit();
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Customer WHERE Email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $response['message'] = 'Email sudah terdaftar. Silakan gunakan email lain.';
            echo json_encode($response);
            exit();
        }
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO Customer (Nama, Email, NomorHP, Password) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $nomorHP, $hashedPassword]);
            
            $response['success'] = true;
            $response['message'] = 'Registrasi berhasil. Silakan masuk.';
        } catch (PDOException $e) {
            // $response['message'] = 'Gagal mendaftar. Terjadi kesalahan server.';
            // error_log("Register error: " . $e->getMessage());
            $response['message'] = 'Gagal mendaftar. Error: ' . $e->getMessage();
        }

    } elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response['message'] = 'Email dan password harus diisi.';
            echo json_encode($response);
            exit();
        }

        try {
            $sql = "SELECT * FROM Customer WHERE Email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['Password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['CustomerID'];
                $_SESSION['user_name'] = $user['Nama'];
                $response['success'] = true;
                $response['message'] = 'Login berhasil!';
            } else {
                $response['message'] = 'Email atau password salah.';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Gagal masuk. Terjadi kesalahan server.';
            error_log("Login error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>