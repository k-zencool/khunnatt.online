<?php
// wallet.php - หน้ากระเป๋าตังค์ + ขอรหัสเชื่อมบอท
require 'includes/auth.php'; // เช็ค Login
require 'includes/db.php';   // เชื่อม Database

$user_id = $_SESSION['user_id']; // ดึง ID คนที่ล็อกอินอยู่

// -----------------------------------------------------
// 🟢 ส่วน Logic: กดปุ่มขอรหัสเชื่อม Telegram
// -----------------------------------------------------
$genToken = null;
$message = "";

if (isset($_POST['gen_code'])) {
    // สุ่มเลข 6 หลัก (100000 - 999999)
    $genToken = rand(100000, 999999); 
    
    // บันทึกลง Database
    try {
        $stmt = $pdo->prepare("UPDATE users SET link_token = :token WHERE id = :uid");
        $stmt->execute(['token' => $genToken, 'uid' => $user_id]);
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// -----------------------------------------------------
// 🟠 ส่วน Logic: ดึงข้อมูลการเงิน
// -----------------------------------------------------

// 1. เช็คสถานะการเชื่อมต่อ Telegram
$stmtUser = $pdo->prepare("SELECT telegram_chat_id, username FROM users WHERE id = :uid");
$stmtUser->execute(['uid' => $user_id]);
$userData = $stmtUser->fetch();
$isLinked = !empty($userData['telegram_chat_id']);

// 2. คำนวณยอดเงินรวม (ของ User นี้ หรือ รวมจากบอทที่ผูกแล้ว)
// ตอนนี้ดึงจาก transactions_bot โดยอิง chat_id ของ user นี้
$totalIncome = 0;
$totalExpense = 0;
$balance = 0;
$transactions = [];

if ($isLinked) {
    $chat_id = $userData['telegram_chat_id'];

    // หา income รวม
    $stmtIn = $pdo->prepare("SELECT SUM(amount) FROM transactions_bot WHERE type = 'income' AND chat_id = :cid");
    $stmtIn->execute(['cid' => $chat_id]);
    $totalIncome = $stmtIn->fetchColumn() ?: 0;

    // หา expense รวม
    $stmtEx = $pdo->prepare("SELECT SUM(amount) FROM transactions_bot WHERE type = 'expense' AND chat_id = :cid");
    $stmtEx->execute(['cid' => $chat_id]);
    $totalExpense = $stmtEx->fetchColumn() ?: 0;

    // ยอดคงเหลือ
    $balance = $totalIncome - $totalExpense;

    // ดึงรายการล่าสุด 20 รายการ
    $stmtList = $pdo->prepare("SELECT * FROM transactions_bot WHERE chat_id = :cid ORDER BY log_date DESC LIMIT 20");
    $stmtList->execute(['cid' => $chat_id]);
    $transactions = $stmtList->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .balance-card {
            background: linear-gradient(135deg, #00c853 0%, #009688 100%);
            border-radius: 20px;
            padding: 25px;
            color: white;
            box-shadow: 0 10px 20px rgba(0, 200, 83, 0.3);
            margin-bottom: 20px;
            text-align: center;
        }
        .transaction-item {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #333;
        }
        .amount-inc { color: #00e676; font-weight: bold; }
        .amount-exp { color: #ff5252; font-weight: bold; }
    </style>
</head>
<body class="d-block">

    <div class="container py-4" style="max-width: 600px;">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fa-solid fa-arrow-left"></i> กลับ</a>
            <h5 class="m-0 text-white">กระเป๋าตังค์</h5>
            <div style="width: 50px;"></div>
        </div>

        <div class="card bg-dark border-secondary mb-4">
            <div class="card-body text-center">
                
                <?php if ($genToken): ?>
                    <div class="alert alert-warning">
                        <h6 class="mb-1">รหัสยืนยันของคุณคือ:</h6>
                        <h1 class="display-4 fw-bold text-dark mb-2"><?php echo $genToken; ?></h1>
                        <small class="text-muted">ไปที่ Telegram แล้วพิมพ์:</small><br>
                        <code class="fs-5">/link <?php echo $genToken; ?></code>
                    </div>
                    <a href="wallet.php" class="btn btn-sm btn-light">ปิดหน้าต่าง</a>

                <?php elseif ($isLinked): ?>
                    <div class="text-success mb-2">
                        <i class="fa-brands fa-telegram fa-3x mb-2"></i><br>
                        <h5>เชื่อมต่อแล้ว!</h5>
                    </div>
                    <small class="text-muted">บัญชีนี้ผูกกับ Telegram เรียบร้อย</small>

                <?php else: ?>
                    <div class="text-muted mb-3">
                        <i class="fa-brands fa-telegram fa-2x"></i><br>
                        ยังไม่เชื่อมต่อกับบอท
                    </div>
                    <form method="post">
                        <button type="submit" name="gen_code" class="btn btn-primary rounded-pill w-100">
                            <i class="fa-solid fa-key me-2"></i> ขอรหัสเชื่อม Telegram
                        </button>
                    </form>
                    <small class="text-secondary d-block mt-2">
                        กดปุ่มเพื่อเอารหัสไปใส่ในบอท
                    </small>
                <?php endif; ?>

            </div>
        </div>

        <?php if ($isLinked): ?>
            <div class="balance-card">
                <div>ยอดเงินคงเหลือ</div>
                <h1 class="fw-bold my-2">฿<?php echo number_format($balance, 2); ?></h1>
                <div class="row mt-3">
                    <div class="col-6 text-start border-end border-white border-opacity-25">
                        <small><i class="fa-solid fa-arrow-down"></i> รายรับ</small><br>
                        <span class="fw-bold">+<?php echo number_format($totalIncome); ?></span>
                    </div>
                    <div class="col-6 text-end">
                        <small><i class="fa-solid fa-arrow-up"></i> รายจ่าย</small><br>
                        <span class="fw-bold">-<?php echo number_format($totalExpense); ?></span>
                    </div>
                </div>
            </div>

            <h6 class="text-secondary ps-2 mb-3">รายการล่าสุด</h6>

            <?php foreach ($transactions as $t): ?>
                <?php 
                    $isInc = ($t['type'] == 'income'); 
                    $sign = $isInc ? '+' : '-';
                    $cls = $isInc ? 'amount-inc' : 'amount-exp';
                ?>
                <div class="transaction-item">
                    <div>
                        <div class="text-white fw-bold"><?php echo htmlspecialchars($t['category']); ?></div>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <?php echo date('d/m H:i', strtotime($t['log_date'])); ?>
                        </small>
                    </div>
                    <div class="<?php echo $cls; ?>">
                        <?php echo $sign . number_format($t['amount']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($transactions) == 0): ?>
                <div class="text-center text-muted py-4">ยังไม่มีรายการจด</div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-muted py-5">
                🚫 คุณยังมองไม่เห็นรายการเงิน<br>
                กรุณาเชื่อมต่อ Telegram ด้านบนก่อนครับ
            </div>
        <?php endif; ?>

    </div>
</body>
</html>