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

$query = "SELECT full_name FROM lawyers WHERE lawyer_id = '$referenced_id'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$full_name = $row['full_name'];

$nameParts = explode(" ", $full_name);
$secondPart = count($nameParts) >= 2 ? $nameParts[1] : $full_name;

function getClientInfo($clientId)
{
    global $conn;
    $query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name, email FROM clients WHERE client_id = '$clientId'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function displayClientMessages()
{
    global $conn, $referenced_id;

    $client_id_from_link = isset($_GET['client_id']) ? $_GET['client_id'] : null;
    $displayed_clients = [];

    if ($client_id_from_link !== null) {
        $clientInfo = getClientInfo($client_id_from_link);
        if ($clientInfo) {
            echo "<div class='client-message'>
                    <div class='client-name'><a href='chat.php?client_id=$client_id_from_link'>" . htmlspecialchars($clientInfo['full_name']) . "</a></div>
                    <div class='client-email'>" . htmlspecialchars($clientInfo['email']) . "</div>
                  </div>";
            $displayed_clients[] = $client_id_from_link;
        }
    }

    $query = "SELECT DISTINCT client_id FROM messages WHERE lawyer_id = '$referenced_id'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $clientId = $row['client_id'];
        if (!in_array($clientId, $displayed_clients)) {
            $clientInfo = getClientInfo($clientId);
            echo "<div class='client-message'>
                    <div class='client-name'><a href='chat.php?client_id=$clientId'>" . htmlspecialchars($clientInfo['full_name']) . "</a></div>
                    <div class='client-email'>" . htmlspecialchars($clientInfo['email']) . "</div>
                  </div>";
        }
    }
}

function displayMessages()
{
    global $conn, $referenced_id, $secondPart;

    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;

    if ($client_id === null) {
        echo "<p>Please select a client to view messages.</p>";
    } else {
        $clientInfo = getClientInfo($client_id);
        $client_name = $clientInfo['full_name'];

        $query = "SELECT * FROM messages WHERE (client_id = '$client_id' AND lawyer_id = '$referenced_id') OR (client_id = '$referenced_id' AND lawyer_id = '$client_id') ORDER BY date_sent ASC";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $isLawyer = $row['sender'] == 'lawyer';
            $sender = $isLawyer ? $secondPart : $client_name;
            $messageColor = $isLawyer ? 'green' : 'blue';
            $messageAlignment = $isLawyer ? 'right' : 'left';
            $messagePrefix = $isLawyer ? 'Sent on' : 'Received on';
            
            echo "<div style='text-align: $messageAlignment; margin: 10px 0;'>
                    <div style='display: inline-block; background-color: $messageColor; color: white; padding: 10px; border-radius: 10px; max-width: 60%;'>
                        <strong>" . htmlspecialchars($sender) . "</strong>: " . htmlspecialchars($row['message']) . "<br>
                        <span style='font-size: 12px; color: #eee;'>$messagePrefix " . $row['date_sent'] . "</span>
                    </div>
                  </div>";
        }
    }
}

function sendMessage()
{
    global $conn, $referenced_id;

    if (isset($_POST['message']) && isset($_GET['client_id'])) {
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $client_id = $_GET['client_id'];

        // Check if the client_id exists in the clients table
        $query = "SELECT client_id FROM clients WHERE client_id = '$client_id'";
        $result = mysqli_query($conn, $query);
        if (!$result || mysqli_num_rows($result) == 0) {
            echo "Error: Client does not exist.";
            return;
        }

        // Insert the message into the messages table
        $query = "INSERT INTO messages (client_id, lawyer_id, message, sender) VALUES ('$client_id', '$referenced_id', '$message', 'lawyer')";
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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    sendMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lawyer Messages - Legal Services Platform</title>
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
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="Messages">
            <h2>Client Messages</h2>
            <?php displayClientMessages(); ?>
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
