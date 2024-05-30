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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lawyer_id = $_POST['lawyer'];
    $date = $_POST['date'];
    $duration = $_POST['duration'];

    // Check if the user already has an appointment on this date
    $check_query = "SELECT * FROM appointments WHERE client_id = '$referenced_id' AND date = '$date'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('You already have an appointment on this date.');</script>";
    } else {
        $insert_query = "INSERT INTO appointments (client_id, lawyer_id, date, duration) VALUES ('$referenced_id', '$lawyer_id', '$date', '$duration')";
        mysqli_query($conn, $insert_query);
        header("Location: appointment.php");
        exit;
    }
}

$lawyers_query = "SELECT lawyer_id, full_name FROM lawyers";
$lawyers_result = mysqli_query($conn, $lawyers_query);

$appointments_query = "SELECT * FROM appointments WHERE client_id = '$referenced_id'";
$appointments_result = mysqli_query($conn, $appointments_query);

function get_unavailable_times($conn, $lawyer_id, $date) {
    $query = "SELECT duration FROM appointments WHERE lawyer_id = '$lawyer_id' AND date = '$date'";
    $result = mysqli_query($conn, $query);
    $unavailable_durations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $unavailable_durations[] = $row['duration'];
    }
    return $unavailable_durations;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legal Services Platform</title>
    <link rel="stylesheet" type="text/css" href="appointment.css">
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
        <h3>Book an Appointment</h3>
        <form id="appointment-form" method="POST" action="">
            <label for="lawyer">Choose Lawyer:</label>
            <select name="lawyer" id="lawyer" required>
                <option value="">Select a lawyer</option>
                <?php while ($row = mysqli_fetch_assoc($lawyers_result)) { ?>
                    <option value="<?= $row['lawyer_id'] ?>"><?= $row['full_name'] ?></option>
                <?php } ?>
            </select>
            <br>
            <label for="date">Choose Date:</label>
            <input type="date" name="date" id="date" required>
            <br>
            <label for="duration">Choose Time Slot:</label>
            <select name="duration" id="duration" required>
                <option value="">Select a time slot</option>
                <option value="9:00 - 12:00">09:00 - 12:00</option>
                <option value="14:30 - 16:20">14:30 - 16:20</option>
            </select>
            <br>
            <button type="submit">Book Appointment</button>
        </form>

        <h3>Your Appointments</h3>
        <ul id="appointments-list">
            <?php while ($row = mysqli_fetch_assoc($appointments_result)) { ?>
                <li>
                    <span> Date: <?= $row['date'] ?>  Time-Duration: <?= $row['duration'] ?></span>
                    <button class="cancel-btn" onclick="cancelAppointment('<?= $row['appointment_id'] ?>')">Cancel</button>
                </li>
            <?php } ?>
        </ul>
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

        function cancelAppointment(appointmentId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'cancel_appointment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    location.reload();
                } else {
                    console.error('Error:', xhr.statusText);
                }
            };
            xhr.onerror = function () {
                console.error('Network error');
            };
            xhr.send('appointment_id=' + appointmentId);
        }

        document.getElementById('lawyer').addEventListener('change', function () {
            const lawyerId = this.value;
            document.getElementById('date').disabled = !lawyerId;
            document.getElementById('duration').disabled = !lawyerId;

            document.getElementById('date').addEventListener('change', function () {
                const date = this.value;
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_unavailable_times.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        const unavailableDurations = JSON.parse(xhr.responseText);
                        const durationSelect = document.getElementById('duration');
                        durationSelect.innerHTML = `
                            <option value="">Select a time slot</option>
                            <option value="09:00-12:00" ${unavailableDurations.includes('09:00-12:00') ? 'disabled' : ''}>09:00 - 12:00</option>
                            <option value="14:30-16:20" ${unavailableDurations.includes('14:30-16:20') ? 'disabled' : ''}>14:30 - 16:20</option>
                        `;
                    } else {
                        console.error('Error:', xhr.statusText);
                    }
                };
                xhr.onerror = function () {
                    console.error('Network error');
                };
                xhr.send('lawyer_id=' + lawyerId + '&date=' + date);
            });
        });
    </script>
</body>
</html>
