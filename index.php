<?php
// index.php - หน้า Dashboard หลัก
// หน้าที่: เมนูหลักหลังจาก Login เข้ามาแล้ว

require 'includes/auth.php'; // เรียกยามมาเฝ้า

// ดึงข้อมูล session
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'NPC'; 

// กำหนดสีของยศ (Logic เล็กๆ น้อยๆ เพื่อความเท่)
$roleClass = 'badge-npc'; // ค่าเริ่มต้น
if ($role === 'GM') $roleClass = 'badge-gm';
if ($role === 'Player') $roleClass = 'badge-player';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KhunNatt System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="main-box text-center">
        
        <div class="mb-4">
            <?php if ($role === 'GM'): ?>
                <i class="fa-solid fa-user-astronaut fa-4x text-danger mb-2"></i>
            <?php else: ?>
                <i class="fa-regular fa-circle-user fa-4x text-success mb-2"></i>
            <?php endif; ?>
            
            <h2 class="fw-bold m-0"><?php echo htmlspecialchars($username); ?></h2>
            <span class="role-badge <?php echo $roleClass; ?> mt-2"><?php echo $role; ?></span>
        </div>

        <div class="divider">เมนูหลัก</div>

        <div class="d-grid gap-3">
            
            <a href="wallet.php" class="btn btn-game btn-lg py-3">
                <i class="fa-solid fa-wallet fa-lg me-2"></i> กระเป๋าตังค์ (Wallet)
            </a>

            <hr class="border-secondary m-0">

            <a href="logout.php" class="btn btn-danger">
                <i class="fa-solid fa-power-off me-2"></i> ออกจากระบบ
            </a>
        </div>

    </div>

</body>
</html>