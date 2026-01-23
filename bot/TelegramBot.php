<?php
// bot/TelegramBot.php
// เครื่องมือช่วยคุยกับ Telegram (จะได้ไม่ต้องเขียน CURL รกๆ ในหน้าหลัก)

class TelegramBot {
    private $token;

    public function __construct($token) {
        $this->token = $token;
    }

    // ฟังก์ชันส่งข้อความ (รองรับ HTML ตัวหนา/เอียง)
    public function sendMessage($chat_id, $text) {
        $url = "https://api.telegram.org/bot" . $this->token . "/sendMessage";
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML' 
        ];
        return $this->request($url, $data);
    }

    // ฟังก์ชันยิง Request (Private ใช้กันเองภายใน)
    private function request($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // เพิ่ม Timeout กันค้าง
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
?>