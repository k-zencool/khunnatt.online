'use strict';

const hasGsap = typeof gsap !== 'undefined';

/* ── 1. Theme toggle — runs FIRST, zero dependencies ──────────────────── */
function initThemeToggle() {
  const btn  = document.getElementById('themeToggle');
  const html = document.documentElement;
  if (!btn) return;
  btn.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('kn-theme', next);
  });
}

/* ── 2. Custom cursor — CSS-transition ring, no lerp loop ─────────────── */
function initCursor() {
  if (window.matchMedia('(pointer:coarse)').matches) return;
  const dot  = document.querySelector('.cursor-dot');
  const ring = document.querySelector('.cursor-ring');
  if (!dot || !ring) return;

  document.addEventListener('mousemove', (e) => {
    const x = `calc(${e.clientX}px - 50%)`;
    const y = `calc(${e.clientY}px - 50%)`;
    dot.style.transform  = `translate(${x},${y})`;
    ring.style.transform = `translate(${x},${y})`;
    if (!dot.classList.contains('on')) { dot.classList.add('on'); ring.classList.add('on'); }
  });

  const sel = 'a,button,.project-card,.skill-pill,.filter-btn,.lang-btn,.social-link';
  document.querySelectorAll(sel).forEach((el) => {
    el.addEventListener('mouseenter', () => { dot.classList.add('hov'); ring.classList.add('hov'); });
    el.addEventListener('mouseleave', () => { dot.classList.remove('hov'); ring.classList.remove('hov'); });
  });
  document.addEventListener('mouseleave', () => { dot.classList.remove('on'); ring.classList.remove('on'); });
  document.addEventListener('mouseenter', () => { dot.classList.add('on'); ring.classList.add('on'); });
}

/* ── 3. Hero entrance ─────────────────────────────────────────────────── */
function animateHero() {
  const steps = [
    ['.hero-badge',     0.15],
    ['.hero-eyebrow',   0.42],
    ['.hero-name',      0.62],
    ['.hero-role-wrap', 0.82],
    ['.hero-tagline',   1.0 ],
    ['.hero-cta-group', 1.18],
    ['.hero-social',    1.35],
  ];

  if (hasGsap) {
    const tl = gsap.timeline();
    steps.forEach(([sel, t]) => {
      const el = document.querySelector(sel);
      if (el) tl.to(el, { opacity:1, y:0, duration:.7, ease:'power3.out' }, t);
    });
  } else {
    steps.forEach(([sel, t]) => {
      const el = document.querySelector(sel);
      if (!el) return;
      setTimeout(() => {
        el.style.transition = 'opacity .7s ease, transform .7s ease';
        el.style.opacity = '1';
        el.style.transform = 'translateY(0)';
      }, t * 1000);
    });
  }
}

/* ── 4. Role typing animation ─────────────────────────────────────────── */
function initTyping() {
  const el = document.getElementById('roleText');
  if (!el) return;
  const roles = ['Full-Stack Developer','Database Designer','IT Specialist','Problem Solver'];
  let ri = 0, ci = 0, del = false;

  function tick() {
    const role = roles[ri];
    if (!del) {
      el.textContent = role.slice(0, ++ci);
      if (ci === role.length) { del = true; setTimeout(tick, 1800); return; }
    } else {
      el.textContent = role.slice(0, --ci);
      if (ci === 0) { del = false; ri = (ri + 1) % roles.length; }
    }
    setTimeout(tick, del ? 50 : 85);
  }
  setTimeout(tick, 1600);
}

/* ── 5. Scroll reveals ────────────────────────────────────────────────── */
function initScrollReveals() {
  const els = document.querySelectorAll('.reveal');
  if (!els.length) return;
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
  }, { threshold:0.1, rootMargin:'0px 0px -48px 0px' });
  els.forEach((el) => io.observe(el));
}

/* ── 6. Bento card stagger entrance ───────────────────────────────────── */
function initCardReveals() {
  const grid = document.querySelector('.project-grid');
  if (!grid) return;
  const cards = Array.from(grid.querySelectorAll('.project-card'));
  cards.forEach((c, i) => {
    c.style.cssText += `opacity:0;transform:translateY(36px);` +
      `transition:opacity .55s ${i*.07}s cubic-bezier(0,0,.2,1),` +
      `transform .55s ${i*.07}s cubic-bezier(0,0,.2,1);`;
  });
  new IntersectionObserver((entries) => {
    if (!entries[0].isIntersecting) return;
    cards.forEach((c) => { c.style.opacity='1'; c.style.transform='translateY(0)'; });
    // remove stagger transition after entrance so hover transform works cleanly
    setTimeout(() => cards.forEach((c) => { c.style.transition='border-color .3s,box-shadow .3s,transform .3s'; }), 1200);
  }, { threshold:0.05 }).observe(grid);
}

