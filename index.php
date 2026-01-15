<?php 
session_start();
include 'db.php'; 

// Check Login Status
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : "Guest";
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volcano Digital | Modern Bus Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        /* --- 1. MODERN THEME VARIABLES --- */
        :root {
            /* Light Mode Defaults */
            --bg-body: #f8fafc;
            --bg-nav: rgba(255, 255, 255, 0.85);
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-glow: rgba(37, 99, 235, 0.3);
            --border: #e2e8f0;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            --glass-border: rgba(255, 255, 255, 0.5);
        }

        [data-theme="dark"] {
            /* Dark Mode Defaults */
            --bg-body: #020617;
            --bg-nav: rgba(2, 6, 23, 0.85);
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #60a5fa;
            --primary-glow: rgba(96, 165, 250, 0.3);
            --border: #334155;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.5);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; transition: background-color 0.3s, border-color 0.3s, color 0.3s; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }

        /* --- 2. GLASS NAVBAR --- */
        nav {
            background: var(--bg-nav);
            backdrop-filter: blur(16px);
            padding: 15px 0;
            position: fixed; width: 100%; top: 0; z-index: 1000;
            border-bottom: 1px solid var(--border);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .nav-content { display: flex; justify-content: space-between; align-items: center; }
        
        .logo { font-size: 1.5rem; font-weight: 800; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo span { color: var(--text-main); }
        
        .nav-links { display: flex; gap: 30px; align-items: center; }
        .nav-links a { text-decoration: none; color: var(--text-muted); font-weight: 500; font-size: 0.95rem; transition: 0.2s; }
        .nav-links a:hover { color: var(--primary); }
        
        .btn-nav { 
            background: var(--text-main); color: var(--bg-body) !important; 
            padding: 10px 24px; border-radius: 50px; font-weight: 600; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-nav:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Theme Toggle Button */
        .theme-btn {
            background: transparent; border: 1px solid var(--border); color: var(--text-main);
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .theme-btn:hover { border-color: var(--primary); color: var(--primary); box-shadow: 0 0 15px var(--primary-glow); }

        /* --- 3. HERO SECTION WITH IMAGE --- */
        .hero {
            padding: 180px 0 140px; 
            text-align: center; 
            position: relative;
            
            /* HERE IS YOUR NEW IMAGE */
            background-image: url('nice.png'); 
            
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        
        /* Dark overlay for text readability */
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(2, 6, 23, 0.5); /* Semi-transparent dark overlay */
            z-index: 0;
        }
        
        /* Ensure content is above the overlay */
        .hero .container {
            position: relative;
            z-index: 1;
        }
        
        .hero h1 { font-size: 4.5rem; line-height: 1.1; margin-bottom: 20px; color: white; font-weight: 800; }
        .hero span { color: var(--primary); }
        .hero p { font-size: 1.25rem; color: #e2e8f0; max-width: 600px; margin: 0 auto 40px; font-weight: 500; }
        
        /* Primary Button */
        .hero-btn { 
            background: var(--primary); color: white; padding: 18px 40px; border-radius: 14px; 
            font-weight: 600; text-decoration: none; font-size: 1.1rem; display: inline-flex; align-items: center; gap: 10px; 
            box-shadow: 0 10px 30px -5px var(--primary-glow); border: none;
        }
        .hero-btn:hover { transform: translateY(-3px); box-shadow: 0 20px 40px -5px var(--primary-glow); }
        
        /* Secondary Button */
        .hero-btn-outline { 
            background: #1e293b; color: white; border: none; margin-left: 20px; 
        }
        .hero-btn-outline:hover { background: #334155; }

        /* --- 4. TRUST BAR --- */
        .trust-bar { border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 30px 0; background: var(--bg-card); }
        .trust-content { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .trust-item { display: flex; align-items: center; gap: 10px; }
        .trust-item i { font-size: 1.2rem; color: var(--primary); }

        /* --- 5. SECTIONS & CARDS --- */
        .section { padding: 100px 0; }
        .section-header { text-align: center; margin-bottom: 70px; }
        .section-header h2 { font-size: 2.5rem; color: var(--text-main); margin-bottom: 15px; }
        .section-header p { color: var(--text-muted); font-size: 1.1rem; }

        /* Features */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .feature-card { 
            background: var(--bg-card); padding: 40px 30px; border-radius: 24px; 
            border: 1px solid var(--border); transition: 0.3s; 
        }
        .feature-card:hover { transform: translateY(-10px); border-color: var(--primary); box-shadow: var(--shadow); }
        .f-icon { 
            width: 60px; height: 60px; background: var(--bg-body); border-radius: 16px; 
            color: var(--primary); display: flex; align-items: center; justify-content: center; 
            font-size: 1.8rem; margin-bottom: 25px; border: 1px solid var(--border);
        }

        /* Steps */
        .steps-bg { background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .steps-container { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 40px; }
        .step { flex: 1; min-width: 250px; text-align: center; position: relative; }
        .step-num { 
            width: 50px; height: 50px; background: var(--primary); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-weight: 700; font-size: 1.2rem; margin: 0 auto 20px; box-shadow: 0 0 20px var(--primary-glow);
        }

        /* --- 6. SCHEDULE & TICKETS --- */
        .schedule-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 30px; }
        
        .ticket-card {
            background: var(--bg-card); border-radius: 24px; overflow: hidden;
            box-shadow: var(--shadow); transition: 0.3s; border: 1px solid var(--border);
            display: flex; flex-direction: column; position: relative;
        }
        .ticket-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        
        .t-header { padding: 25px; border-bottom: 2px dashed var(--border); display: flex; justify-content: space-between; align-items: center; }
        .route-text { font-weight: 700; font-size: 1.1rem; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .price-badge { background: var(--bg-body); color: var(--primary); padding: 8px 16px; border-radius: 12px; font-weight: 800; font-size: 1.1rem; border: 1px solid var(--border); }
        
        .t-body { padding: 25px; }
        .t-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--text-muted); font-size: 0.95rem; }
        .t-row strong { color: var(--text-main); font-weight: 600; }
        
        .company-tag { 
            background: var(--bg-body); padding: 6px 12px; border-radius: 8px; 
            font-size: 0.75rem; font-weight: 800; color: var(--text-muted); 
            text-transform: uppercase; letter-spacing: 1px; display: inline-flex; 
            align-items: center; gap: 6px; margin-bottom: 20px; border: 1px solid var(--border);
        }

        .btn-book { 
            display: block; width: 100%; padding: 18px; text-align: center;
            text-decoration: none; font-weight: 700; font-size: 1rem; 
            transition: 0.2s; margin-top: auto; 
        }
        .book-active { background: var(--text-main); color: var(--bg-body); }
        .book-active:hover { background: var(--primary); color: white; }
        .book-disabled { background: var(--bg-body); color: var(--text-muted); cursor: not-allowed; pointer-events: none; border-top: 1px solid var(--border); }

        /* --- FOOTER --- */
        footer { padding: 80px 0 30px; border-top: 1px solid var(--border); background: var(--bg-body); }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; margin-bottom: 60px; }
        .f-col h4 { color: var(--text-main); margin-bottom: 25px; font-size: 1.1rem; font-weight: 700; }
        .f-col a { display: block; color: var(--text-muted); text-decoration: none; margin-bottom: 12px; transition: 0.3s; }
        .f-col a:hover { color: var(--primary); padding-left: 5px; }
        .copyright { text-align: center; border-top: 1px solid var(--border); padding-top: 30px; color: var(--text-muted); font-size: 0.9rem; }
        /* --- HERO SECTION WITH BACKGROUND IMAGE --- */
.hero {
    position: relative;
    padding: 180px 0 140px;
    text-align: center;
    
    /* Background Image Setup */
    background-image: linear-gradient(
        to bottom, 
        rgba(2, 6, 23, 0.4), /* Subtle dark top */
        rgba(2, 6, 23, 0.8)  /* Heavier dark bottom for text contrast */
    ), url('nice4.png');
    
    background-size: cover;      /* Fills the whole area */
    background-position: center; /* Keeps the bus/center visible */
    background-attachment: fixed; /* Optional: adds a cool "Parallax" scroll effect */
    background-repeat: no-repeat;
    
    /* Ensure the section takes enough height */
    min-height: 80vh;
    display: flex;
    align-items: center;
}

/* Make sure the text pops on the background */
.hero h1 {
    font-size: 4.5rem;
    line-height: 1.1;
    margin-bottom: 20px;
    color: #ffffff; /* Forced white for background readability */
    font-weight: 800;
    text-shadow: 0 4px 15px rgba(0,0,0,0.5); /* Adds depth */
}

.hero p {
    font-size: 1.25rem;
    color: #e2e8f0;
    max-width: 700px;
    margin: 0 auto 40px;
    font-weight: 500;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
}

/* Ensure container stays on top of any absolute positioning */
.hero .container {
    position: relative;
    z-index: 2;
}

    </style>
</head>
<body>

    <nav>
        <div class="container nav-content">
            <a href="index.php" class="logo">
                <i class="ph-fill ph-bus"></i> 
                Volcano<span>Digital</span>
            </a>
            
            <div class="nav-links">
                <a href="#how-it-works">How it Works</a>
                <a href="#schedule">Schedule</a>
                <a href="#">Support</a>
                
                <?php if ($is_logged_in): ?>
                    <a href="my_tickets.php" style="color:var(--text-main); font-weight:600;">My Tickets</a>
                    <a href="logout.php" style="color:#ef4444; font-weight:600;">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="font-weight:600;">Log In</a>
                    <a href="login.php" class="btn-nav">Sign Up</a>
                <?php endif; ?>

                <button class="theme-btn" onclick="toggleTheme()">
                    <i id="theme-icon" class="ph ph-moon"></i>
                </button>
            </div>
        </div>
    </nav>

<section class="hero">
    <div class="container">
        
        <h1>Travel Across East Africa<br>With <span>Confidence.</span></h1>
        <p>Skip the chaotic queues. Compare buses, book your seat visually, and pay with Mobile Money in seconds.</p>
        
        <div style="margin-top: 40px;">
            <?php if (!$is_logged_in): ?>
                <a href="login.php" class="hero-btn">Get Started <i class="ph-bold ph-arrow-right"></i></a>
                <a href="#schedule" class="hero-btn hero-btn-outline">View Schedule</a>
            <?php else: ?>
                <a href="search.php" class="hero-btn">Plan a Trip <i class="ph-bold ph-magnifying-glass"></i></a>
            <?php endif; ?>
        </div>
    </div>
</section>

    <div class="trust-bar">
        <div class="container trust-content">
            <div class="trust-item"><i class="ph-fill ph-shield-check"></i> Secure Payments</div>
            <div class="trust-item"><i class="ph-fill ph-clock"></i> On-Time Departures</div>
            <div class="trust-item"><i class="ph-fill ph-users"></i> 10,000+ Travelers</div>
            <div class="trust-item"><i class="ph-fill ph-globe"></i> Cross-Border Travel</div>
        </div>
    </div>

    <section class="section" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Volcano Digital?</h2>
                <p>We've digitized the entire experience for your comfort.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="f-icon"><i class="ph ph-armchair"></i></div>
                    <h3>Visual Seat Selection</h3>
                    <p style="color:var(--text-muted);">Don't be assigned a random seat. Look at the bus layout and pick your favorite window spot.</p>
                </div>
                <div class="feature-card">
                    <div class="f-icon"><i class="ph ph-qr-code"></i></div>
                    <h3>QR Boarding Pass</h3>
                    <p style="color:var(--text-muted);">No more paper tickets. Your ticket is saved in your account. Just scan and board.</p>
                </div>
                <div class="feature-card">
                    <div class="f-icon"><i class="ph ph-device-mobile"></i></div>
                    <h3>Mobile Money</h3>
                    <p style="color:var(--text-muted);">Seamless integration with MTN MoMo and Airtel Money for instant confirmation.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section steps-bg" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>Book in 3 Simple Steps</h2>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Choose Route</h3>
                    <p style="color:var(--text-muted);">Browse our daily schedule to Kigali, Bujumbura, and more.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Pick Your Seat</h3>
                    <p style="color:var(--text-muted);">Use our interactive map to see which seats are free.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Pay & Go</h3>
                    <p style="color:var(--text-muted);">Pay securely and receive your digital ticket instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="schedule">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Departures</h2>
                <p>Real-time availability. Book before they sell out.</p>
            </div>

            <div class="schedule-grid">
                <?php
                // Fetch trips
                $sql = "SELECT trips.id, trips.departure_time, trips.total_seats, trips.bus_plate,
                               routes.origin, routes.destination, routes.price, routes.duration,
                               companies.name AS company_name,
                               (SELECT COUNT(*) FROM bookings WHERE bookings.trip_id = trips.id) AS booked_count
                        FROM trips 
                        JOIN routes ON trips.route_id = routes.id
                        LEFT JOIN companies ON trips.company_id = companies.id
                        ORDER BY trips.departure_time ASC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $seats_left = $row['total_seats'] - $row['booked_count'];
                        $is_low = $seats_left < 5;
                        
                        echo "<div class='ticket-card'>";
                        
                        // Header
                        echo "<div class='t-header'>";
                        echo "<div class='route-text'>" . $row['origin'] . " <i class='ph-bold ph-arrow-right'></i> " . $row['destination'] . "</div>";
                        echo "<div class='price-badge'>" . number_format($row['price']) . " RWF</div>";
                        echo "</div>";

                        // Body
                        echo "<div class='t-body'>";
                        
                        // Company Tag
                        echo "<div class='company-tag'><i class='ph-fill ph-bus'></i> ". ($row['company_name'] ?? 'Volcano Express') ."</div>";

                        echo "<div class='t-row'><span>Date</span> <strong>" . date("D, M d", strtotime($row['departure_time'])) . "</strong></div>";
                        echo "<div class='t-row'><span>Time</span> <strong>" . date("h:i A", strtotime($row['departure_time'])) . "</strong></div>";
                        
                        // Seats Logic
                        echo "<div style='margin-top:15px; display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-muted);'>";
                        echo "<span>Availability</span>";
                        echo "<span style='color:" . ($is_low ? '#ef4444' : '#22c55e') . "; font-weight:700;'>$seats_left Seats Left</span>";
                        echo "</div>";
                        
                        // Progress Bar
                        $percent = ($row['booked_count'] / $row['total_seats']) * 100;
                        echo "<div style='background:var(--border); height:6px; border-radius:10px; margin-top:5px; overflow:hidden;'>
                                <div style='width:{$percent}%; background:var(--primary); height:100%;'></div>
                              </div>";
                        
                        echo "</div>"; // End Body

                        // Button Logic
                        if ($seats_left > 0) {
                            $link = $is_logged_in ? "book.php?trip_id=" . $row["id"] : "login.php";
                            $btn_text = $is_logged_in ? "Select Seat <i class='ph-bold ph-caret-right'></i>" : "Login to Book";
                            echo "<a href='$link' class='btn-book book-active'>$btn_text</a>";
                        } else {
                            echo "<div class='btn-book book-disabled'>Sold Out</div>";
                        }

                        echo "</div>"; // End Card
                    }
                } else {
                    echo "<p style='text-align:center; width:100%; grid-column:1/-1; padding:40px; background:var(--bg-card); border-radius:12px; color:var(--text-muted);'>No buses scheduled at the moment.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="f-col">
                    <a href="#" class="logo" style="margin-bottom:20px;">
                        <i class="ph-fill ph-bus"></i> <span>Volcano Digital</span>
                    </a>
                    <p>The smartest way to travel across East Africa. Secure, fast, and digital.</p>
                </div>
                <div class="f-col">
                    <h4>Company</h4>
                    <a href="#">About Us</a>
                    <a href="#">Careers</a>
                    <a href="#">Press</a>
                </div>
                <div class="f-col">
                    <h4>Support</h4>
                    <a href="#">Help Center</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Privacy Policy</a>
                </div>
                <div class="f-col">
                    <h4>Contact</h4>
                    <a href="#">support@volcano.rw</a>
                    <a href="#">+250 788 123 456</a>
                    <a href="#">Kigali, Rwanda</a>
                </div>
            </div>
            <div class="copyright">
                &copy; 2026 Volcano Express Digital. All rights reserved.
            </div>
        </div>
    </footer>

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

        // Load Saved Preference
        const saved = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', saved);
        if(saved === 'dark') document.getElementById('theme-icon').classList.replace('ph-moon', 'ph-sun');
    </script>

</body>
</html>