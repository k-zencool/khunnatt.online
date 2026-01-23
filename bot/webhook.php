<?php
// bot/webhook.php
// สมองบอท V.Final: รองรับทั้ง Link Token (Google) และ User/Password

// 1. เชื่อมต่อระบบ
if (file_exists('../includes/db.php')) {
    require '../includes/db.php';
} else {
    exit;
}

require 'bot_config.php';
require 'TelegramBot.php';

$bot = new TelegramBot($botToken);

// 2. รับข้อมูล
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) exit;

$chat_id = $update["message"]["chat"]["id"];
$text = trim($update["message"]["text"]);
// $username = $update["message"]["from"]["username"] ?? 'Unknown';

$parts = explode(" ", $text);
$command = strtolower($parts[0]);

// ------------------------------------------------------------------
// 🔒 โซนตรวจสอบตัวตน (Authentication)
// ------------------------------------------------------------------

// ฟังก์ชันเช็คว่า Chat ID นี้ เป็นของใคร?
function getLinkedUser($pdo, $chat_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telegram_chat_id = :chat_id LIMIT 1");
    $stmt->execute(['chat_id' => $chat_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$user = getLinkedUser($pdo, $chat_id);

// ------------------------------------------------------------------
// ⛔ กรณีที่ 1: คนแปลกหน้า (ยังไม่เชื่อมต่อ)
// ------------------------------------------------------------------
if (!$user) {
    
    // 👉 วิธีที่ 1: ใช้รหัสลับ 6 หลัก (แนะนำสำหรับ Google Login)
    // พิมพ์: /link 123456
    if ($command == "/link") {
        if (count($parts) >= 2) {
            $token = $parts[1]; // เลขที่ส่งมา

            // หาว่าเลขนี้เป็นของใคร
            $stmt = $pdo->prepare("SELECT * FROM users WHERE link_token = :token LIMIT 1");
            $stmt->execute(['token' => $token]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData) {
                // เจอตัวแล้ว! ผูกไอดีทันที และลบรหัสลับทิ้ง (กันคนอื่นมาแย่ง)
                $update = $pdo->prepare("UPDATE users SET telegram_chat_id = :chat_id, link_token = NULL WHERE id = :uid");
                $update->execute(['chat_id' => $chat_id, 'uid' => $userData['id']]);

                $msg = "✅ <b>เชื่อมต่อสำเร็จ!</b>\n";
                $msg .= "สวัสดีคุณ <b>{$userData['username']}</b>\n";
                $msg .= "บัญชีของคุณผูกกับ Telegram เรียบร้อย เริ่มจดเงินได้เลย!";
                $bot->sendMessage($chat_id, $msg);
            } else {
                $bot->sendMessage($chat_id, "❌ <b>รหัสไม่ถูกต้อง!</b> หรือหมดอายุแล้ว\nไปกดขอรหัสหน้าเว็บใหม่นะ");
            }
        } else {
            $bot->sendMessage($chat_id, "⚠️ <b>วิธีเชื่อมต่อ:</b>\n1. เข้าหน้าเว็บ Wallet\n2. กดปุ่ม 'ขอรหัสเชื่อมบอท'\n3. พิมพ์: <code>/link เลขที่ได้</code>");
        }
        exit;
    }

    // 👉 วิธีที่ 2: ใช้ Username + Password (วิธีเดิม)
    // พิมพ์: /auth jack 1234
    if ($command == "/auth") {
        if (count($parts) >= 3) {
            $inputUser = $parts[1];
            $inputPass = $parts[2];
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :user LIMIT 1");
            $stmt->execute(['user' => $inputUser]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userData && password_verify($inputPass, $userData['password'])) {
                // เช็คว่าเคยผูกกับใครไปรึยัง
                if (!empty($userData['telegram_chat_id'])) {
                    $bot->sendMessage($chat_id, "⚠️ User นี้ผูกกับเครื่องอื่นไปแล้ว!");
                    exit;
                }

                $updateUser = $pdo->prepare("UPDATE users SET telegram_chat_id = :chat_id WHERE id = :uid");
                $updateUser->execute(['chat_id' => $chat_id, 'uid' => $userData['id']]);
                
                $bot->sendMessage($chat_id, "✅ <b>ยืนยันตัวตนสำเร็จ!</b> (Login Mode)\nยินดีต้อนรับคุณ {$userData['username']}");
            } else {
                $bot->sendMessage($chat_id, "❌ ชื่อหรือรหัสผ่านผิด!");
            }
        } else {
            $bot->sendMessage($chat_id, "พิมพ์: <code>/auth username password</code>");
        }
        exit;
    }

    // ถ้าไม่ใช่คำสั่งเชื่อมต่อ -> ด่ากลับ
    $bot->sendMessage($chat_id, "⛔ <b>ยังไม่เชื่อมต่อ!</b>\nไปที่หน้าเว็บ Wallet > กดปุ่มขอรหัส\nแล้วพิมพ์: <code>/link เลขที่ได้</code>");
    exit;
}

// ------------------------------------------------------------------
// ✅ กรณีที่ 2: คนกันเอง (เชื่อมต่อแล้ว)
// ------------------------------------------------------------------

// 🔴 จ่ายเงิน
if ($command == "/pay" || $command == "/จ่าย") {
    if (count($parts) >= 3) {
        $amount = $parts[1];
        $category = implode(" ", array_slice($parts, 2));

        try {
            // บันทึกโดยอิง chat_id (ซึ่งเราเชื่อมกับ user ไว้แล้ว)
            $stmt = $pdo->prepare("INSERT INTO transactions_bot (chat_id, type, amount, category) VALUES (:chat_id, 'expense', :amount, :category)");
            $stmt->execute(['chat_id' => $chat_id, 'amount' => $amount, 'category' => $category]);

            $msg = "💸 <b>จดจ่ายสำเร็จ!</b>\n";
            $msg .= "💰 <b>{$amount}</b> บาท ({$category})\n";
            $msg .= "👤 {$user['username']}";
            $bot->sendMessage($chat_id, $msg);
        } catch (PDOException $e) {
            $bot->sendMessage($chat_id, "Error: " . $e->getMessage());
        }
    } else {
        $bot->sendMessage($chat_id, "ใช้ผิด! พิมพ์: <code>/pay 50 ค่าข้าว</code>");
    }
} 
// 🟢 รับเงิน
elseif ($command == "/income" || $command == "/รับ") {
    if (count($parts) >= 3) {
        $amount = $parts[1];
        $category = implode(" ", array_slice($parts, 2));
        
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions_bot (chat_id, type, amount, category) VALUES (:chat_id, 'income', :amount, :category)");
            $stmt->execute(['chat_id' => $chat_id, 'amount' => $amount, 'category' => $category]);
            
            $msg = "🤑 <b>เงินเข้า!</b>\n";
            $msg .= "💰 <b>{$amount}</b> บาท ({$category})\n";
            $msg .= "👤 {$user['username']}";
            $bot->sendMessage($chat_id, $msg);
        } catch (PDOException $e) {
            $bot->sendMessage($chat_id, "Error: " . $e->getMessage());
        }
    } else {
        $bot->sendMessage($chat_id, "ใช้ผิด! พิมพ์: <code>/income 500 ถูกหวย</code>");
    }
}
// 👋 เช็คสถานะ
elseif ($command == "/start" || $command == "/me") {
    $msg = "👤 <b>ข้อมูลผู้ใช้:</b>\n";
    $msg .= "User: <b>{$user['username']}</b>\n";
    $msg .= "Status: Online ✅";
    $bot->sendMessage($chat_id, $msg);
}
else {
    $bot->sendMessage($chat_id, "งงครับลูกพี่? สั่ง /pay หรือ /income มาเลย");
}
?>