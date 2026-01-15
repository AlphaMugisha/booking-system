<?php
session_start();
include 'db.php';

// HANDLE SIGN UP
if (isset($_POST['signup'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $pass = $_POST['password']; 
    
    $check = $conn->query("SELECT * FROM users WHERE phone='$phone'");
    if ($check->num_rows > 0) {
        echo "<script>alert('Phone number already registered!');</script>";
    } else {
        $conn->query("INSERT INTO users (name, phone, password) VALUES ('$name', '$phone', '$pass')");
        $_SESSION['user_phone'] = $phone; 
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $name;
        header("Location: search.php");
    }
}

// HANDLE LOGIN
if (isset($_POST['login'])) {
    $phone = $_POST['phone'];
    $pass = $_POST['password'];
    
    $result = $conn->query("SELECT * FROM users WHERE phone='$phone' AND password='$pass'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        header("Location: search.php"); 
    } else {
        $error = "Incorrect phone number or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Volcano Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-body: #020617;
                --bg-card: #1e293b;
                --text-main: #f8fafc;
                --text-muted: #94a3b8;
                --border: #334155;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; transition: 0.3s; }
        
        body {
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            display: flex;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow: hidden;
        }

        /* --- LEFT SIDE: THE VISUAL --- */
        .brand-panel {
            flex: 1.2;
            position: relative;
            background-image: linear-gradient(rgba(2, 6, 23, 0.6), rgba(2, 6, 23, 0.8)), url('nice4.png');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            color: white;
        }

        .brand-panel h1 { font-size: 3.5rem; line-height: 1.1; margin-bottom: 20px; font-weight: 700; }
        .brand-panel p { font-size: 1.2rem; opacity: 0.8; max-width: 450px; }

        /* --- RIGHT SIDE: THE FORM --- */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--bg-body);
        }

        .form-container { width: 100%; max-width: 400px; }

        .header-text { margin-bottom: 35px; }
        .header-text h2 { font-size: 2rem; margin-bottom: 8px; }
        .header-text p { color: var(--text-muted); }

        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: var(--text-muted);
            font-size: 1.2rem;
        }

        input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 25px -5px rgba(37, 99, 235, 0.4); }

        .toggle-text { margin-top: 25px; text-align: center; color: var(--text-muted); font-size: 0.95rem; }
        .toggle-link { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .hidden { display: none; }

        @media (max-width: 900px) {
            .brand-panel { display: none; }
            body { background-image: none; }
        }
    </style>
</head>
<body>

    <div class="brand-panel">
        <div style="position: absolute; top: 40px; left: 60px; font-weight: 800; font-size: 1.5rem;">
            <i class="ph-fill ph-bus"></i> Volcano Digital
        </div>
        <h1>Your journey<br>starts here.</h1>
        <p>Sign in to access visual seat selection, manage your bookings, and travel across East Africa with ease.</p>
    </div>

    <div class="form-panel">
        <div class="form-container">

            <?php if(isset($error)): ?>
                <div class="error-msg">
                    <i class="ph-bold ph-warning-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div id="login-box">
                <div class="header-text">
                    <h2>Welcome back</h2>
                    <p>Enter your phone number to continue</p>
                </div>
                <form method="post">
                    <div class="input-group">
                        <label>Phone Number</label>
                        <div class="input-wrapper">
                            <i class="ph ph-phone"></i>
                            <input type="text" name="phone" placeholder="078..." required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="ph ph-lock-key"></i>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn-submit">Sign In</button>
                </form>
                <p class="toggle-text">
                    New here? <span class="toggle-link" onclick="toggleForm()">Create an account</span>
                </p>
                <div style="text-align:center; margin-top:30px;">
                    <a href="index.php" style="color:var(--text-muted); text-decoration:none; font-size:0.9rem;">
                        <i class="ph ph-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>

            <div id="signup-box" class="hidden">
                <div class="header-text">
                    <h2>Create account</h2>
                    <p>Join thousands of happy travelers</p>
                </div>
                <form method="post">
                    <div class="input-group">
                        <label>Full Name</label>
                        <div class="input-wrapper">
                            <i class="ph ph-user"></i>
                            <input type="text" name="name" placeholder="John Doe" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Phone Number</label>
                        <div class="input-wrapper">
                            <i class="ph ph-phone"></i>
                            <input type="text" name="phone" placeholder="078..." required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="ph ph-lock-key"></i>
                            <input type="password" name="password" placeholder="Min. 8 characters" required>
                        </div>
                    </div>
                    <button type="submit" name="signup" class="btn-submit">Create Account</button>
                </form>
                <p class="toggle-text">
                    Already have an account? <span class="toggle-link" onclick="toggleForm()">Sign in</span>
                </p>
            </div>

        </div>
    </div>

    <script>
        function toggleForm() {
            const login = document.getElementById('login-box');
            const signup = document.getElementById('signup-box');
            login.classList.toggle('hidden');
            signup.classList.toggle('hidden');
        }
    </script>

</body>
</html>