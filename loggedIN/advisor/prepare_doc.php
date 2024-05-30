<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

$username = $_SESSION['user_info']['username'];
$conn = new mysqli('localhost', 'root', '', 'olcs');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch lawyer_id based on username
$query = "SELECT referenced_id FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lawyer_id = $row['referenced_id'];
} else {
    echo "<script>alert('Error fetching lawyer ID.');</script>";
    exit;
}

$stmt->close();

// Fetch requests for the logged-in lawyer
$query = "SELECT r.request_id, r.client_id, r.document_name, r.request, c.first_name, c.last_name 
          FROM request r 
          JOIN clients c ON r.client_id = c.client_id 
          WHERE r.lawyer_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $lawyer_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
} else {
    echo "<script>alert('No requests found.');</script>";
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor Dashboard</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <style>
        .table-container {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #1aa3ff;
            color: white;
        }
        .contact-btn {
            background-color: #1aa3ff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .contact-btn:hover {
            background-color: #007acc;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h2><span style="color: #1aa3ff;">O -</span>LCS Platform</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <div class="table-container">
        <h2>Client Requests</h2>
        <?php if (!empty($requests)): ?>
            <table>
                <tr>
                    <th>Request ID</th>
                    <th>Client Name</th>
                    <th>Document Name</th>
                    <th>Request Description</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['request_id']) ?></td>
                        <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                        <td><?= htmlspecialchars($request['document_name']) ?></td>
                        <td><?= htmlspecialchars($request['request']) ?></td>
                        <td><button class="contact-btn" onclick="contactUser('<?= htmlspecialchars($request['client_id']) ?>')">Contact</button></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>There are no requests for you.</p>
        <?php endif; ?>
    </div>

    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>

    <script>
        function contactUser(clientId) {
            window.location.href = 'chat.php?client_id=' + clientId;
        }

        function logout() {
            const confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../logout.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        window.location.href = "../../index.html";
                    } else {
                        console.error('Error:', xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    console.error('Network error');
                };
                xhr.send();
            }
        }

        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 0);
        });
    </script>
</body>
</html>
