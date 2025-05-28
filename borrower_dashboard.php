<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'borrower') {
    header("Location: login.html");
    exit();
}

$name = $_SESSION['name'];

// DB connection info - update these with your actual credentials
$host = 'localhost';
$dbname = 'lendu_db';
$user = 'db_user';
$pass = 'db_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT id, amount, status, requested_on FROM loans WHERE user_id = ? ORDER BY requested_on DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $loans = [];
    $error_loading_loans = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrower Dashboard | Lendu</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: #f4f6f9;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        .container {
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }
        .welcome {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .action-buttons {
            margin-bottom: 30px;
        }
        .action-buttons a {
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .action-buttons a:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #f1f1f1;
        }
        th {
            background-color: #f0f0f0;
        }
        .error-msg {
            color: red;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .no-loans {
            text-align: center;
            padding: 20px;
            color: #555;
        }
    </style>
</head>
<body>

<header>
    <h2>Lendu | Borrower Dashboard</h2>
    <nav>
        <a href="borrower_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="welcome">Welcome, <?= htmlspecialchars($name) ?> 👋</div>

    <div class="action-buttons">
        <a href="apply_loan.php">Apply for a New Loan</a>
    </div>

    <h3>Your Loan Applications</h3>

    <?php if (!empty($error_loading_loans)): ?>
        <div class="error-msg">Error loading your loan applications. Please try again later.</div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Loan Amount</th>
                <th>Status</th>
                <th>Requested On</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($loans)): ?>
                <tr>
                    <td colspan="4" class="no-loans">No loan applications found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td>RWF <?= number_format($loan['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($loan['status']) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($loan['requested_on']))) ?></td>
                        <td><a href="loan_details.php?id=<?= urlencode($loan['id']) ?>">View</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
