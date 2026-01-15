<?php 
session_start();
include 'db.php'; 

// 1. FORCE LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_name = $_SESSION['user_name'];

// 2. FETCH LOCATIONS
$origins = $conn->query("SELECT DISTINCT origin FROM routes");
$destinations = $conn->query("SELECT DISTINCT destination FROM routes");

// 3. MAIN SEARCH QUERY
$sql = "SELECT trips.id, trips.departure_time, trips.total_seats, trips.bus_plate,
               routes.origin, routes.destination, routes.price, routes.duration,
               companies.name AS company_name, 
               (SELECT COUNT(*) FROM bookings WHERE bookings.trip_id = trips.id) AS booked_count
        FROM trips 
        JOIN routes ON trips.route_id = routes.id
        LEFT JOIN companies ON trips.company_id = companies.id
        WHERE 1=1";

// 4. APPLY FILTERS
if (isset($_GET['search'])) {
    $from = $_GET['from'];
    $to = $_GET['to'];

    if (!empty($from)) {
        $sql .= " AND routes.origin = '$from'";
    }
    if (!empty($to)) {
        $sql .= " AND routes.destination = '$to'";
    }
}

$sql .= " ORDER BY trips.departure_time ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Find a Bus | Volcano Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        /* --- 1. SAME THEME VARIABLES AS INDEX --- */
        :root {
            --bg-body: #f8fafc;
            --bg-nav: rgba(255, 255, 255, 0.85);
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-glow: rgba(37, 99, 235, 0.3);
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
            --primary-glow: rgba(96, 165, 250, 0.3);
            --border: #334155;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.5);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s, border-color 0.3s, color 0.3s; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; padding-bottom: 50px; }

        /* --- 2. GLASS NAVBAR --- */
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
        .user-menu a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.9rem; }
        .theme-btn {
            background: transparent; border: 1px solid var(--border); color: var(--text-main);
            width: 35px; height: 35px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }

        /* --- 3. HEADER & SEARCH BOX --- */
        .search-header { 
            padding: 60px 20px 100px; text-align: center; 
            background: linear-gradient(to bottom, #0f172a, #1e293b); color: white;
        }
        .search-header h2 { font-size: 2.2rem; margin-bottom: 10px; }

        .search-container { max-width: 900px; margin: -50px auto 40px; padding: 0 20px; }
        .search-box {
            background: var(--bg-card); padding: 25px; border-radius: 20px;
            box-shadow: var(--shadow); border: 1px solid var(--border);
            display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;
        }
        .input-group { flex: 1; min-width: 200px; }
        .input-group label { display: block; font-size: 0.75rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; }
        select { 
            width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 12px; 
            background: var(--bg-body); color: var(--text-main); font-size: 1rem; font-family: inherit;
        }
        .btn-search {
            background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 12px;
            font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; height: 48px;
            box-shadow: 0 10px 20px -5px var(--primary-glow);
        }

        /* --- 4. TICKET CARDS --- */
        .results-container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        .ticket-card {
            background: var(--bg-card); border-radius: 24px; border: 1px solid var(--border); 
            margin-bottom: 25px; display: flex; justify-content: space-between; 
            overflow: hidden; box-shadow: var(--shadow); transition: 0.3s;
        }
        .ticket-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        
        .ticket-left { padding: 30px; flex: 1; }
        .company-badge { 
            display: inline-flex; align-items: center; gap: 6px; 
            font-size: 0.75rem; font-weight: 800; color: var(--text-muted); 
            text-transform: uppercase; margin-bottom: 15px;
            background: var(--bg-body); padding: 5px 12px; border-radius: 8px; border: 1px solid var(--border);
        }
        .route { font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
        .details { display: flex; flex-wrap: wrap; gap: 20px; color: var(--text-muted); font-size: 0.9rem; }
        .detail-item { display: flex; align-items: center; gap: 8px; }

        .ticket-right {
            background: var(--bg-body); padding: 30px; width: 220px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; border-left: 2px dashed var(--border); text-align: center;
        }
        .price { font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-bottom: 5px; }
        .btn-book { 
            background: var(--text-main); color: var(--bg-body); text-decoration: none; 
            padding: 12px 24px; border-radius: 12px; font-weight: 700; width: 100%; transition: 0.2s; 
        }
        .btn-book:hover { background: var(--primary); color: white; }

        @media (max-width: 768px) {
            .ticket-card { flex-direction: column; }
            .ticket-right { width: 100%; border-left: none; border-top: 2px dashed var(--border); }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="logo"><i class="ph-fill ph-bus"></i> Volcano Digital</a>
        <div class="user-menu">
            <span style="font-weight: 600;">Hi, <?php echo $user_name; ?></span>
            <a href="my_tickets.php">My Tickets</a>
            <a href="logout.php" style="color:#ef4444;">Logout</a>
            <button class="theme-btn" onclick="toggleTheme()">
                <i id="theme-icon" class="ph ph-moon"></i>
            </button>
        </div>
    </nav>

    <div class="search-header">
        <h2>Plan Your Trip</h2>
        <p>Search across multiple agencies and secure your seat.</p>
    </div>

    <div class="search-container">
        <form method="get" class="search-box">
            <div class="input-group">
                <label>Leaving From</label>
                <select name="from">
                    <option value="">Any Location</option>
                    <?php 
                    $origins->data_seek(0);
                    while($row = $origins->fetch_assoc()) {
                        $selected = (isset($_GET['from']) && $_GET['from'] == $row['origin']) ? 'selected' : '';
                        echo "<option value='".$row['origin']."' $selected>".$row['origin']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="input-group">
                <label>Going To</label>
                <select name="to">
                    <option value="">Any Destination</option>
                    <?php 
                    $destinations->data_seek(0);
                    while($row = $destinations->fetch_assoc()) {
                        $selected = (isset($_GET['to']) && $_GET['to'] == $row['destination']) ? 'selected' : '';
                        echo "<option value='".$row['destination']."' $selected>".$row['destination']."</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="search" class="btn-search">
                <i class="ph-bold ph-magnifying-glass"></i> Search
            </button>
        </form>
    </div>

    <div class="results-container">
        <p style="color:var(--text-muted); margin-bottom: 20px;"><?php echo $result->num_rows; ?> Buses Found</p>

        <?php while($row = $result->fetch_assoc()): 
            $seats_left = $row['total_seats'] - $row['booked_count'];
        ?>
            <div class="ticket-card">
                <div class="ticket-left">
                    <div class="company-badge">
                        <i class="ph-fill ph-bus"></i> 
                        <?php echo $row['company_name'] ?? 'Volcano Express'; ?>
                    </div>

                    <div class="route">
                        <?php echo $row['origin']; ?> 
                        <i class="ph-bold ph-arrow-right" style="color:var(--text-muted); font-size:1.1rem;"></i> 
                        <?php echo $row['destination']; ?>
                    </div>
                    
                    <div class="details">
                        <div class="detail-item"><i class="ph-bold ph-calendar-blank"></i> <?php echo date("D, M d", strtotime($row['departure_time'])); ?></div>
                        <div class="detail-item"><i class="ph-bold ph-clock"></i> <?php echo date("h:i A", strtotime($row['departure_time'])); ?></div>
                        <div class="detail-item"><i class="ph-bold ph-hash"></i> <?php echo $row['bus_plate']; ?></div>
                    </div>
                </div>

                <div class="ticket-right">
                    <div class="price"><?php echo number_format($row['price']); ?> <span style="font-size:0.8rem;">RWF</span></div>
                    <div style="font-size:0.85rem; margin-bottom:15px; font-weight:700; color:<?php echo ($seats_left < 5) ? '#ef4444' : '#22c55e'; ?>">
                        <?php echo $seats_left; ?> seats left
                    </div>
                    
                    <?php if($seats_left > 0): ?>
                        <a href="book.php?trip_id=<?php echo $row['id']; ?>" class="btn-book">Book Now</a>
                    <?php else: ?>
                        <div style="background:var(--border); color:var(--text-muted); padding:12px; border-radius:12px; font-weight:bold; width:100%;">Sold Out</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
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
    </script>
</body>
</html>