/* ── 7. Stats counter ─────────────────────────────────────────────────── */
function initCounters() {
  const counters = document.querySelectorAll('.stat-number[data-target]');
  if (!counters.length) return;
  // Observe each counter individually so e.target IS the counter element
  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      io.unobserve(e.target);
      const el  = e.target;
      const end = parseInt(el.dataset.target, 10);
      const t0  = performance.now();
      (function frame(now) {
        const p = Math.min((now - t0) / 1500, 1);
        el.textContent = Math.round((1 - Math.pow(2, -10 * p)) * end);
        if (p < 1) requestAnimationFrame(frame);
      })(performance.now());
    });
  }, { threshold: 0.6 });
  counters.forEach((el) => io.observe(el));
}

/* ── 8. 3D card tilt ──────────────────────────────────────────────────── */
function initCardTilt() {
  if (!hasGsap || window.matchMedia('(pointer:coarse)').matches) return;
  document.querySelectorAll('.project-card').forEach((card) => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width  - .5;
      const y = (e.clientY - r.top)  / r.height - .5;
      gsap.to(card, { rotateY:x*6, rotateX:-y*6, transformPerspective:900, duration:.4, ease:'power2.out', overwrite:'auto' });
    });
    card.addEventListener('mouseleave', () => {
      gsap.to(card, { rotateX:0, rotateY:0, duration:.65, ease:'elastic.out(1,.5)', overwrite:'auto' });
    });
  });
}

/* ── 9. Aura parallax ─────────────────────────────────────────────────── */
function initAuraParallax() {
  if (!hasGsap) return;
  const orbs = document.querySelectorAll('.aura-orb');
  if (!orbs.length) return;
  let raf = null, mx = 0, my = 0;
  document.addEventListener('mousemove', (e) => {
    mx = e.clientX - window.innerWidth / 2;
    my = e.clientY - window.innerHeight / 2;
    if (raf) return;
    raf = requestAnimationFrame(() => {
      gsap.to(orbs[0], { x:mx*.028, y:my*.028, duration:2.5, ease:'power1.out', overwrite:'auto' });
      if (orbs[1]) gsap.to(orbs[1], { x:mx*-.02, y:my*-.02, duration:3, ease:'power1.out', overwrite:'auto' });
      raf = null;
    });
  });
}

/* ── 10. Nav scroll + active link ─────────────────────────────────────── */
function initNavScroll() {
  const nav  = document.querySelector('.nav');
  const hero = document.querySelector('.hero');
  if (!nav) return;
  if (hero) {
    new IntersectionObserver(([e]) => nav.classList.toggle('scrolled', !e.isIntersecting),
      { rootMargin:'-80px 0px 0px 0px', threshold:0 }).observe(hero);
  }
  document.querySelectorAll('section[id]').forEach((sec) => {
    new IntersectionObserver((entries) => {
      const link = document.querySelector(`.nav-links a[href="#${sec.id}"]`);
      if (link) link.classList.toggle('active', entries[0].isIntersecting);
    }, { rootMargin:'-25% 0px -25% 0px', threshold:0 }).observe(sec);
  });
}

/* ── 11. Category filter ──────────────────────────────────────────────── */
function initCategoryFilter() {
  const btns  = document.querySelectorAll('.filter-btn');
  const cards = document.querySelectorAll('.project-card');
  if (!btns.length || !cards.length) return;
  btns.forEach((btn) => {
    btn.addEventListener('click', () => {
      btns.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      const target = btn.dataset.filter;
      cards.forEach((card) => {
        const show = target === 'all' || card.dataset.category === target;
        if (show) {
          card.style.display = '';
          if (hasGsap) gsap.to(card, { opacity:1, scale:1, duration:.3, ease:'power2.out', overwrite:'auto' });
          else { card.style.opacity='1'; card.style.transform='scale(1)'; }
        } else {
          if (hasGsap) gsap.to(card, { opacity:0, scale:.97, duration:.22, ease:'power2.in', overwrite:'auto',
            onComplete:() => { card.style.display='none'; } });
          else { card.style.opacity='0'; setTimeout(() => { card.style.display='none'; }, 220); }
        }
      });
    });
  });
}

/* ── 12. Mobile nav ───────────────────────────────────────────────────── */
function initMobileNav() {
  const ham     = document.querySelector('.nav-hamburger');
  const overlay = document.querySelector('.nav-mobile');
  if (!ham || !overlay) return;
  const toggle = (open) => {
    ham.classList.toggle('open', open);
    overlay.classList.toggle('open', open);
    ham.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
  };
  ham.addEventListener('click', () => toggle(!ham.classList.contains('open')));
  overlay.querySelectorAll('.nav-mobile__links a').forEach((a) => a.addEventListener('click', () => toggle(false)));
  overlay.addEventListener('click', (e) => { if (e.target === overlay) toggle(false); });
}

/* ── Bootstrap ────────────────────────────────────────────────────────── */
function init() {
  initThemeToggle();    // must be first
  initCursor();
  animateHero();
  initTyping();
  initScrollReveals();
  initCardReveals();
  initCounters();
  initCardTilt();
  initAuraParallax();
  initNavScroll();
  initCategoryFilter();
  initMobileNav();
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', init)
  : init();
