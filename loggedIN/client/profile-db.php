<?php
session_start();

// Include the database connection file
include '../../db_conn.php';

// Initialize variables
$first_name = "";
$last_name = "";
$email = "";
$birth_date = "";
$sex = "";
$phone_number = "";

// Check if the session variable is set
if(isset($_SESSION['user_info']['username'])) {
    $username = $_SESSION['user_info']['username'];

    // Fetch the citizen_id associated with the username
    $stmt = $conn->prepare("SELECT referenced_id FROM users WHERE username =?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($referenced_id);
    $stmt->fetch();
    $stmt->close();

    // Fetch the user's profile information from the clients table using client_id
    $stmt = $conn->prepare("SELECT * FROM clients WHERE client_id =?");
    $stmt->bind_param("i", $referenced_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Check if data is fetched
    if ($row) {
        // Assign fetched data to variables
        $first_name = $row['first_name'];
        $last_name = $row['last_name'];
        $email = $row['email'];
        $birth_date = $row['birth_date'];
        $sex = $row['sex'];
        $phone_number = $row['phone_number'];
    }

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get edited phone number from POST request
        $phone = $_POST['phone'];

        // Validate and sanitize phone number
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            echo "Invalid phone number";
            exit();
        }

        // Check if phone number is already in use
        $stmt = $conn->prepare("SELECT * FROM clients WHERE phone_number =?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo "Phone number already in use";
            exit();
        }
$stmt->close();

        // Update the phone number in the clients table
        $stmt = $conn->prepare("UPDATE clients SET phone_number =? WHERE client_id =?");
        $stmt->bind_param("si", $phone, $referenced_id);
        $stmt->execute();
        $stmt->close();

        // Redirect to profile page after updating
        header("Location: profile.php");
        exit();
    }
} else {
    // Redirect to login page if session variable not set
    echo "<script>alert('no session variable')</script>";
}
?>