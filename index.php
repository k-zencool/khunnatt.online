<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/lang.php';
require_once __DIR__ . '/includes/functions.php';

$db         = getDB();
$projects   = getProjects($db, LANG);
$categories = getActiveCategories($db);

$validCategories = ['web', 'database', 'hardware', 'it_support'];
$activeFilter    = (isset($_GET['cat']) && in_array($_GET['cat'], $validCategories, true))
                    ? $_GET['cat'] : 'all';
?>
<!DOCTYPE html>
<html lang="<?= LANG === 'th' ? 'th' : 'en' ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= h(t('hero_tagline')) ?>">
  <meta name="theme-color" content="#00d4ff">
  <meta property="og:title"       content="<?= h(SITE_NAME) ?> — Portfolio">
  <meta property="og:description" content="<?= h(t('hero_tagline')) ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= h(SITE_URL) ?>">
  <title><?= h(SITE_NAME) ?> · Portfolio</title>

  <!-- Anti-flash: set theme before first paint -->
  <script>
    (function(){
      var t=localStorage.getItem('kn-theme')||
        (window.matchMedia('(prefers-color-scheme:light)').matches?'light':'dark');
      document.documentElement.setAttribute('data-theme',t);
    })();
  </script>

  <!-- Favicons -->
  <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="assets/images/favicon.svg">
  <meta name="msapplication-TileColor" content="#07102a">

  <!-- Fonts: Inter (body/UI) + Space Grotesk (display headings) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&family=JetBrains+Mono:wght@400;500&display=swap">

  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

  <!-- Custom cursor (hidden on touch) -->
  <div class="cursor-dot"  aria-hidden="true"></div>
  <div class="cursor-ring" aria-hidden="true"></div>

  <!-- Background: grid lines + 3 aqua orbs -->
  <div class="aura-bg" aria-hidden="true">
    <div class="grid-lines"></div>
    <div class="aura-orb aura-orb-1"></div>
    <div class="aura-orb aura-orb-2"></div>
    <div class="aura-orb aura-orb-3"></div>
  </div>

  <div class="page-wrapper">

    <!-- ── NAVIGATION ──────────────────────────────────────────────────── -->
    <header>
      <nav class="nav" role="navigation" aria-label="Main navigation">

        <a href="/" class="nav-logo" aria-label="<?= h(SITE_NAME) ?> home">
          <?= h(SITE_NAME) ?>
        </a>

        <ul class="nav-links" role="list">
          <li><a href="#home"    ><?= t('nav_home') ?></a></li>
          <li><a href="#projects"><?= t('nav_projects') ?></a></li>
          <li><a href="#about"   ><?= t('nav_about') ?></a></li>
          <li><a href="#contact" ><?= t('nav_contact') ?></a></li>
        </ul>

        <div class="nav-controls">
          <!-- Theme toggle -->
          <button class="theme-toggle" id="themeToggle" type="button"
                  aria-label="Toggle colour mode">
            <svg class="icon-sun" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="5"/>
              <line x1="12" y1="1"  x2="12" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22"   x2="5.64" y2="5.64"/>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1"  y1="12" x2="3"  y2="12"/>
              <line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
              <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
            </svg>
            <svg class="icon-moon" width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
          </button>

          <!-- Language switcher -->
          <div class="lang-switcher" role="group" aria-label="Language selector">
            <a href="<?= h(langUrl('th')) ?>"
               class="lang-btn <?= LANG === 'th' ? 'active' : '' ?>"
               aria-label="เปลี่ยนเป็นภาษาไทย">TH</a>
            <a href="<?= h(langUrl('en')) ?>"
               class="lang-btn <?= LANG === 'en' ? 'active' : '' ?>"
               aria-label="Switch to English">EN</a>
          </div>

          <!-- Hamburger (mobile) -->
          <button class="nav-hamburger" id="hamburger" type="button"
                  aria-label="Toggle menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
          </button>
        </div>

      </nav>

      <!-- Mobile fullscreen overlay -->
      <div class="nav-mobile" id="navMobile" aria-hidden="true">
        <nav class="nav-mobile__links">
          <a href="#home"    ><?= t('nav_home') ?></a>
          <a href="#projects"><?= t('nav_projects') ?></a>
          <a href="#about"   ><?= t('nav_about') ?></a>
          <a href="#contact" ><?= t('nav_contact') ?></a>
        </nav>
        <div class="nav-mobile__divider"></div>
        <div class="nav-mobile__social">
          <a href="https://github.com/k-zencool" target="_blank" rel="noopener noreferrer"
             class="social-link" aria-label="GitHub">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.868-.013-1.703-2.782.604-3.369-1.341-3.369-1.341-.454-1.155-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.59 9.59 0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
            </svg>
          </a>
        </div>
      </div>
    </header>

    <main>

      <!-- ── HERO ──────────────────────────────────────────────────────── -->
      <section class="hero" id="home" aria-labelledby="hero-heading">

        <!-- Left: text content -->
        <div class="hero-content">

          <span class="hero-badge">
            <span class="hero-badge-dot" aria-hidden="true"></span>
            Available for work
          </span>

          <p class="hero-eyebrow"><?= h(t('hero_greeting')) ?></p>

          <h1 class="hero-name" id="hero-heading">
            <?= h(t('hero_name')) ?>
          </h1>

          <div class="hero-role-wrap" aria-live="polite">
            <span id="roleText"></span>
            <span class="hero-cursor-blink" aria-hidden="true"></span>
          </div>

          <p class="hero-tagline"><?= h(t('hero_tagline')) ?></p>

          <div class="hero-cta-group">
            <a href="#projects" class="btn btn-primary"><?= h(t('hero_cta')) ?> &darr;</a>
            <a href="#contact"  class="btn btn-ghost"><?= h(t('hero_contact')) ?></a>
          </div>

          <div class="hero-social">
            <a href="https://github.com/k-zencool" target="_blank" rel="noopener noreferrer"
               class="social-link" aria-label="GitHub">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.868-.013-1.703-2.782.604-3.369-1.341-3.369-1.341-.454-1.155-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.59 9.59 0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
              </svg>
            </a>
            <span class="social-divider" aria-hidden="true"></span>
            <a href="mailto:zencool@gmail.com" class="hero-email">zencool@gmail.com</a>
          </div>

        </div>

        <!-- Right: code window -->
        <div class="hero-visual" aria-hidden="true">
          <div class="code-window">

            <!-- Title bar -->
            <div class="code-window__bar">
              <span class="cw-dot cw-dot--red"></span>
              <span class="cw-dot cw-dot--yellow"></span>
              <span class="cw-dot cw-dot--green"></span>
              <span class="cw-filename">khunnatt.php</span>
            </div>

            <!-- Code body with PHP syntax highlight via spans -->
            <div class="code-window__body">
