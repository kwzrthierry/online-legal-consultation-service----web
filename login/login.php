<?php
// Include the database connection file
include '../db_conn.php';

// Function to verify user login and get user type
function verifyLogin($conn, $username, $password) {
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a row with the given username exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password using password_verify for clients
        if ($user['user_type'] == 'client' && password_verify($password, $user['password'])) {
            return $user['user_type'];
        } elseif ($user['user_type'] == 'lawyer' && $password == $user['password']) {
            // Compare passwords directly for lawyers
            return $user['user_type'];
        } else {
            // Debugging: Echo if password verification fails
            echo "Password Verification Failed";
        }
    } else {
        // Debugging: Echo if no user found with the given username
        echo "No User Found";
    }
    // Return false if login fails
    return false;
}

// Start a session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from POST request
    $username = $_POST['Username'];
    $password = $_POST['Password'];

    // Verify login credentials
    if (!empty($username) && !empty($password)) {
        $userType = verifyLogin($conn, $username, $password);
        if ($userType) {
            // Store username in session
            $_SESSION['user_info'] = array(
                'username' => $username
            );

            // Show success message and redirect after a delay based on user type
            if ($userType == 'client') {
                echo "<script>
                        setTimeout(function() {
                            alert('Login successful');
                            window.location.href = '../loggedIN/client/index.php';
                        }, 2000); // 2000 milliseconds = 2 seconds
                      </script>";
            } elseif ($userType == 'lawyer') {
                echo "<script>
                        setTimeout(function() {
                            alert('Login successful');
                            window.location.href = '../loggedIN/advisor/index.php';
                        }, 2000); // 2000 milliseconds = 2 seconds
                      </script>";
            }
        } else {
            // Show error message
            echo "<script>alert('Invalid username or password');</script>";
        }
    } else {
        // Show error message if fields are empty
        echo "<script>alert('Username and password are required');</script>";
    }
} else {
    // Redirect to login page if accessed directly
    header("Location: home.html");
    exit();
}
?>