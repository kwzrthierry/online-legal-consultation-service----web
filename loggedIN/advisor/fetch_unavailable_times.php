<?php
$conn = mysqli_connect('localhost', 'root', '', 'olcs');
$lawyer_id = $_POST['lawyer_id'];
$date = $_POST['date'];

$query = "SELECT time, duration FROM appointments WHERE lawyer_id = '$lawyer_id' AND date = '$date'";
$result = mysqli_query($conn, $query);

$unavailable_times = [];
while ($row = mysqli_fetch_assoc($result)) {
    $unavailable_times[] = [
        'time' => $row['time'],
        'duration' => $row['duration']
    ];
}

echo json_encode($unavailable_times);
?>