<span class="cw-kw">&lt;?php</span>
<span class="cw-cmt">// khunnatt.online</span>

<span class="cw-var">$developer</span> <span class="cw-op">=</span> <span class="cw-punc">[</span>
  <span class="cw-str">'name'</span>     <span class="cw-op">=&gt;</span> <span class="cw-str">'Khun Natt'</span><span class="cw-punc">,</span>
  <span class="cw-str">'role'</span>     <span class="cw-op">=&gt;</span> <span class="cw-str">'Full-Stack Dev'</span><span class="cw-punc">,</span>
  <span class="cw-str">'location'</span> <span class="cw-op">=&gt;</span> <span class="cw-str">'Chiang Mai, TH'</span><span class="cw-punc">,</span>
  <span class="cw-str">'stack'</span>    <span class="cw-op">=&gt;</span> <span class="cw-punc">[</span>
    <span class="cw-str">'PHP 8.3'</span><span class="cw-punc">,</span>  <span class="cw-str">'MariaDB'</span><span class="cw-punc">,</span>
    <span class="cw-str">'Docker'</span><span class="cw-punc">,</span>   <span class="cw-str">'Nginx'</span><span class="cw-punc">,</span>
    <span class="cw-str">'Linux'</span><span class="cw-punc">,</span>    <span class="cw-str">'JavaScript'</span><span class="cw-punc">,</span>
  <span class="cw-punc">],</span>
  <span class="cw-str">'status'</span>   <span class="cw-op">=&gt;</span> <span class="cw-ac">'&#10022; Available'</span><span class="cw-punc">,</span>
<span class="cw-punc">];</span>
<span class="cw-cursor">|</span>
            </div>

            <!-- Status bar -->
            <div class="code-window__status">
              <span>PHP 8.3</span>
              <span>UTF-8</span>
              <span class="cw-status-dot"></span><span>Ready</span>
            </div>

          </div>
        </div>

        <!-- Scroll indicator -->
        <div class="scroll-indicator" aria-hidden="true">
          <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
          <span class="scroll-label">Scroll</span>
        </div>

      </section>

      <!-- ── STATS ──────────────────────────────────────────────────────── -->
      <section class="stats-section" aria-label="Statistics">
        <div class="stats-grid reveal">

          <div class="stat-card glass">
            <div class="stat-number-wrap">
              <span class="stat-number" data-target="3">0</span>
              <span class="stat-suffix">+</span>
            </div>
            <p class="stat-label">Years Experience</p>
          </div>

          <div class="stat-card glass">
            <div class="stat-number-wrap">
              <span class="stat-number" data-target="10">0</span>
              <span class="stat-suffix">+</span>
            </div>
            <p class="stat-label">Projects Done</p>
          </div>

          <div class="stat-card glass">
            <div class="stat-number-wrap">
              <span class="stat-number" data-target="8">0</span>
              <span class="stat-suffix">+</span>
            </div>
            <p class="stat-label">Technologies</p>
          </div>

          <div class="stat-card glass">
            <div class="stat-number-wrap">
              <span class="stat-number" data-target="100">0</span>
              <span class="stat-suffix">%</span>
            </div>
            <p class="stat-label">Committed</p>
          </div>

        </div>
      </section>

      <!-- ── PROJECTS — BENTO GRID ───────────────────────────────────────── -->
      <section class="section" id="projects" aria-labelledby="projects-heading">

        <div class="section-header reveal">
          <h2 class="section-title" id="projects-heading">
            <?= h(t('projects_title')) ?>
          </h2>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="filter-bar reveal" role="group" aria-label="Filter by category">
          <button class="filter-btn active" data-filter="all" type="button">
            <?= h(t('projects_filter_all')) ?>
          </button>
          <?php foreach ($categories as $cat): ?>
          <button class="filter-btn <?= $activeFilter === $cat ? 'active' : '' ?>"
                  data-filter="<?= h($cat) ?>" type="button">
            <?= categoryIcon($cat) ?> <?= h(categoryLabel($cat)) ?>
          </button>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($projects)): ?>
          <p class="empty-msg"><?= h(t('no_projects')) ?></p>
        <?php else: ?>
        <div class="project-grid" role="list">
          <?php foreach ($projects as $index => $project):
            $accentClass = categoryAccentClass($project['category']);
            $hasImage    = !empty($project['image_path']) && file_exists(__DIR__ . '/' . $project['image_path']);
            $isFeatured  = $hasImage && $index === 0;
            $rowNum      = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
          ?>

          <?php if ($isFeatured): ?>
          <!-- ── Featured card (has real photo) ── -->
          <article class="project-card project-card--featured"
                   data-category="<?= h($project['category']) ?>"
                   role="listitem">

            <div class="card-preview">
              <img class="card-preview__img"
                   src="<?= h($project['image_path']) ?>"
                   alt="<?= h($project['title']) ?>"
                   loading="eager" decoding="async">
              <div class="card-preview__overlay" aria-hidden="true"></div>
            </div>

            <div class="card-body">
              <div class="card-live-badge">
                <span class="card-live-dot" aria-hidden="true"></span>
                Live Project
              </div>
              <span class="card-badge <?= h($accentClass) ?>" style="margin-bottom:.75rem"><?= h(categoryLabel($project['category'])) ?></span>
              <h3 class="card-title"><?= h($project['title']) ?></h3>
              <p class="card-desc"><?= h($project['description']) ?></p>
              <div class="card-tags">
                <?php foreach (categoryTechTags($project['category']) as $tag): ?>
                <span class="card-tag"><?= h($tag) ?></span>
                <?php endforeach; ?>
              </div>
              <div class="card-links">
                <?php if (!empty($project['github_link'])): ?>
                <a href="<?= safeUrl($project['github_link']) ?>"
                   class="card-link card-link--primary"
                   target="_blank" rel="noopener noreferrer">
                  &#8599; <?= h(t('view_live')) ?>
                </a>
                <?php endif; ?>
                <a href="#" class="card-link"><?= h(t('view_details')) ?></a>
              </div>
            </div>

          </article>

          <?php else: ?>
          <!-- ── Row card (no real photo) ── -->
          <article class="project-card project-card--row"
                   data-category="<?= h($project['category']) ?>"
                   role="listitem">

            <span class="row-num"><?= $rowNum ?></span>

            <div class="row-icon">
              <?= categoryIcon($project['category']) ?>
            </div>

            <div class="row-content">
              <h3 class="row-title"><?= h($project['title']) ?></h3>
              <p class="row-desc"><?= h($project['description']) ?></p>
            </div>

            <div class="row-tags">
              <?php foreach (categoryTechTags($project['category']) as $tag): ?>
              <span class="card-tag"><?= h($tag) ?></span>
              <?php endforeach; ?>
            </div>

            <?php if (!empty($project['github_link'])): ?>
            <a href="<?= safeUrl($project['github_link']) ?>"
               class="row-link" target="_blank" rel="noopener noreferrer">
              <?= isGithubUrl($project['github_link']) ? h(t('view_github')) : h(t('view_live')) ?> &#8599;
            </a>
            <?php else: ?>
            <a href="#" class="row-link"><?= h(t('view_details')) ?> &#8594;</a>
            <?php endif; ?>

          </article>
          <?php endif; ?>

          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </section>

      <!-- ── ABOUT ─────────────────────────────────────────────────────── -->
      <section class="section" id="about" aria-labelledby="about-heading">
        <div class="about-layout">

          <!-- Left: bio + highlights -->
          <div class="about-left reveal">

            <div class="about-header">
              <div class="about-avatar" aria-hidden="true">KN</div>
              <div>
                <h2 class="about-name" id="about-heading">Khun Natt</h2>
                <p class="about-role">Full-Stack Developer &middot; Chiang Mai, TH</p>
              </div>
            </div>

            <p class="about-bio"><?= h(t('about_body')) ?></p>

            <div class="about-highlights">
              <div class="about-hl">
                <span class="about-hl__val">2+</span>
                <span class="about-hl__lbl"><?= LANG === 'th' ? 'ปีประสบการณ์' : 'Years Experience' ?></span>
              </div>
              <div class="about-hl">
                <span class="about-hl__val">7+</span>
                <span class="about-hl__lbl"><?= LANG === 'th' ? 'โปรเจกต์' : 'Projects Built' ?></span>
              </div>
              <div class="about-hl">
                <span class="about-hl__val">TH/EN</span>
                <span class="about-hl__lbl"><?= LANG === 'th' ? 'รองรับ 2 ภาษา' : 'Bilingual Dev' ?></span>
              </div>
            </div>

            <div class="about-services">

              <div class="about-svc">
                <div class="about-svc__icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </div>
                <p class="about-svc__title"><?= LANG === 'th' ? 'พัฒนาเว็บ' : 'Web Development' ?></p>
                <p class="about-svc__desc"><?= LANG === 'th' ? 'PHP · JS · CSS · REST API' : 'PHP · JS · CSS · REST API' ?></p>
              </div>

              <div class="about-svc">
                <div class="about-svc__icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <p class="about-svc__title"><?= LANG === 'th' ? 'ออกแบบฐานข้อมูล' : 'Database Design' ?></p>
                <p class="about-svc__desc"><?= LANG === 'th' ? 'MariaDB · MySQL · PDO' : 'MariaDB · MySQL · PDO' ?></p>
              </div>

              <div class="about-svc">
                <div class="about-svc__icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>
                </div>
                <p class="about-svc__title"><?= LANG === 'th' ? 'ฮาร์ดแวร์ & ระบบ' : 'Hardware & Systems' ?></p>
                <p class="about-svc__desc"><?= LANG === 'th' ? 'ประกอบ · ซ่อม · ดูแลระบบ' : 'Build · Repair · Maintain' ?></p>
              </div>

              <div class="about-svc">
                <div class="about-svc__icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <p class="about-svc__title"><?= LANG === 'th' ? 'IT Support' : 'IT Support' ?></p>
                <p class="about-svc__desc"><?= LANG === 'th' ? 'Network · AD · Windows Server' : 'Network · AD · Windows Server' ?></p>
              </div>

            </div>

          </div>

          <!-- Right: categorised tech stack -->
          <div class="about-stack reveal">

            <div class="stack-group glass">
              <h4 class="stack-label">Backend</h4>
              <div class="stack-pills">
                <?php foreach (['PHP 8.3','Node.js','Bun','Express.js','Hono','TypeScript'] as $s): ?>
                <span class="stack-pill"><?= h($s) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="stack-group glass">
              <h4 class="stack-label">Database</h4>
              <div class="stack-pills">
                <?php foreach (['MySQL','MariaDB','PostgreSQL','PDO','Drizzle ORM','Prisma'] as $s): ?>
                <span class="stack-pill"><?= h($s) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="stack-group glass">
              <h4 class="stack-label">Frontend</h4>
              <div class="stack-pills">
                <?php foreach (['JavaScript','TypeScript','HTML / CSS','Tailwind CSS','GSAP'] as $s): ?>
                <span class="stack-pill"><?= h($s) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="stack-group glass">
              <h4 class="stack-label">Infrastructure &amp; APIs</h4>
              <div class="stack-pills">
                <?php foreach (['Docker','Linux','Nginx','Git','Telegram Bot API','OpenAI API'] as $s): ?>
                <span class="stack-pill"><?= h($s) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

          </div>

        </div>
      </section>

      <!-- ── CONTACT ───────────────────────────────────────────────────── -->
      <section class="section" id="contact" aria-labelledby="contact-heading">
        <div class="reveal">
          <h2 class="section-title" id="contact-heading">
            <?= h(t('nav_contact')) ?>
          </h2>
          <p class="contact-body">
            <?= LANG === 'th'
              ? 'สนใจร่วมงานหรือมีโปรเจกต์ที่น่าสนใจ? ติดต่อมาได้เลยทุกช่องทาง'
              : 'Interested in working together or have an exciting project? Reach out anytime.' ?>
          </p>
        </div>

        <div class="contact-channels reveal">

          <!-- Email -->
          <a href="mailto:zencool@gmail.com" class="contact-ch glass">
            <div class="contact-ch__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
              </svg>
            </div>
            <p class="contact-ch__label">Email</p>
            <p class="contact-ch__value">zencool@gmail.com</p>
          </a>

          <!-- Phone -->
          <a href="tel:+66612955236" class="contact-ch glass">
            <div class="contact-ch__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 12 19.79 19.79 0 0 1 1.93 3.4 2 2 0 0 1 3.9 1.22h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 5.99 5.99l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
            </div>
            <p class="contact-ch__label"><?= LANG === 'th' ? 'โทรศัพท์' : 'Phone' ?></p>
            <p class="contact-ch__value">061-295-5236</p>
          </a>

          <!-- Facebook -->
          <a href="https://www.facebook.com/khun.natt.2025" target="_blank" rel="noopener noreferrer" class="contact-ch glass">
            <div class="contact-ch__icon contact-ch__icon--fb">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
              </svg>
            </div>
            <p class="contact-ch__label">Facebook</p>
            <p class="contact-ch__value">khun.natt.2025</p>
          </a>

          <!-- LINE -->
          <a href="https://line.me/ti/p/~24042005_natt" target="_blank" rel="noopener noreferrer" class="contact-ch glass">
            <div class="contact-ch__icon contact-ch__icon--line">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
              </svg>
            </div>
            <p class="contact-ch__label">LINE</p>
            <p class="contact-ch__value">24042005_natt</p>
          </a>

        </div>
      </section>

    </main>

    <footer class="footer">
      <p class="footer-text">
        &copy; <?= date('Y') ?> <span class="footer-accent"><?= h(SITE_NAME) ?></span>.
        <?= h(t('footer_rights')) ?>.
      </p>
      <p class="footer-text">
        <?= h(t('footer_built')) ?> pure PHP &amp; &#9829;
      </p>
    </footer>

  </div>

  <!-- GSAP 3.12.5 — SRI verified sha512 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"
          integrity="sha512-7eHRwcbYkK4d9g/6tD/mhkf++eoTHwpNM9woBxtPUBWm67zeAfFC+HrdoE2GanKeocly/VxeLvIqwvCdk7qScg=="
          crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>

  <script src="assets/js/main.js" defer></script>

</body>
</html>
