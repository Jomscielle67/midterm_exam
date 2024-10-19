<?php
include 'db.php';
session_start();

// Fetch new users and pending requests
$new_users = $conn->query("SELECT * FROM users WHERE is_verified = 0 AND status != 'Rejected'");
$pending_requests = $conn->query("SELECT * FROM requests WHERE status = 'Pending'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_user'])) {
        $user_id = $_POST['user_id'];
        $conn->query("UPDATE users SET is_verified = 1 WHERE id = $user_id");
    }

    if (isset($_POST['reject_user'])) {
        $user_id = $_POST['user_id'];
        $conn->query("UPDATE users SET status = 'Rejected' WHERE id = $user_id");
    }

    if (isset($_POST['status'])) {
        $request_id = $_POST['request_id'];
        $status = $_POST['status']; // 'Approved' or 'Rejected'
        
        // Update the request status in the database
        $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
    }

    if (isset($_POST['approve_request'])) {
        $request_id = $_POST['request_id'];
        $status = $_POST['approve_request']; // 'Approved'
        
        // Update the request status to 'Approved'
        $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
    }

    if (isset($_POST['reject_request'])) {
        $request_id = $_POST['request_id'];
        $status = $_POST['reject_request']; // 'Rejected'
        
        // Update the request status to 'Rejected'
        $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        $stmt->execute();
    }

    // Auto-refresh page after form submission
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .dashboard {
            width: 80%;
            max-width: 600px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h1, h3 {
            text-align: center;
            margin: 0 0 10px;
            color: #333;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        form {
            display: inline;
        }

        button {
            margin-left: 5px;
            padding: 5px 10px;
            border: none;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        button[name="reject_user"] {
            background-color: #dc3545;
        }

        button:hover {
            opacity: 0.9;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
        }

        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Admin Dashboard</h1>

        <h3>New Users (<?= $new_users->num_rows ?>)</h3>
        <ul>
            <?php while ($user = $new_users->fetch_assoc()): ?>
                <li>
                    <?= $user['username'] ?> (<?= $user['email'] ?>)
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <button type="submit" name="approve_user">Approve</button>
                        <button type="submit" name="reject_user">Reject</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>

        <h3>Pending Requests</h3>
        <ul>
            <?php while ($req = $pending_requests->fetch_assoc()): ?>
                <li>
                    <?= $req['request_type'] ?> - Student ID: <?= $req['student_id'] ?>, Course: <?= $req['course'] ?>
                    <form method="POST">
                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                        <button type="submit" name="status" value="Approved">Approve</button>
                        <button type="submit" name="status" value="Rejected">Reject</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>

        <a href="logout.php">Logout</a>
    </div>
</body>
</html>

