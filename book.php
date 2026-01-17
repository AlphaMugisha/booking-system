<?php 
session_start(); // <--- START SESSION

include 'db.php'; 

// 1. CHECK LOGIN (Security)
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them to login page
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id']; // <--- GET USER ID

// 2. GET TRIP ID
if (!isset($_GET['trip_id']) && !isset($_POST['trip_id'])) {
    header("Location: index.php"); exit();
}
$trip_id = isset($_GET['trip_id']) ? $_GET['trip_id'] : $_POST['trip_id'];

// 3. GET TRIP DETAILS
$trip = $conn->query("SELECT trips.*, routes.origin, routes.destination, routes.price, trips.total_seats 
                      FROM trips JOIN routes ON trips.route_id = routes.id 
                      WHERE trips.id = $trip_id")->fetch_assoc();

// 4. GET RESERVED SEATS
$reserved_seats = [];
$res_q = $conn->query("SELECT seat_number FROM bookings WHERE trip_id = $trip_id");
while($r = $res_q->fetch_assoc()) {
    $reserved_seats[] = $r['seat_number'];
}

// 5. HANDLE SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $boarding = $_POST['boarding_point'];
    $seat_no = $_POST['selected_seat']; 
    $ticket_code = rand(10000000, 99999999); 

    // Check if seat is taken
    if (in_array($seat_no, $reserved_seats)) {
        echo "<script>alert('Sorry! That seat was just taken.');</script>";
    } else {
        // --- THIS IS THE FIXED SQL QUERY ---
        // We added 'user_id' to the list of columns and values
        $sql = "INSERT INTO bookings (trip_id, user_id, passenger_name, phone_number, boarding_point, seat_number, ticket_code, status)
                VALUES ('$trip_id', '$user_id', '$name', '$phone', '$boarding', '$seat_no', '$ticket_code', 'pending')";
        
        if ($conn->query($sql)) {
            echo "<script>alert('Booking Successful! Check My Tickets.'); window.location='my_tickets.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Seat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; display: flex; justify-content: center; padding: 40px; }
        .container { display: flex; gap: 40px; background: white; padding: 30px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .bus-layout { background: #e2e8f0; padding: 20px; border-radius: 20px 20px 5px 5px; width: 220px; }
        .seats-grid { display: grid; grid-template-columns: 1fr 1fr 40px 1fr 1fr; gap: 10px; }
        .aisle { grid-column: 3; }
        .seat { height: 40px; background: white; border: 2px solid #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; color: #64748b; }
        .seat:hover { border-color: #2563eb; color: #2563eb; }
        .seat.selected { background: #2563eb; color: white; border-color: #2563eb; }
        .seat.taken { background: #ef4444; color: white; border-color: #ef4444; cursor: not-allowed; opacity: 0.6; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; }
        .btn-confirm { width: 100%; padding: 15px; background: #2563eb; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-confirm:disabled { background: #cbd5e1; cursor: not-allowed; }
        /* --- PRELOADER STYLES --- */
#page-loader {
    position: fixed;
    inset: 0;
    background: var(--bg-body); /* Matches your theme */
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.5s ease, visibility 0.5s;
}

.loader-wrapper {
    position: relative;
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* The Bus Icon */
.loader-wrapper i {
    font-size: 2.5rem;
    color: var(--primary);
    z-index: 10;
}

/* The Spinning Blue Circle */
.spinning-ring {
    position: absolute;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top: 3px solid var(--primary); /* The blue part */
    border-right: 3px solid var(--primary);
    animation: spin 1s linear infinite;
}

/* Glow Effect around the ring */
.spinning-ring::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 3px solid var(--primary);
    opacity: 0.2;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Class to hide the loader */
.loader-hidden {
    opacity: 0;
    visibility: hidden;
}
#page-loader {
    position: fixed;
    inset: 0;
    background: var(--bg-body);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    /* Faster transition */
    transition: opacity 0.3s ease-out; 
}

.loader-wrapper {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* The Spinning Blue Circle - increased speed to match the fast fade */
.spinning-ring {
    position: absolute;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top: 3px solid var(--primary);
    border-right: 3px solid var(--primary);
    animation: spin 0.6s linear infinite; /* Faster spin */
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
    </style>
</head>
<body>
    <div id="page-loader">
    <div class="loader-wrapper">
        <i class="ph-fill ph-bus"></i>
        <div class="spinning-ring"></div>
    </div>
</div>

<form method="post" class="container">
    <input type="hidden" name="trip_id" value="<?php echo $trip_id; ?>">
    <input type="hidden" id="selected_seat_input" name="selected_seat" required>

    <div>
        <h3 style="text-align:center;">Select Seat</h3>
        <div class="bus-layout">
            <div style="background:#94a3b8; height:5px; margin-bottom:20px; text-align:center; color:white; font-size:0.7em;">DRIVER</div>
            <div class="seats-grid">
                <?php
                for ($i = 1; $i <= $trip['total_seats']; $i++) {
                    $is_taken = in_array($i, $reserved_seats) ? 'taken' : '';
                    echo "<div class='seat $is_taken' onclick='selectSeat(this, $i)'>$i</div>";
                    if ($i % 2 == 0 && $i % 4 != 0) echo "<div class='aisle'></div>";
                }
                ?>
            </div>
        </div>
    </div>

    <div style="width: 350px;">
        <h2>Confirm Booking</h2>
        <p><?php echo $trip['origin']; ?> ➝ <?php echo $trip['destination']; ?> <br> <b><?php echo number_format($trip['price']); ?> RWF</b></p>
        
        <label>Passenger Name</label>
        <input type="text" name="full_name" required>
        <label>Phone Number</label>
        <input type="text" name="phone" required>
        <label>Boarding Point</label>
        <select name="boarding_point">
            <option>Main Station</option>
            <option>Nyabugogo</option>
            <option>Remera</option>
        </select>
        
        <div style="margin: 20px 0; font-weight:bold; color:#2563eb;">Seat Selected: <span id="display_seat">None</span></div>
        <button type="submit" class="btn-confirm" id="btn_submit" disabled>Confirm & Pay</button>
    </div>
</form>

<script>
    function selectSeat(el, num) {
        if (el.classList.contains('taken')) return;
        document.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('selected_seat_input').value = num;
        document.getElementById('display_seat').innerText = "#" + num;
        document.getElementById('btn_submit').disabled = false;
        window.addEventListener("load", function() {
    const loader = document.getElementById("page-loader");
    // Add a slight delay so people can actually see the cool animation
    setTimeout(() => {
        loader.classList.add("loader-hidden");
    }, 800); 
});
    }
    // Fires as soon as the basic page structure is ready
document.addEventListener("DOMContentLoaded", function() {
    const loader = document.getElementById("page-loader");
    
    // Short delay for a professional "blink" effect
    setTimeout(() => {
        loader.style.opacity = "0";
        loader.style.pointerEvents = "none"; // Allows clicking through immediately
        
        // Fully remove from DOM so it doesn't eat CPU
        setTimeout(() => {
            loader.style.display = "none";
        }, 300); 
    }, 300); // 0.3 seconds total wait time
});
</script>

</body>
</html>