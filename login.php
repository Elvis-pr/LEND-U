<?php
session_start();

// Database connection
$host = 'localhost';
$db   = 'lendu_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Fetch user from DB
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role']; // 'lender' or 'borrower'
    $_SESSION['user_name'] = $user['name'];

    // Redirect to the appropriate dashboard
    if ($user['role'] === 'lender') {
      header('Location: lender_dashboard.php');
    } else {
      header('Location: borrower_dashboard.php');
    }
    exit(); // Always exit after redirect
  } else {
    // Invalid login
    echo "<script>alert('Invalid email or password.'); window.location.href = 'login.html';</script>";
  }
}
?>
