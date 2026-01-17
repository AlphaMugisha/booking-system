<?php 
session_start();
include 'db.php'; 

// 1. FORCE LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 2. FETCH TICKETS FOR THIS USER
$sql = "SELECT bookings.*, trips.departure_time, trips.bus_plate, 
               routes.origin, routes.destination, routes.price,
               companies.name AS company_name
        FROM bookings 
        JOIN trips ON bookings.trip_id = trips.id 
        JOIN routes ON trips.route_id = routes.id
        LEFT JOIN companies ON trips.company_id = companies.id
        WHERE bookings.user_id = $user_id
        ORDER BY trips.departure_time DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>My Tickets | Volcano Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-nav: rgba(255, 255, 255, 0.85);
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --border: #e2e8f0;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        }

        [data-theme="dark"] {
            --bg-body: #020617;
            --bg-nav: rgba(2, 6, 23, 0.85);
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #60a5fa;
            --border: #334155;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s, border-color 0.3s, color 0.3s; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; padding-bottom: 50px; }

        /* NAVBAR */
        nav {
            background: var(--bg-nav);
            backdrop-filter: blur(16px);
            padding: 15px 20px;
            position: sticky; top: 0; z-index: 1000;
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .logo { font-weight: 800; font-size: 1.3rem; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .user-menu { display: flex; gap: 20px; align-items: center; }

        /* HEADER */
        .page-header { 
            padding: 60px 20px; text-align: center; 
            background: linear-gradient(to bottom, #0f172a, #1e293b); color: white;
        }
        .page-header h2 { font-size: 2.2rem; margin-bottom: 10px; font-weight: 700; }

        /* TICKETS LIST */
        .tickets-container { max-width: 800px; margin: -30px auto 40px; padding: 0 20px; }
        
        .ticket-card {
            background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border); 
            margin-bottom: 25px; display: flex; flex-direction: column;
            overflow: hidden; box-shadow: var(--shadow); position: relative;
        }

        .ticket-main { padding: 30px; display: flex; justify-content: space-between; align-items: center; }
        
        .ticket-info { flex: 1; }
        .company-name { font-weight: 700; color: var(--primary); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 5px; }
        .route { font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        
        .ticket-meta { display: flex; gap: 20px; color: var(--text-muted); font-size: 0.9rem; }
        .meta-item { display: flex; align-items: center; gap: 6px; }

        .ticket-side { 
            padding: 30px; border-left: 2px dashed var(--border); 
            text-align: center; min-width: 180px; background: rgba(0,0,0,0.02);
        }
        .seat-badge { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1; }
        .seat-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 15px; }

        /* STATUS BADGES */
        .badge { padding: 6px 14px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; display: inline-block; }
        .badge-paid { background: rgba(34, 197, 94, 0.15); color: #22c55e; }
        .badge-pending { background: rgba(234, 179, 8, 0.15); color: #eab308; }

        /* TICKET CODE SECTION */
        .ticket-footer { background: var(--bg-body); padding: 15px 30px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .ticket-code { font-family: monospace; font-size: 1.1rem; font-weight: 700; color: var(--text-main); }

        .theme-btn {
            background: transparent; border: 1px solid var(--border); color: var(--text-main);
            width: 35px; height: 35px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }

        @media (max-width: 600px) {
            .ticket-main { flex-direction: column; align-items: flex-start; gap: 20px; }
            .ticket-side { width: 100%; border-left: none; border-top: 2px dashed var(--border); }
        }
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

    <nav>
        <a href="index.php" class="logo"><i class="ph-fill ph-bus"></i> Volcano Digital</a>
        <div class="user-menu">
            <a href="search.php">Find Buses</a>
            <button class="theme-btn" onclick="toggleTheme()">
                <i id="theme-icon" class="ph ph-moon"></i>
            </button>
        </div>
    </nav>

    <div class="page-header">
        <h2>My Boarding Passes</h2>
        <p>Hello, <?php echo $user_name; ?>. Here are your upcoming trips.</p>
    </div>

    <div class="tickets-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="ticket-card">
                    <div class="ticket-main">
                        <div class="ticket-info">
                            <div class="company-name"><?php echo $row['company_name'] ?? 'Volcano Express'; ?></div>
                            <div class="route">
                                <?php echo $row['origin']; ?> 
                                <i class="ph-bold ph-arrow-right" style="color:var(--text-muted); font-size:1rem;"></i> 
                                <?php echo $row['destination']; ?>
                            </div>
                            <div class="ticket-meta">
                                <div class="meta-item"><i class="ph-bold ph-calendar-blank"></i> <?php echo date("M d, Y", strtotime($row['departure_time'])); ?></div>
                                <div class="meta-item"><i class="ph-bold ph-clock"></i> <?php echo date("h:i A", strtotime($row['departure_time'])); ?></div>
                                <div class="meta-item"><i class="ph-bold ph-bus"></i> <?php echo $row['bus_plate']; ?></div>
                            </div>
                        </div>

                        <div class="ticket-side">
                            <div class="seat-label">Seat Number</div>
                            <div class="seat-badge"><?php echo $row['seat_number']; ?></div>
                            <div style="margin-top:10px;">
                                <?php if($row['status'] == 'paid'): ?>
                                    <span class="badge badge-paid">PAID</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">PENDING</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-footer">
                        <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">TICKET CODE</span>
                        <span class="ticket-code"><?php echo $row['ticket_code']; ?></span>
                        <i class="ph-bold ph-qr-code" style="font-size:1.5rem;"></i>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:100px 20px; background:var(--bg-card); border-radius:24px; border:1px solid var(--border);">
                <i class="ph ph-ticket" style="font-size:4rem; color:var(--text-muted); margin-bottom:20px;"></i>
                <h3>No tickets found</h3>
                <p style="color:var(--text-muted); margin-bottom:30px;">You haven't booked any trips yet.</p>
                <a href="search.php" style="background:var(--primary); color:white; padding:12px 30px; border-radius:12px; text-decoration:none; font-weight:700;">Book a Trip Now</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const current = html.getAttribute('data-theme');
            
            if (current === 'light') {
                html.setAttribute('data-theme', 'dark');
                icon.classList.replace('ph-moon', 'ph-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                html.setAttribute('data-theme', 'light');
                icon.classList.replace('ph-sun', 'ph-moon');
                localStorage.setItem('theme', 'light');
            }
        }
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if(savedTheme === 'dark') document.getElementById('theme-icon').classList.replace('ph-moon', 'ph-sun');
        window.addEventListener("load", function() {
    const loader = document.getElementById("page-loader");
    // Add a slight delay so people can actually see the cool animation
    setTimeout(() => {
        loader.classList.add("loader-hidden");
    }, 800); 
});
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