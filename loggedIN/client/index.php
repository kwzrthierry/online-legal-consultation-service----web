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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Services Platform</title>
    <link rel="stylesheet" type="text/css" href="index.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h2><span style="color: #1aa3ff;">O -</span>LCS Platform</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="documents.php" class="nav-link">Documents</a></li>
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <div class="welcome-message">
        <h3 style="color: white;">Welcome <?php echo htmlspecialchars($fullname); ?>!</h3>
    </div>

    <div class="services">
        <div class="service-card">
            <img src="../../img/consultation.jpg" alt="Consultation">
            <h2>Legal Consultation</h2>
             <p>Get professional legal advice from the comfort of your home.</p>
            <div class="service-options">
                <a href="chat.php" style="margin-left: 10px;"><button>Get Advice</button></a>
                <a href="appointment.php" style="margin-left: 30px;"><button>Schedule Consultation</button></a>
            </div>
        </div>
        <div class="service-card">
            <img src="../../img/document.jpg" alt="Document Preparation">
            <h2>Document Preparation</h2>
            <p>Let us help you prepare legal documents for your needs.</p>
            <div class="service-options">
                <a href="prepare_doc.php" style="margin-left: 50px"><button>Prepare or Review Document</button></a>
            </div>
        </div>
        <div class="service-card">
            <img src="../../img/present.jpg" alt="Legal Representation">
            <h2>Legal Representation</h2>
            <p>We offer legal mediation services for various legal matters.</p>
            <div class="service-options">
                <a href="mediation.php" style="margin-left: 80px"><button>Mediation Services</button></a>
            </div>
        </div>
    </div>
    <div class="consultation-button-container">
        <a href="provide_consultation.php"><button class="consultation-button">Submit Cases and request consultation</button></a>
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
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            header.classList.toggle('scrolled', window.scrollY > 0);
        });
    </script>
</body>
</html>
