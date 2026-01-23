<?php
// bot/setup_bot_db.php
// หน้าที่: สร้างตาราง transactions_bot ไว้เก็บเงินจาก Telegram

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🤖 กำลังสร้างสมองบอท...</h3>";

// ✅ แก้ Path: ถอยหลัง 1 ทีเพื่อไปหา includes
if (file_exists('../includes/db.php')) {
    require '../includes/db.php';
} else {
    die("❌ หาไฟล์ Database ไม่เจอ! เช็ค Path ดีๆ ว่าไฟล์นี้อยู่ในโฟลเดอร์ bot/ หรือเปล่า");
}

try {
    // สร้างตาราง: transactions_bot
    // chat_id: สำคัญมาก เอาไว้ระบุตัวตนคนใช้งาน
    $sql = "CREATE TABLE IF NOT EXISTS transactions_bot (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chat_id VARCHAR(50) NOT NULL,
        type ENUM('expense', 'income') NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        note TEXT NULL,
        log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    echo "✅ สร้างตาราง <b>transactions_bot</b> สำเร็จ!<br>";
    echo "👉 ขั้นตอนต่อไป: เอา Token ไปใส่ใน webhook.php แล้วตั้งค่า Webhook ซะ";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ พังว่ะเพื่อน:</h2>";
    echo $e->getMessage();
}
?>