<?php
session_start();

// 1. เรียกไฟล์ Config (ไฟล์นี้อยู่ในโฟลเดอร์ login คู่กัน เรียกชื่อได้เลย)
require 'google_config.php';

// 2. เรียก Database (ถอยหลัง 1 ก้าว ../ ไปหา includes)
if (file_exists('../includes/db.php')) {
    require '../includes/db.php';
} else {
    die("❌ หาไฟล์ Database ไม่เจอ! เช็คโฟลเดอร์ includes ดีๆ");
}

// 3. ถ้าล็อกอินอยู่แล้ว จะเข้ามาทำไม? ถีบกลับหน้าแรกไป
if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$error = '';

// --- ส่วน Login ปกติ (Username/Password) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // ดึงข้อมูล User
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // 🔥 จุดแก้บั๊กสำคัญ: เพิ่ม !empty($user['password'])
            // เพื่อเช็คว่า User นี้มีรหัสผ่านจริงๆ (พวก ID Google จะไม่มีรหัสผ่าน ถ้าไม่เช็คตรงนี้จะ Error)
            if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                
                // ✅ ผ่าน! จดจำ Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; 
                
                header("Location: /");
                exit;
            } else {
                $error = "ชื่อหรือรหัสผิดครับพี่!";
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    } else {
        $error = "กรอกให้ครบสิวะ!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KhunNatt Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #0f0f0f; /* ดำสนิท */
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-box {
            background: #1a1a1a;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 255, 127, 0.1); /* แสงเขียวจางๆ แบบ RPG */
            width: 100%;
            max-width: 400px;
            border: 1px solid #333;
        }
        .form-control {
            background-color: #2b2b2b;
            border: 1px solid #444;
            color: white;
        }
        .form-control:focus {
            background-color: #333;
            color: white;
            border-color: #00ff7f;
            box-shadow: 0 0 0 0.25rem rgba(0, 255, 127, 0.25);
        }
        .btn-game {
            background-color: #00c853; /* เขียวเกมมิ่ง */
            color: white;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-game:hover {
            background-color: #00e676;
            color: black;
            transform: translateY(-2px);
        }
        .btn-google {
            background-color: #db4437; /* แดง Google */
            color: white;
            border: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-google:hover {
            background-color: #c53929;
            color: white;
            transform: translateY(-2px);
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #666;
            margin: 20px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #333;
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }
    </style>
</head>
<body>

    <div class="login-box text-center">
        <i class="fa-solid fa-dragon fa-3x text-success mb-3"></i>
        <h3 class="fw-bold mb-1">KHUNNATT</h3>
        <p class="text-secondary small mb-4">SERVER LOGIN</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small border-0 shadow-sm">
                <i class="fa-solid fa-circle-exclamation me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3 text-start">
                <label class="small text-muted mb-1">USERNAME</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin" required>
                </div>
            </div>
            <div class="mb-3 text-start">
                <label class="small text-muted mb-1">PASSWORD</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-key"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-game w-100 py-2 mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i> LOGIN
            </button>
        </form>

        <div class="divider small">OR</div>

        <a href="https://accounts.google.com/o/oauth2/auth?response_type=code&access_type=online&client_id=<?php echo $google_client_id; ?>&redirect_uri=<?php echo urlencode($google_redirect_url); ?>&scope=email+profile" class="btn btn-google w-100 py-2">
            <i class="fab fa-google me-2"></i> SIGN IN WITH GOOGLE
        </a>
        
        <div class="mt-4 small text-muted">
            ยังไม่มีไอดี? <a href="#" class="text-success text-decoration-none">สมัครไม่ได้เว้ย</a> (ระบบปิด)
        </div>
    </div>

</body>
</html>