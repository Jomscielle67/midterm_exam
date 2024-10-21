<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['status'] === 'Rejected') {
    session_destroy();
    header("Location: registration_rejected.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user['is_verified']) {
        $message = "Error: Your account is not verified. You cannot submit requests.";
    } else {
        $request_type = $_POST['request_type'];
        $student_id = $_POST['student_id'];
        $course = $_POST['course'];

        $stmt = $conn->prepare(
            "INSERT INTO requests (user_id, request_type, student_id, course, status) 
            VALUES (?, ?, ?, ?, 'Pending')"
        );
        $stmt->bind_param("isss", $user_id, $request_type, $student_id, $course);

        if ($stmt->execute()) {
            $message = "Form submitted successfully! Waiting for admin approval.";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}

$stmt = $conn->prepare(
    "SELECT request_type, status, created_at FROM requests WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_requests = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
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

        h1 {
            text-align: center;
            color: #343a40;
        }

        form {
            margin-top: 10px;
        }

        select, input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #f8f9fa;
        }

        td {
            text-align: center;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            padding: 10px;
            border-radius: 5px;
        }

        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>WELCOME, <?= htmlspecialchars($user['username']) ?>!</h1>

        <?php if (!$user['is_verified']): ?>
            <h2 style="color: red;">WAIT KA LANG , VINE VERIFY PA YUNG ACCOUNT MO NG ADMIN.</h2>
        <?php else: ?>
            <div>
                <h3>USER INFORMATION</h3>
                <p>USERNAME: <?= htmlspecialchars($user['username']) ?></p>
                <p>EMAIL: <?= htmlspecialchars($user['email']) ?></p>
                <p>STATUS: <?= $user['is_verified'] ? 'Verified' : 'Not Verified' ?></p>
            </div>
        <?php endif; ?>

        <div>
            <h3>SUBMIT NEW REQUEST</h3>
            <?php if (!$user['is_verified']): ?>
                <p style="color: red;">DI KA PA PWEDENG MAG SUBMIT HANGGAT DIKA VERIFIED</p>
            <?php else: ?>
                <form method="POST">
                    <select name="request_type" required>
                        <option value="TOR">TOR</option>
                        <option value="COR">COR</option>
                        <option value="GRAD_CERT">CERTIFICATE OF GRADUATION</option>
                    </select>
                    <input type="text" name="student_id" placeholder="Student ID" required>
                    <input type="text" name="course" placeholder="Course" required>
                    <button type="submit">SUBMIT REQUEST</button>
                </form>
            <?php endif; ?>
        </div>

        <div>
            <h3>YOUR REQUESTS</h3>
            <table>
                <thead>
                    <tr>
                        <th>REQUEST TYPE</th>
                        <th>STATUS</th>
                        <th>DATE SUBMITTED</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($req = $user_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['request_type']) ?></td>
                            <td style="color: <?= $req['status'] === 'Approved' ? 'green' : ($req['status'] === 'Rejected' ? 'red' : 'black') ?>;">
                                <?= htmlspecialchars($req['status']) ?>
                            </td>
                            <td><?= htmlspecialchars($req['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <a href="logout.php">LOGOUT</a>
    </div>
</body>
</html>

