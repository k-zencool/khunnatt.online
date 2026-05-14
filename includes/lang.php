<?php
declare(strict_types=1);

if (!defined('LANG')) define('LANG', 'th');

const TRANSLATIONS = [
  'nav_home'     => ['th' => 'หน้าหลัก',     'en' => 'Home'],
  'nav_projects' => ['th' => 'ผลงาน',         'en' => 'Projects'],
  'nav_about'    => ['th' => 'เกี่ยวกับฉัน', 'en' => 'About'],
  'nav_contact'  => ['th' => 'ติดต่อ',        'en' => 'Contact'],

  'hero_greeting' => ['th' => "Hello, I'm",  'en' => "Hello, I'm"],
  'hero_name'     => ['th' => 'Khun Natt',   'en' => 'Khun Natt'],
  'hero_tagline'  => [
    'th' => 'Full-Stack Developer · Database Designer · IT Support',
    'en' => 'Full-Stack Developer · Database Designer · IT Support',
  ],
  'hero_cta'     => ['th' => 'View Projects', 'en' => 'View Projects'],
  'hero_contact' => ['th' => 'Get in Touch',  'en' => 'Get in Touch'],

  'projects_title'      => ['th' => 'ผลงานของฉัน', 'en' => 'My Projects'],
  'projects_filter_all' => ['th' => 'ทั้งหมด',      'en' => 'All'],
  'view_github'         => ['th' => 'View on GitHub', 'en' => 'View on GitHub'],
  'view_live'           => ['th' => 'Visit Site',    'en' => 'Visit Site'],
  'view_details'        => ['th' => 'Details',       'en' => 'Details'],

  'cat_web'        => ['th' => 'พัฒนาเว็บ',     'en' => 'Web Dev'],
  'cat_database'   => ['th' => 'ฐานข้อมูล',     'en' => 'Database'],
  'cat_hardware'   => ['th' => 'ฮาร์ดแวร์',     'en' => 'Hardware'],
  'cat_it_support' => ['th' => 'ไอทีซัพพอร์ต', 'en' => 'IT Support'],

  'about_title' => ['th' => 'เกี่ยวกับฉัน', 'en' => 'About Me'],
  'about_body'  => [
    'th' => 'นักพัฒนาเว็บและ IT Support ที่หลงใหลในการสร้างระบบที่ใช้งานได้จริง มีประสบการณ์ด้าน PHP, MySQL, Docker และ Linux พร้อมทักษะดูแลฮาร์ดแวร์และสนับสนุนไอทีในองค์กร รักการเรียนรู้สิ่งใหม่ๆ และพัฒนาตัวเองอย่างต่อเนื่อง',
    'en' => 'A web developer and IT support specialist who loves building things that actually work. Hands-on with PHP, MySQL, Docker, and Linux — plus hardware maintenance and on-site IT support. Always learning, always building.',
  ],

  'footer_rights' => ['th' => 'สงวนลิขสิทธิ์', 'en' => 'All rights reserved'],
  'footer_built'  => ['th' => 'สร้างด้วย',      'en' => 'Built with'],
  'no_projects'   => ['th' => 'ยังไม่มีผลงาน', 'en' => 'No projects yet'],
];

function t(string $key): string
{
  return TRANSLATIONS[$key][LANG] ?? TRANSLATIONS[$key]['en'] ?? $key;
}

function categoryLabel(string $category): string
{
  $map = [
    'web'        => t('cat_web'),
    'database'   => t('cat_database'),
    'hardware'   => t('cat_hardware'),
    'it_support' => t('cat_it_support'),
  ];
  return $map[$category] ?? ucfirst($category);
}

/**
 * Returns a minimal inline SVG icon per category — no emoji, no external font.
 * All icons are 12×12, stroke-based (Feather/Lucide style).
 */
function categoryIcon(string $category): string
{
  $base = 'width="12" height="12" viewBox="0 0 24 24" fill="none" '
        . 'stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"';

  $icons = [
    // </> code brackets — web
    'web' => '<svg '.$base.'>'
           . '<polyline points="16 18 22 12 16 6"/>'
           . '<polyline points="8 6 2 12 8 18"/>'
           . '</svg>',

    // cylinder — database
    'database' => '<svg '.$base.'>'
                . '<ellipse cx="12" cy="5" rx="9" ry="3"/>'
                . '<path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>'
                . '<path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'
                . '</svg>',

    // CPU chip — hardware
    'hardware' => '<svg '.$base.'>'
                . '<rect x="4" y="4" width="16" height="16" rx="2"/>'
                . '<rect x="9" y="9" width="6" height="6"/>'
                . '<line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/>'
                . '<line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/>'
                . '<line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/>'
                . '<line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>'
                . '</svg>',

    // shield — IT support
    'it_support' => '<svg '.$base.'>'
                  . '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'
                  . '</svg>',
  ];

  return $icons[$category] ?? '';
}

function langUrl(string $targetLang): string
{
  $params = $_GET;
  $params['lang'] = $targetLang;
  return '?' . http_build_query($params);
}
