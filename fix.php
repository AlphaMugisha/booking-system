<?php
include 'db.php';

// This command adds the missing column
$sql = "ALTER TABLE bookings ADD COLUMN boarding_point VARCHAR(100) NOT NULL DEFAULT 'Main Station'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Success! Database updated. You can now delete this file and try booking again.";
} else {
    echo "Error: " . $conn->error;
}
?>