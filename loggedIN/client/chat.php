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
$first_name = $row['first_name'];
$last_name = $row['last_name'];
$client_name = $first_name . ' ' . $last_name;

$query = "SELECT full_name, department, lawyer_id FROM lawyers";
$result = mysqli_query($conn, $query);

// Fetch all lawyers and their departments
$lawyers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $lawyers[] = $row;
}

// Function to display chat messages
function displayMessages() {
    global $conn, $referenced_id, $client_name;
    
    $lawyer_id = isset($_GET['lawyer_id']) ? $_GET['lawyer_id'] : null;

    if ($lawyer_id === null) {
        // Handle the case when lawyer_id is not set in the URL
        // You might want to set a default value or display an error message
    } else {
        // Retrieve the lawyer's name
        $query = "SELECT full_name FROM lawyers WHERE lawyer_id = '$lawyer_id'";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $lawyer_name = $row['full_name'];

        $query = "SELECT * FROM messages WHERE (client_id = '$referenced_id' AND lawyer_id = '$lawyer_id') OR (client_id = '$lawyer_id' AND lawyer_id = '$referenced_id') ORDER BY date_sent ASC";
        $result = mysqli_query($conn, $query);

        // Display messages
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine sender's role (lawyer or client)
            $isLawyer = $row['sender'] == 'lawyer';
            $sender = $isLawyer ? $lawyer_name : $client_name; // Inverted sender assignment
            $messageColor = $isLawyer ? 'blue' : 'green'; // Inverted message color
            $messageAlignment = $isLawyer ? 'left' : 'right'; // Inverted message alignment
            $messagePrefix = $isLawyer ? 'Received on' : 'Sent on'; // Inverted message prefix
            
            echo "<div style='text-align: $messageAlignment; margin: 10px 0;'>
                    <div style='display: inline-block; background-color: $messageColor; color: white; padding: 10px; border-radius: 10px; max-width: 60%;'>
                        <strong>" . htmlspecialchars($sender) . "</strong>: " . htmlspecialchars($row['message']) . "<br>
                        <span style='font-size: 12px; color: #eee;'>$messagePrefix " . $row['date_sent'] . "</span>
                    </div>
                  </div>";
        }

    }
}


// Function to send message
function sendMessage() {
    global $conn, $referenced_id;
    
    if (isset($_POST['message']) && isset($_GET['lawyer_id'])) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $lawyer_id = $_GET['lawyer_id'];

        $query = "INSERT INTO messages (client_id, lawyer_id, message, sender) VALUES ('$referenced_id', '$lawyer_id', '$message', 'client')";
        $result = mysqli_query($conn, $query);

        if ($result) {
            // Redirect to prevent form resubmission
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit;
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Send message if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sendMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Services Platform</title>
    <link rel="stylesheet" type="text/css" href="chat.css">
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
        <div class="Messages">
            <h2>Lawyer's Messages</h2>
            <?php foreach ($lawyers as $lawyer): ?>
                <?php
                // Check if the current lawyer is selected
                $selectedClass = isset($_GET['lawyer_id']) && $_GET['lawyer_id'] == $lawyer['lawyer_id'] ? 'selected' : '';
                ?>
                <div class="lawyer <?php echo $selectedClass; ?>" onclick="selectLawyer(<?php echo $lawyer['lawyer_id']; ?>)">
                    <div class="name"><?php echo htmlspecialchars($lawyer['full_name']); ?></div>
                    <div class="department"><?php echo htmlspecialchars($lawyer['department']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chat">
            <div class="chat-header">
                <h2>Send Message</h2>
            </div>
            <div class="chat-messages">
                <?php displayMessages(); ?>
            </div>
            <div class="chat-input">
                <form method="post">
                    <textarea name="message" placeholder="Type your message..."></textarea>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>

    <button class="logout-btn" onclick="logout()">Logout</button>
    <footer>
        <p class="footer-copyright">&copy; 2024 O-Lcs Platform. All rights reserved.</p>
    </footer>
    <script>
        function selectLawyer(lawyerId) {
            // Redirect to the same page with selected lawyer id as URL parameter
            window.location.href = `chat.php?lawyer_id=${lawyerId}`;
        }

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
