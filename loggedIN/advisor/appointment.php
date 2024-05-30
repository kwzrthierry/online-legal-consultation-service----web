<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

$username = $_SESSION['user_info']['username'];
$conn = mysqli_connect('localhost', 'root', '', 'olcs');

$query = "SELECT referenced_id FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$referenced_id = $row['referenced_id'];

$query = "SELECT first_name, last_name FROM clients WHERE client_id = '$referenced_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$firstname = $row['first_name'];
$lastname = $row['last_name'];
$fullname = $firstname . " " . $lastname;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lawyer_id = $_POST['lawyer'];
    $date = $_POST['date'];
    $duration = $_POST['duration'];

    $check_query = "SELECT * FROM appointments WHERE client_id = '$referenced_id' AND date = '$date'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('You already have an appointment on this date.');</script>";
    } else {
        $insert_query = "INSERT INTO appointments (client_id, lawyer_id, date, duration) VALUES ('$referenced_id', '$lawyer_id', '$date', '$duration')";
        mysqli_query($conn, $insert_query);
        header("Location: appointment.php");
        exit;
    }
}

$appointments_query = "
    SELECT a.date, a.duration, c.first_name, c.last_name 
    FROM appointments a 
    JOIN clients c ON a.client_id = c.client_id 
    WHERE a.client_id = '$referenced_id'
";
$appointments_result = mysqli_query($conn, $appointments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Services Platform</title>
    <link rel="stylesheet" type="text/css" href="appointment.css">
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

    <div class="container">
        <h3>Your Appointments</h3>
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Date</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($appointments_result)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['duration']) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>
    <script>
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
    </script>
</body>
</html>
