<?php
session_start();

// Database connection
$conn = mysqli_connect("localhost", "root", "", "olcs");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch available cases where lawyer_id is 0
$sql = "SELECT * FROM consultation WHERE lawyer_id = 0";
$result = mysqli_query($conn, $sql);

// Check if there are available cases
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('No cases available for consultation.')</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle form submission
    $client_id = $_POST['client_id'];
    $consultation = mysqli_real_escape_string($conn, $_POST['consultation']);

    // Get lawyer_id from users table based on the username in session
    $username = $_SESSION['user_info']['username'];
    $query = "SELECT referenced_id FROM users WHERE username = '$username'";
    $user_result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($user_result);
    $lawyer_id = $row['referenced_id'];

    // Update consultation in the database for the selected case
    $sql = "UPDATE consultation SET lawyer_id = '$lawyer_id', consultation = '$consultation' WHERE client_id = '$client_id' AND lawyer_id = 0";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Consultation updated successfully.')</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation</title>
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
        form {
            max-width: 600px;
            margin: 0 auto;
            margin-top: 100px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
        }

        label {
            font-weight: bold;
        }

        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical; /* Allow vertical resizing of textarea */
        }

        input[type="submit"] {
            background-color: #1aa3ff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0d7ddf;
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
                <h2><span style="color: #1aa3ff;">O -</span>LCS Platform </h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="chat.php" class="nav-link">Messages</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
            </ul>
        </nav>
    </header>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="case">Choose a case:</label>
        <select name="client_id" id="case">
            <?php
            // Display available cases in dropdown
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['client_id'] . "'>" . $row['cases'] . "</option>";
            }
            ?>
        </select><br><br>
        <label for="consultation">Consultation:</label><br>
        <textarea name="consultation" id="consultation" rows="4" cols="50"></textarea><br><br>
        <input type="submit" value="Submit">
    </form>

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
