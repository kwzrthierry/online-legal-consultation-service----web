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
$stmt = $conn->prepare("SELECT referenced_id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($lawyer_id);
$stmt->fetch();
$stmt->close();

// Initialize variables for form data
$first_party = $second_party = $case_note = $client_fullname = '';

// Check if med_id is passed in the URL
if(isset($_GET['med_id'])) {
    $med_id = $_GET['med_id'];

    // Retrieve mediation case details using med_id
    $stmt = $conn->prepare("SELECT first_party, second_party, case_note, client_id FROM mediation_cases WHERE med_id = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $stmt->bind_result($first_party, $second_party, $case_note, $client_id);
    $stmt->fetch();
    $stmt->close();

    // Retrieve client's full name using client_id
    $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM clients WHERE client_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $stmt->bind_result($client_fullname);
    $stmt->fetch();
    $stmt->close();
}

// Function to send Zoom call link to the client
function sendZoomCallLink($client_id, $lawyer_id, $zoom_link, $conn) {
    $sender = 'Lawyer'; // Assuming the sender is always the lawyer
    $date_sent = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO messages (client_id, lawyer_id, sender, message, date_sent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $client_id, $lawyer_id, $sender, $zoom_link, $date_sent);
    $stmt->execute();
    $stmt->close();
}

// Check if Zoom call link is submitted
if(isset($_POST['zoom_link'])) {
    $zoom_link = $_POST['zoom_link'];
    sendZoomCallLink($client_id, $lawyer_id, $zoom_link, $conn);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediation Cases</title>
    <style type="text/css">
        @font-face {
          font-family: "Poppins-Regular";
          src: url("../../fonts/poppins/Poppins-Regular.ttf");
        }

        @font-face {
          font-family: "Poppins-SemiBold";
          src: url("../../fonts/poppins/Poppins-SemiBold.ttf");
        }
        body {
            margin: 0;
            padding: 0;
            font-family: "Poppins-Regular";
            position: relative;
            background-image: url('../../img/2.jpg');
            background-size: cover;
        }

        body::after {
            content: '';
            opacity: 0.5;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            z-index: -1;
            background-color: rgba(0, 0, 0, 0.5);
        }

        header {
            background-color: rgba(0, 0, 0, 0.1);
            padding: 10px 0;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 50px;
        }

        .logo h2 {
            color: #fff;
            margin: 0;
        }

        .logo {
            margin-left: 20px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            margin-right: 560px;
        }

        .nav-links li {
            margin-right: 20px;
        }

        .nav-links li a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links li a:hover {
            color: #e6e6e6;
            transform: scale(1.1);
            transition: transform 0.3s ease;
            position: relative;
        }

        .nav-links li a:hover::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 100%;
            height: 2px;
            background-color: black;
        }

        .container {
            width: 60%;
            margin: 50px auto;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1aa3ff;
            box-shadow: 0 0 5px rgba(26, 163, 255, 0.5);
        }

        .form-group input[type="text"][readonly],
        .form-group textarea[readonly] {
            background-color: #f9f9f9;
        }

        .form-group textarea {
            resize: vertical;
        }

        .zoom-btn {
            width: 100%;
            padding: 10px;
            background-color: #1aa3ff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .zoom-btn:hover {
            background-color: #007acc;
        }
                .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 15px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        footer {
            color: #fff;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .footer-copyright {
            margin-top: 50px;
            color: #ccc;
            font-size: 14px;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
        }

        h3 {
            text-align: center;
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

    <div class="container">
        <h2>Mediation Case Details</h2>
        <form method="post">
            <div class="form-group">
                <label for="first_party">First Party:</label>
                <input type="text" id="first_party" name="first_party" value="<?php echo $first_party; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="second_party">Second Party:</label>
                <input type="text" id="second_party" name="second_party" value="<?php echo $second_party; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="case_note">Case Note:</label>
                <textarea id="case_note" name="case_note" rows="4" readonly><?php echo $case_note; ?></textarea>
            </div>
            <div class="form-group">
                <label for="client_fullname">Client's Full Name:</label>
                <input type="text" id="client_fullname" name="client_fullname" value="<?php echo $client_fullname; ?>" readonly>
            </div>
            <div class="form-group">
                <button type="button" class="zoom-btn" onclick="window.location.href='https://zoom.us/'">Zoom Call</button>
            </div>
            <div class="form-group">
                <label for="zoom_link">Zoom Call Link:</label>
                <input type="text" id="zoom_link" name="zoom_link">
            </div>
            <div class="form-group">
                <button type="submit" class="zoom-btn">Send Zoom Call Link</button>
            </div>
        </form>
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

        function zoomCall() {
            // You can replace this with the actual Zoom call URL or any other desired functionality
            alert("Zoom call initiated!");
        }
    </script>
</body>
</html>
