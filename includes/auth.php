<?php
// includes/auth.php

// เช็คว่าเปิด Session หรือยัง ถ้ายังก็เปิดซะ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เช็คว่าล็อกอินยัง?
if (!isset($_SESSION['user_id'])) {
    // ถ้ายัง -> ถีบไปหน้า Login
    header("Location: /login/"); 
    exit;
}
?>