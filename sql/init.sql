-- =============================================================================
-- sql/init.sql
-- Database initialisation script for khunnatt.online
-- Runs automatically on first Docker boot via /docker-entrypoint-initdb.d/
-- Also safe to run manually on DirectAdmin shared hosting via phpMyAdmin.
-- =============================================================================

-- Ensure correct charset from the start
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ── Database ──────────────────────────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `khunnatt_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `khunnatt_db`;

-- ── portfolio_projects ────────────────────────────────────────────────────────
-- Stores all portfolio items shown on the homepage.
-- title_th/en and description_th/en allow bilingual display without a separate
-- translations table (simple & fast for a personal portfolio scale).
CREATE TABLE IF NOT EXISTS `portfolio_projects` (
    `id`              INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `title_th`        VARCHAR(255)        NOT NULL COMMENT 'Project title in Thai',
    `title_en`        VARCHAR(255)        NOT NULL COMMENT 'Project title in English',
    `category`        ENUM(
                          'web',
                          'database',
                          'hardware',
                          'it_support'
                      )                   NOT NULL COMMENT 'Project category for filtering',
    `description_th`  TEXT                NOT NULL COMMENT 'Full description in Thai',
    `description_en`  TEXT                NOT NULL COMMENT 'Full description in English',
    `image_path`      VARCHAR(500)            NULL DEFAULT NULL COMMENT 'Relative path from project root, e.g. assets/images/foo.jpg',
    `github_link`     VARCHAR(500)            NULL DEFAULT NULL COMMENT 'Full GitHub repository URL',
    `sort_order`      TINYINT UNSIGNED    NOT NULL DEFAULT 99 COMMENT 'Manual display order (lower = earlier)',
    `is_featured`     TINYINT(1)          NOT NULL DEFAULT 0  COMMENT '1 = render in large bento cell',
    `created_at`      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    INDEX `idx_category`   (`category`),
    INDEX `idx_sort_order` (`sort_order`, `id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Portfolio projects shown on the homepage';

-- ── Seed data ─────────────────────────────────────────────────────────────────
-- Replace placeholder values with your real project info.
INSERT INTO `portfolio_projects`
    (`title_th`, `title_en`, `category`,
     `description_th`, `description_en`,
     `image_path`, `github_link`, `sort_order`, `is_featured`)
VALUES
    -- 1. FEATURED — CMNS FixMac (real live project)
    (
        'CMNS FixMac — แพลตฟอร์มร้านซ่อม Apple',
        'CMNS FixMac — Apple Repair Platform',
        'web',
        'เว็บไซต์เต็มรูปแบบสำหรับร้านซ่อมอุปกรณ์ Apple ในเชียงใหม่ พัฒนาด้วย PHP + MySQL มีระบบ CMS แอดมิน 10 โมดูล ตลาดอุปกรณ์มือสอง ระบบรับประกัน N8N Automation รองรับ 2 ภาษา และ SEO Schema Markup เว็บไซต์ใช้งานจริงที่ cmnsfixmac.com',
        'Live full-stack service website for an Apple repair shop in Chiang Mai. PHP + MySQL + vanilla JS. Admin CMS with 10+ modules, used-device e-commerce marketplace, warranty tracking system, N8N workflow automation, bilingual TH/EN, and structured data SEO. Currently live at cmnsfixmac.com.',
        'assets/images/project-cmnsfixmac.webp',
        'https://cmnsfixmac.com',
        1,
        1   -- is_featured = true → rendered in the largest bento cell
    ),
    -- 2. Aqua — auth system + AI Telegram bot (same project)
    (
        'Aqua — ระบบ Auth & AI Telegram Bot',
        'Aqua — Auth System & AI Telegram Bot',
        'web',
        'แพลตฟอร์ม SaaS Monorepo ที่รวมระบบยืนยันตัวตน (Bun + Hono + Drizzle ORM + PostgreSQL) ดีไซน์ Glassmorphism, OTP ผ่าน Gmail API, bcryptjs และ Telegram Bot AI ที่ผู้ใช้นำ OpenRouter API Key มาใช้เองได้ รองรับ Function Calling พร้อม Dashboard จัดการผู้ใช้',
        'A SaaS monorepo combining an enterprise-grade authentication system (Bun, Hono, Drizzle ORM, PostgreSQL, Glassmorphism UI, OTP via Gmail API) with an AI Telegram bot where users bring their own OpenRouter API key — supporting function calling and a web user-management dashboard.',
        NULL,
        NULL,
        2,
        0
    ),
    -- 3. NextHand — second-hand marketplace
    (
        'NextHand — ตลาดซื้อขายสินค้ามือสอง',
        'NextHand — Second-Hand Marketplace',
        'web',
        'แพลตฟอร์มซื้อขายสินค้ามือสองที่พัฒนาด้วย PHP + MySQL รองรับการลงขาย ค้นหา กรองตามหมวดหมู่ ระบบถูกใจ (Wishlist) และการยืนยันตัวตนผู้ขาย',
        'A second-hand marketplace built with PHP and MySQL. Supports product listings, full-text search, category filtering, a wishlist system, and seller verification.',
        NULL,
        NULL,
        3,
        0
    ),
    -- 4. Comtech — school department website
    (
        'Comtech — เว็บแผนกคอมพิวเตอร์',
        'Comtech — Computer Dept. Website',
        'web',
        'เว็บไซต์ประชาสัมพันธ์แผนกวิชาเทคโนโลยีคอมพิวเตอร์ พัฒนาด้วย PHP + MySQL ประกอบด้วยระบบข่าวสาร โครงสร้างหลักสูตร รางวัล ผลงานนักศึกษา และ Admin Panel ครบชุด',
        'Promotional website for a computer technology department, built with PHP and MySQL. Includes a news system, curriculum structure, awards, student projects, and a full admin panel.',
        NULL,
        NULL,
        4,
        0
    ),
    -- 5. Novis — Minecraft server on Docker
    (
        'Novis — Minecraft Server Infrastructure',
        'Novis — Minecraft Server Infrastructure',
        'hardware',
        'โครงสร้างพื้นฐานเซิร์ฟเวอร์ Minecraft บน Docker (Paper 1.21) ประกอบด้วย MySQL 8.0 สำหรับ LuckPerms/AuthMe, phpMyAdmin และ PHP Web Interface รองรับ cross-platform (macOS, Linux, Windows)',
        'Docker-based Minecraft server infrastructure running Paper 1.21, MySQL 8.0 for LuckPerms and AuthMe plugins, phpMyAdmin, and a PHP web interface for events and top-up — cross-platform across macOS, Linux, and Windows.',
        NULL,
        NULL,
        5,
        0
    ),
    -- 6. Personal portfolio website (this site)
    (
        'เว็บพอร์ตโฟลิโอส่วนตัว — khunnatt.online',
        'Personal Portfolio — khunnatt.online',
        'web',
        'เว็บไซต์พอร์ตโฟลิโอส่วนตัวที่พัฒนาด้วย PHP บริสุทธิ์และ MariaDB พร้อมดีไซน์ Glassmorphism สไตล์ iOS 26 "Liquid Aura" ระบบ Bento-grid อสมมาตร GSAP animation รองรับสองภาษา (ไทย/อังกฤษ) และ Docker สำหรับ local development',
        'Personal portfolio built with pure PHP & MariaDB. iOS 26 "Liquid Aura" Glassmorphism design, asymmetric Bento-grid layout, GSAP animations, bilingual TH/EN support, and a full Docker development environment.',
        NULL,
        'https://github.com/k-zencool/khunnatt.online',
        6,
        0
    );
