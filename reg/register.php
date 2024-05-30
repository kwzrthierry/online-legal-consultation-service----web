<?php
// Include the database connection file
include '../db_conn.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $email = htmlspecialchars($_POST['email']);
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $firstName = htmlspecialchars($_POST['firstname']);
    $lastName = htmlspecialchars($_POST['lastname']);
    $BDate = htmlspecialchars($_POST['birthDate']);
    $sex = htmlspecialchars($_POST['gender']);

    // Check if email already exists
    $emailCheckQuery = $conn->prepare("SELECT * FROM clients WHERE email=?");
    $emailCheckQuery->bind_param("s", $email);
    $emailCheckQuery->execute();
    $emailCheckResult = $emailCheckQuery->get_result();

    if ($emailCheckResult->num_rows > 0) {
        echo "Error: Email is already registered";
        exit();
    } else {
        // Check if username already exists
        $usernameCheckQuery = $conn->prepare("SELECT * FROM users WHERE username=?");
        $usernameCheckQuery->bind_param("s", $username);
        $usernameCheckQuery->execute();
        $usernameCheckResult = $usernameCheckQuery->get_result();

        if ($usernameCheckResult->num_rows > 0) {
            echo "Error: Username is already taken";
            exit();
        } else {
            // Hash the password before storing
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert data into clients table
            $insertClientQuery = $conn->prepare("INSERT INTO clients (first_name, last_name, email, birth_date, sex) VALUES (?, ?, ?, ?, ?)");
            $insertClientQuery->bind_param("sssss", $firstName, $lastName, $email, $BDate, $sex);

            if ($insertClientQuery->execute()) {
                // Retrieve the generated ID
                $client_id = $conn->insert_id;

                // Insert data into users table
                $insertLoginQuery = $conn->prepare("INSERT INTO users (username, password, referenced_id) VALUES (?, ?, ?)");
                $insertLoginQuery->bind_param("ssi", $username, $hashedPassword, $client_id);

                if ($insertLoginQuery->execute()) {
                    // Close prepared statements
                    $insertClientQuery->close();
                    $insertLoginQuery->close();

                    // Close the database connection
                    $conn->close();

                    // Show success message and redirect after a delay
                    echo "<script>
                            setTimeout(function() {
                                alert('Registration successful');
                                window.location.href = '../loggedIN/client/index.php';
                            }, 2000); // 2000 milliseconds = 2 seconds
                          </script>";
                    exit(); // Stop further execution
                } else {
                    echo "Error: " . $insertLoginQuery->error;
                }
            } else {
                echo "Error: " . $insertClientQuery->error;
            }
        }
    }
} else {
    echo "Error: Invalid request method.";
}
?>
