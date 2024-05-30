<?php
session_start();

// Include the database connection file
include '../../db_conn.php';

// Initialize variables
$full_name = "";
$sex = "";
$email = "";
$phone_number = "";

// Check if the session variable is set
if(isset($_SESSION['user_info']['username'])) {
    $Username = $_SESSION['user_info']['username'];

    // Fetch the lawyer_id associated with the username
    $stmt = $conn->prepare("SELECT referenced_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $Username);
    $stmt->execute();
    $stmt->bind_result($referenced_id);
    $stmt->fetch();
    $stmt->close();

    // Fetch the lawyer's profile information from the lawyers table using lawyer_id
    $stmt = $conn->prepare("SELECT * FROM lawyers WHERE lawyer_id = ?");
    $stmt->bind_param("i", $referenced_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Check if data is fetched
    if ($row) {
        // Assign fetched data to variables
        $full_name = $row['full_name'];
        $sex = $row['sex'];
        $email = $row['email'];
        $phone_number = $row['phone_number'];
    }

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get edited phone number from POST request
        $phone = $_POST['phone'];

        // Update the phone number in the lawyers table
        $stmt = $conn->prepare("UPDATE lawyers SET phone_number = ? WHERE lawyer_id = ?");
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
