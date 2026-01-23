<?php
// login/google_config.php

// 1. อันแรกที่มึงส่งมา (Client ID) กูใส่ให้ละ
$google_client_id     = 'xxxxxxxxxxxxx';

// 2. อันนี้มึงต้องไปก๊อปมาใส่เอง! (Client Secret)
$google_client_secret = 'xxxxxxxxxxxxx';  // <--- ห้ามลืม! ขึ้นต้นด้วย GOCSPX-

// 3. ลิงก์ขากลับ (ต้องเหมือนใน Console เป๊ะ)
$google_redirect_url  = 'http://localhost/login/google_callback.php';
?>