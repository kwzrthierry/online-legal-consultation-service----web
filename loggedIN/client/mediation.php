<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

// Database connection
include '../../db_conn.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve client_id from session using username
    $username = $_SESSION['user_info']['username'];
    $stmt = $conn->prepare("SELECT referenced_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($client_id);
    $stmt->fetch();
    $stmt->close();

    // Retrieve department and lawyer information
    $department = $_POST['department'];
    $lawyer_ids = array();
    $stmt = $conn->prepare("SELECT lawyer_id FROM lawyers WHERE department = ?");
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->bind_result($lawyer_id);
    while ($stmt->fetch()) {
        $lawyer_ids[] = $lawyer_id;
    }
    $stmt->close();

    // Get the first lawyer id from the list
    $lawyer_id = reset($lawyer_ids);

    // Retrieve form data
    $first_party = $_POST['firstParty'];
    $second_party = $_POST['secondParty'];
    $case_note = $_POST['caseNote'];

    // Prepare and execute the SQL statement to insert into the mediation_cases table
    $sql = "INSERT INTO mediation_cases (client_id, lawyer_id, first_party, second_party, department, case_note)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $client_id, $lawyer_id, $first_party, $second_party, $department, $case_note);
    if ($stmt->execute()) {
        // Case inserted successfully
        $message = "Mediation Request submitted successfully";
    } else {
        // Error in insertion
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediation services</title>
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
                <li><a href="documents.php" class="nav-link">Documents</a></li>
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Start Mediation Case</h2>
        <form id="mediationForm" method="post">
            <label for="firstParty">Party One:</label>
            <input type="text" id="firstParty" name="firstParty" required><br><br>

            <label for="secondParty">Party Two:</label>
            <input type="text" id="secondParty" name="secondParty" required><br><br>

            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <?php
                // Populate department dropdown
                $sql = "SELECT DISTINCT department FROM lawyers";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['department'] . "'>" . $row['department'] . "</option>";
                }
                ?>
            </select><br><br>

            <label for="caseNote">Case Note:</label><br>
            <textarea id="caseNote" name="caseNote" rows="4" cols="50" required></textarea><br><br>

            <input type="submit" value="Submit">
        </form>
    </div>

    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>

    <script>
        <?php if (!empty($message)): ?>
            alert("<?php echo $message; ?>");
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            alert("<?php echo $error; ?>");
        <?php endif; ?>

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
