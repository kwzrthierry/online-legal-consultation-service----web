<?php
session_start();
if (!isset($_SESSION['user_info']['username'])) {
    header('Location: ../../login/home.html');
    exit;
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "olcs");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['user_info']['username'];

// Fetch the referenced_id associated with the username
$query = "SELECT referenced_id FROM users WHERE username = '$username'";
$user_result = mysqli_query($conn, $query);
if (!$user_result || mysqli_num_rows($user_result) == 0) {
    die("User not found or query failed: " . mysqli_error($conn));
}
$row = mysqli_fetch_assoc($user_result);
$client_id = $row['referenced_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['case'])) {
    // Handle new case submission
    $case = mysqli_real_escape_string($conn, $_POST['case']);

    // Insert case into the database
    $sql = "INSERT INTO consultation (client_id, cases) VALUES ('$client_id', '$case')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Case submitted successfully.')</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Fetch cases for the dropdown
$cases_query = "SELECT consultation_id, cases FROM consultation WHERE client_id = '$client_id'";
$cases_result = mysqli_query($conn, $cases_query);

// Handle fetching of consultation details
$selected_case = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_case_id'])) {
    $selected_case_id = mysqli_real_escape_string($conn, $_POST['selected_case_id']);
    $consultation_query = "SELECT consultation FROM consultation WHERE consultation_id = '$selected_case_id'";
    $consultation_result = mysqli_query($conn, $consultation_query);
    if ($consultation_result && mysqli_num_rows($consultation_result) > 0) {
        $selected_case = mysqli_fetch_assoc($consultation_result)['consultation'];
    } else {
        $selected_case = "No case details found for the selected case.";
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
            width: 80%;
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
        <label for="case">Case:</label><br>
        <textarea id="case" name="case" rows="4" cols="50" required></textarea><br><br>
        <input type="submit" value="Submit">
    </form>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="margin-top: 20px;">
        <label for="selected_case_id">Select Case:</label><br>
        <select id="selected_case_id" name="selected_case_id" required>
            <option value="" disabled selected>Select a case</option>
            <?php
            while ($case_row = mysqli_fetch_assoc($cases_result)) {
                echo "<option value='" . $case_row['consultation_id'] . "'>" . $case_row['cases'] . "</option>";
            }
            ?>
        </select><br><br>
        <input type="submit" value="View Consultation">
    </form>

    <?php
    if ($selected_case) {
        echo "<div style='max-width: 600px; margin: 0 auto; margin-top: 20px; padding: 20px; background: rgba(255, 255, 255, 0.8); border-radius: 10px;'>
                <h3>Selected Case Details:</h3>
                <p>$selected_case</p>
              </div>";
    }
    ?>

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
