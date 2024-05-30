<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location:../../login/home.html');
    exit;
}

// Database connection
include '../../db_conn.php';

$message = '';
$error = '';

// Retrieve lawyer_id from session using username
$username = $_SESSION['user_info']['username'];
$stmt = $conn->prepare("SELECT referenced_id FROM users WHERE username =?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($lawyer_id);
$stmt->fetch();
$stmt->close();

// Retrieve mediation cases assigned to the logged-in user's ID
$stmt = $conn->prepare("SELECT med_id, lawyer_id, first_party, second_party, department, case_note FROM mediation_cases WHERE lawyer_id = ?");
$stmt->bind_param("i", $lawyer_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediation Cases</title>
    <link rel="stylesheet" type="text/css" href="med.css">
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
        <h2>Mediation Cases</h2>
        <table>
            <thead>
                <tr>
                    <th>Med ID</th>
                    <th>Lawyer ID</th>
                    <th>First Party</th>
                    <th>Second Party</th>
                    <th>Department</th>
                    <th>Case Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) {?>
                <tr>
                    <td><?php echo $row['med_id'];?></td>
                    <td><?php echo $row['lawyer_id'];?></td>
                    <td><?php echo $row['first_party'];?></td>
                    <td><?php echo $row['second_party'];?></td>
                    <td><?php echo $row['department'];?></td>
                    <td><?php echo $row['case_note'];?></td>
                    <td>
                        <a href="next_page.php?med_id=<?php echo $row['med_id'];?>">
                            <button>Action</button>
                        </a>
                    </td>
                </tr>
                <?php }?>
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
                // Send a request to logout.php using AJAX
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '../logout.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Redirect the user to index.html after successful logout
                        window.location.href = "../../index.html";
                    } else {
                        // Handle errors if needed
                        console.error('Error:', xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    // Handle network errors if needed
                    console.error('Network error');
                };
                xhr.send();
            }
        }
    </script>
</body>
</html>
