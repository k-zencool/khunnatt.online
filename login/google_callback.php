<?php
// login/google_callback.php
session_start();
require '../includes/db.php';
require 'google_config.php';

if (isset($_GET['code'])) {
    // 1. เอา Code ไปแลก Token
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code' => $_GET['code'],
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri' => $google_redirect_url,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        // 2. ได้กุญแจแล้ว ไขเข้าไปดูข้อมูล User
        $info_url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $response['access_token'];
        $user_info = json_decode(file_get_contents($info_url), true);

        $google_id = $user_info['id'];
        $email = $user_info['email'];
        $name = $user_info['name'];

        // 3. เช็คว่าเคยสมัครยัง?
        $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
        $stmt->execute([$google_id, $email]);
        $user = $stmt->fetch();

        if ($user) {
            // ✅ เก่า: ล็อกอินเลย
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        } else {
            // 🆕 ใหม่: สมัครให้เลย + ยัดยศ NPC (กากสุด)
            $default_role = 'NPC'; // <--- ตรงนี้แหละที่กำหนดความกาก!

            $sql = "INSERT INTO users (username, email, google_id, role) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $google_id, $default_role]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $name;
            $_SESSION['role'] = $default_role;
        }

        header('Location: /'); // เข้าบ้านได้!
        exit;
    }
}
echo "Login Google พังว่ะเพื่อน เช็ค Config หน่อยดิ๊";
?>