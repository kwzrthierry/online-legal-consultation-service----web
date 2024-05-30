<?php
$conn = mysqli_connect('localhost', 'root', '', 'olcs');
$appointment_id = $_POST['appointment_id'];

$query = "DELETE FROM appointments WHERE appointment_id = '$appointment_id'";
mysqli_query($conn, $query);

echo "success";
?>
