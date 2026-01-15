<?php include 'db.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volcano Express Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --light: #f1f5f9;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--light); margin: 0; padding-bottom: 50px; }

        /* HEADER */
        .header { background-color: var(--dark); padding: 20px 0; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 800; color: white; text-decoration: none; }
        .nav-link { color: #cbd5e1; text-decoration: none; margin-left: 20px; transition: 0.3s; }
        .nav-link:hover { color: white; }

        /* HERO SECTION */
        .hero { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; padding: 60px 0; text-align: center; margin-bottom: 40px; }
        .hero h1 { margin: 0; font-size: 2.5rem; }
        .hero p { color: #cbd5e1; margin-top: 10px; font-size: 1.1rem; }

        /* BUS CARDS GRID */
        .bus-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }

        .bus-card { 
            background: white; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e2e8f0;
        }
        .bus-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

        /* CARD HEADER (Route) */
        .card-header { background: #f8fafc; padding: 20px; border-bottom: 1px solid #e2e8f0; }
        .route { font-size: 1.2rem; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 10px; }
        .arrow { color: var(--primary); font-size: 1.2rem; }

        /* CARD BODY (Details) */
        .card-body { padding: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem; color: #475569; }
        .price-tag { font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-top: 10px; display: block; }
        
        /* SEAT INDICATOR */
        .seats-badge { 
            display: inline-block; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 0.85rem; 
            font-weight: 600;
            background: #f1f5f9;
        }

        /* BUTTON */
        .btn-book { 
            display: block; 
            width: 100%; 
            padding: 15px; 
            background-color: var(--primary); 
            color: white; 
            text-align: center; 
            text-decoration: none; 
            font-weight: 600; 
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-book:hover { background-color: #1d4ed8; }
        .btn-disabled { background-color: #cbd5e1; cursor: not-allowed; }

    </style>
</head>
<body>

    <div class="header">
        <div class="container nav">
            <a href="index.php" class="logo">🌋 Volcano Digital</a>
            <div>
                <a href="#" class="nav-link">Check Booking</a>
                <a href="#" class="nav-link">My Tickets</a>
                <a href="admin.php" class="nav-link" style="color: #fbbf24;">Admin Login</a>
            </div>
        </div>
    </div>

    <div class="hero">
        <div class="container">
            <h1>Where are you going today?</h1>
            <p>Book your ticket from your phone. No queues, no paper.</p>
        </div>
    </div>

    <div class="container">
        <div class="bus-grid">

            <?php
            // SQL: Join Trips + Routes + Count Bookings
            $sql = "SELECT trips.id, trips.departure_time, trips.total_seats, trips.bus_plate,
                           routes.origin, routes.destination, routes.price, routes.duration,
                           (SELECT COUNT(*) FROM bookings WHERE bookings.trip_id = trips.id) AS booked_count
                    FROM trips 
                    JOIN routes ON trips.route_id = routes.id
                    ORDER BY trips.departure_time ASC";
                    
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    // --- LOGIC ---
                    $seats_left = $row['total_seats'] - $row['booked_count'];
                    $time_formatted = date("h:i A", strtotime($row['departure_time'])); // e.g. 08:00 AM
                    $date_formatted = date("D, M d", strtotime($row['departure_time'])); // e.g. Mon, Jan 15

                    // Determine Seat Color
                    $seat_color = ($seats_left < 5) ? "#ef4444" : "#22c55e"; // Red if low, Green if many
                    $seat_bg = ($seats_left < 5) ? "#fee2e2" : "#dcfce7";
                    
                    // --- CARD HTML ---
                    echo "<div class='bus-card'>";
                    
                    // Header
                    echo "<div class='card-header'>";
                    echo "<div class='route'>" . $row['origin'] . " <span class='arrow'>➜</span> " . $row['destination'] . "</div>";
                    echo "</div>";

                    // Body
                    echo "<div class='card-body'>";
                    echo "<div class='info-row'><span>📅 Date:</span> <b>$date_formatted</b></div>";
                    echo "<div class='info-row'><span>⏰ Time:</span> <b>$time_formatted</b></div>";
                    echo "<div class='info-row'><span>🚍 Bus:</span> <span>" . $row['bus_plate'] . "</span></div>";
                    echo "<div class='info-row'><span>⏳ Duration:</span> <span>" . $row['duration'] . "</span></div>";
                    
                    echo "<div style='margin-top:15px; display:flex; justify-content:space-between; align-items:center;'>";
                    echo "<span class='price-tag'>" . number_format($row['price']) . " RWF</span>";
                    
                    // Seat Badge
                    echo "<span class='seats-badge' style='color:$seat_color; background:$seat_bg;'>
                          $seats_left Seats Left</span>";
                    echo "</div>";
                    echo "</div>"; // End Body

                    // Button
                    if ($seats_left > 0) {
                        echo "<a href='book.php?trip_id=" . $row["id"] . "' class='btn-book'>Book Now</a>";
                    } else {
                        echo "<div class='btn-book btn-disabled'>SOLD OUT</div>";
                    }

                    echo "</div>"; // End Card
                }
            } else {
                echo "<p style='text-align:center; width:100%; color:#64748b;'>No buses scheduled. Please check back later.</p>";
            }
            ?>

        </div>
    </div>

</body>
</html>