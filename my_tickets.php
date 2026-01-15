<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Tickets</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 20px; }
        .ticket-card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .status-paid { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 20px; font-size: 0.8em; }
        .status-pending { background: #fef3c7; color: #b45309; padding: 5px 10px; border-radius: 20px; font-size: 0.8em; }
    </style>
</head>
<body>

<h1>🎫 My Trip History</h1>
<a href="index.php" style="text-decoration:none; color:#2563eb;">⬅ Book New Trip</a><br><br>

<?php
$sql = "SELECT bookings.*, trips.departure_time, routes.origin, routes.destination 
        FROM bookings 
        JOIN trips ON bookings.trip_id = trips.id 
        JOIN routes ON trips.route_id = routes.id 
        WHERE bookings.user_id = $user_id 
        ORDER BY bookings.id DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $status_class = ($row['status'] == 'paid') ? 'status-paid' : 'status-pending';
        
        echo "<div class='ticket-card'>";
        echo "<div>";
        echo "<h3>" . $row['origin'] . " ➝ " . $row['destination'] . "</h3>";
        echo "<p>📅 " . date("d M, Y H:i", strtotime($row['departure_time'])) . "</p>";
        echo "<p>💺 Seat: <b>" . $row['seat_number'] . "</b> | Ticket #: " . $row['ticket_code'] . "</p>";
        echo "</div>";
        
        echo "<div style='text-align:right;'>";
        echo "<span class='$status_class'>" . strtoupper($row['status']) . "</span><br><br>";
        // Button to view QR again
        echo "<a href='#' style='color:#64748b; font-size:0.9em;'>View QR</a>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "You haven't booked any trips yet.";
}
?>

</body>
</html>