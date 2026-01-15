<?php 
include 'db.php'; 

// --- 1. HANDLE ACTIONS ---

// ADD COMPANY
if (isset($_POST['add_company'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $conn->query("INSERT INTO companies (name, contact_phone) VALUES ('$name', '$phone')");
    echo "<script>window.location='admin.php?page=companies';</script>";
}
// DELETE COMPANY
if (isset($_GET['delete_company'])) {
    $id = $_GET['delete_company'];
    $conn->query("DELETE FROM companies WHERE id=$id");
    header("Location: admin.php?page=companies"); exit();
}
// ADD ROUTE
if (isset($_POST['add_route'])) {
    $origin = $_POST['origin'];
    $dest = $_POST['destination'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $conn->query("INSERT INTO routes (origin, destination, price, duration) VALUES ('$origin', '$dest', '$price', '$duration')");
    echo "<script>window.location='admin.php?page=routes';</script>";
}
// DELETE ROUTE
if (isset($_GET['delete_route'])) {
    $id = $_GET['delete_route'];
    $conn->query("DELETE FROM routes WHERE id=$id");
    header("Location: admin.php?page=routes"); exit();
}
// ADD TRIP
if (isset($_POST['add_trip'])) {
    $company_id = $_POST['company_id']; 
    $route_id = $_POST['route_id'];
    $plate = $_POST['bus_plate'];
    $time = $_POST['departure_time'];
    $seats = $_POST['total_seats'];
    $conn->query("INSERT INTO trips (company_id, route_id, bus_plate, departure_time, total_seats) VALUES ('$company_id', '$route_id', '$plate', '$time', '$seats')");
    echo "<script>window.location='admin.php?page=buses';</script>";
}
// DELETE TRIP
if (isset($_GET['delete_trip'])) {
    $id = $_GET['delete_trip'];
    $conn->query("DELETE FROM trips WHERE id=$id");
    header("Location: admin.php?page=buses"); exit();
}
// APPROVE TICKET
if (isset($_GET['approve_ticket'])) {
    $code = $_GET['approve_ticket'];
    $tid = $_GET['trip_id'];
    $conn->query("UPDATE bookings SET status='paid' WHERE ticket_code='$code'");
    header("Location: admin.php?page=manifest&trip_id=$tid"); exit();
}

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; 
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Volcano Admin | Command Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <style>
        /* --- CSS VARIABLES FOR THEME --- */
        :root {
            /* Light Mode Defaults */
            --bg-body: #f8fafc;
            --bg-sidebar: #0f172a;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --primary: #3b82f6;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --glass-bg: rgba(0, 0, 0, 0.05);
            --glass-border: rgba(0, 0, 0, 0.1);
        }

        /* Dark Mode Overrides */
        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-sidebar: #020617;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
            --primary: #60a5fa;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s, color 0.3s; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: var(--text-main); display: flex; height: 100vh; overflow: hidden; }

        /* --- SIDEBAR --- */
        .sidebar { width: 260px; background-color: var(--bg-sidebar); color: white; display: flex; flex-direction: column; flex-shrink: 0; }
        .brand { padding: 30px; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 10px; color: white; text-decoration: none; }
        
        .menu { flex: 1; padding: 0 15px; }
        .menu-item {
            display: flex; align-items: center; gap: 12px; padding: 16px; margin-bottom: 5px;
            color: #94a3b8; text-decoration: none; border-radius: 12px; font-weight: 500; font-size: 0.95rem;
        }
        .menu-item:hover { background: rgba(255,255,255,0.05); color: white; }
        .menu-item.active { background: var(--primary); color: white; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4); }
        .menu-item i { font-size: 1.2rem; }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; overflow-y: auto; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h2 { font-size: 2rem; font-weight: 700; }
        
        /* THEME TOGGLE (Updated with your Glass Style) */
        .theme-toggle { 
            cursor: pointer; 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; /* Perfect Circle */
            
            /* Glass Effect */
            background: var(--glass-bg); 
            border: 1px solid var(--glass-border); 
            backdrop-filter: blur(4px); 
            
            color: var(--text-main); 
            font-size: 1.5rem; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            box-shadow: 0 0 15px var(--primary); /* Glow effect */
            transform: scale(1.1);
            border-color: var(--primary);
        }

        /* --- CARDS & GRID --- */
        .grid-2 { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .card { background: var(--bg-card); padding: 30px; border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 30px; }
        
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 30px; }
        .stat-card { background: var(--bg-card); padding: 25px; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .stat-info h4 { color: var(--text-muted); font-weight: 500; font-size: 0.9rem; margin-bottom: 5px; }
        .stat-info .num { font-size: 1.8rem; font-weight: 700; color: var(--text-main); }

        /* --- TABLES --- */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { text-align: left; padding: 15px; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid var(--border); }
        td { padding: 15px; border-bottom: 1px solid var(--border); color: var(--text-main); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        
        /* --- FORMS --- */
        input, select {
            width: 100%; padding: 14px; margin-top: 8px; margin-bottom: 20px;
            background: var(--bg-body); border: 1px solid var(--border); border-radius: 10px;
            color: var(--text-main); font-family: 'Outfit', sans-serif; font-size: 0.95rem;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); }
        label { color: var(--text-muted); font-size: 0.9rem; font-weight: 600; }
        
        button {
            width: 100%; padding: 15px; background: var(--primary); color: white;
            border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem;
        }
        button:hover { opacity: 0.9; }

        /* --- BADGES --- */
        .badge { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .bg-blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .bg-green { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .bg-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .bg-yellow { background: rgba(234, 179, 8, 0.1); color: #eab308; }

        .btn-sm { padding: 8px 14px; border-radius: 8px; font-size: 0.8rem; text-decoration: none; display:inline-block; font-weight:600; }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="#" class="brand">
            <i class="ph-fill ph-bus"></i> Volcano Admin
        </a>
        <div class="menu">
            <a href="admin.php?page=dashboard" class="menu-item <?php if($page=='dashboard') echo 'active'; ?>">
                <i class="ph ph-squares-four"></i> Dashboard
            </a>
            <a href="admin.php?page=companies" class="menu-item <?php if($page=='companies') echo 'active'; ?>">
                <i class="ph ph-buildings"></i> Companies
            </a>
            <a href="admin.php?page=routes" class="menu-item <?php if($page=='routes') echo 'active'; ?>">
                <i class="ph ph-path"></i> Routes
            </a>
            <a href="admin.php?page=buses" class="menu-item <?php if($page=='buses') echo 'active'; ?>">
                <i class="ph ph-bus"></i> Fleet Schedule
            </a>
        </div>
        <div style="padding: 20px; font-size: 0.8rem; color: #64748b; text-align: center;">
            v2.0.0 Pro
        </div>
    </div>

    <div class="main-content">
        
        <div class="header">
            <div>
                <h2 style="text-transform: capitalize;"><?php echo $page; ?></h2>
                <p style="color:var(--text-muted);">Overview of your system performance</p>
            </div>
            <button class="theme-toggle" onclick="toggleTheme()">
                <i id="theme-icon" class="ph ph-moon"></i>
            </button>
        </div>

        <?php if ($page == 'dashboard') { 
            // Calculate Dashboard Stats
            $rev = $conn->query("SELECT SUM(routes.price) FROM bookings JOIN trips ON bookings.trip_id = trips.id JOIN routes ON trips.route_id = routes.id WHERE bookings.status='paid'")->fetch_row()[0];
            $tix = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
            $bus = $conn->query("SELECT COUNT(*) FROM trips")->fetch_row()[0];
        ?>
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-green"><i class="ph ph-money"></i></div>
                    <div class="stat-info">
                        <h4>Total Revenue</h4>
                        <div class="num"><?php echo number_format($rev); ?> RWF</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-blue"><i class="ph ph-ticket"></i></div>
                    <div class="stat-info">
                        <h4>Tickets Sold</h4>
                        <div class="num"><?php echo number_format($tix); ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-yellow"><i class="ph ph-bus"></i></div>
                    <div class="stat-info">
                        <h4>Active Buses</h4>
                        <div class="num"><?php echo $bus; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3>Recent Activity</h3>
                <p style="color:var(--text-muted); margin-top:10px;">System is running smoothly. Use the sidebar to manage specific modules.</p>
            </div>
        <?php } ?>

        <?php if ($page == 'companies') { ?>
            <div class="grid-2">
                <div class="card">
                    <h3>Add Company</h3>
                    <form method="post">
                        <label>Company Name</label>
                        <input type="text" name="name" placeholder="e.g. Ritco" required>
                        <label>Contact Phone</label>
                        <input type="text" name="phone" placeholder="078..." required>
                        <button type="submit" name="add_company">Create Company</button>
                    </form>
                </div>
                <div class="card">
                    <h3>Partner Agencies</h3>
                    <table>
                        <thead><tr><th>Name</th><th>Contact</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php $res = $conn->query("SELECT * FROM companies");
                            while($c = $res->fetch_assoc()) {
                                echo "<tr>
                                    <td><b>{$c['name']}</b></td>
                                    <td>{$c['contact_phone']}</td>
                                    <td><a href='admin.php?delete_company={$c['id']}' class='btn-sm bg-red'>Remove</a></td>
                                </tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>

        <?php if ($page == 'routes') { ?>
            <div class="grid-2">
                <div class="card">
                    <h3>Add New Route</h3>
                    <form method="post">
                        <label>Origin</label>
                        <input type="text" name="origin" placeholder="Kigali" required>
                        <label>Destination</label>
                        <input type="text" name="destination" placeholder="Musanze" required>
                        <label>Price (RWF)</label>
                        <input type="number" name="price" placeholder="2000" required>
                        <label>Duration</label>
                        <input type="text" name="duration" placeholder="2h" required>
                        <button type="submit" name="add_route">Create Route</button>
                    </form>
                </div>
                <div class="card">
                    <h3>Active Routes</h3>
                    <table>
                        <thead><tr><th>Origin</th><th>Dest</th><th>Price</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php $res = $conn->query("SELECT * FROM routes");
                            while($r = $res->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$r['origin']}</td>
                                    <td>{$r['destination']}</td>
                                    <td><b>".number_format($r['price'])."</b></td>
                                    <td><a href='admin.php?delete_route={$r['id']}' class='btn-sm bg-red'>Delete</a></td>
                                </tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>

        <?php if ($page == 'buses') { ?>
            <div class="grid-2">
                <div class="card">
                    <h3>Schedule Trip</h3>
                    <form method="post">
                        <label>Company</label>
                        <select name="company_id" required>
                            <?php $c = $conn->query("SELECT * FROM companies");
                            while ($r = $c->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['name']}</option>"; ?>
                        </select>
                        <label>Route</label>
                        <select name="route_id" required>
                            <?php $rt = $conn->query("SELECT * FROM routes");
                            while ($r = $rt->fetch_assoc()) echo "<option value='{$r['id']}'>{$r['origin']} - {$r['destination']}</option>"; ?>
                        </select>
                        <label>Plate Number</label>
                        <input type="text" name="bus_plate" required>
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" value="40" required>
                        <label>Departure Time</label>
                        <input type="datetime-local" name="departure_time" required>
                        <button type="submit" name="add_trip">Publish Schedule</button>
                    </form>
                </div>
                <div class="card">
                    <h3>Live Schedule</h3>
                    <table>
                        <thead><tr><th>Operator</th><th>Route</th><th>Time</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php $sql = "SELECT trips.id, trips.bus_plate, trips.departure_time, routes.origin, routes.destination, companies.name as cname FROM trips JOIN routes ON trips.route_id = routes.id LEFT JOIN companies ON trips.company_id = companies.id ORDER BY trips.departure_time ASC";
                            $trips = $conn->query($sql);
                            while($row = $trips->fetch_assoc()) {
                                echo "<tr>
                                    <td><span class='badge bg-blue'>".($row['cname'] ?? 'Unknown')."</span></td>
                                    <td>{$row['origin']} ➝ {$row['destination']}</td>
                                    <td>".date("M d, H:i", strtotime($row['departure_time']))."</td>
                                    <td>
                                        <a href='admin.php?page=manifest&trip_id={$row['id']}' class='btn-sm bg-green'>Pax</a>
                                        <a href='admin.php?delete_trip={$row['id']}' class='btn-sm bg-red'>X</a>
                                    </td>
                                </tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
        
        <?php if ($page == 'manifest') { 
            $tid = $_GET['trip_id'];
            $passengers = $conn->query("SELECT * FROM bookings WHERE trip_id=$tid ORDER BY seat_number ASC");
        ?>
            <div class="card">
                <h3>Passenger Manifest</h3>
                <table>
                    <thead><tr><th>Seat</th><th>Name</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while($p = $passengers->fetch_assoc()) {
                            $status = $p['status'] == 'pending' 
                                ? "<span class='badge bg-yellow'>Pending</span>" 
                                : "<span class='badge bg-green'>Paid</span>";
                            $btn = $p['status'] == 'pending' 
                                ? "<a href='admin.php?approve_ticket={$p['ticket_code']}&trip_id=$tid' class='btn-sm bg-green'>Approve</a>" 
                                : "Verified";
                            echo "<tr><td>{$p['seat_number']}</td><td>{$p['passenger_name']}</td><td>$status</td><td>$btn</td></tr>";
                        } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

    </div>

    <script>
        // --- THEME SWITCHER LOGIC ---
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

        // LOAD SAVED THEME
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if(savedTheme === 'dark') document.getElementById('theme-icon').classList.replace('ph-moon', 'ph-sun');
    </script>
</body>
</html>