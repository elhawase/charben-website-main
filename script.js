// === THEME ===
const toggle = document.getElementById('themeToggle');
const html = document.documentElement;
toggle.addEventListener('click', () => {
  const current = html.getAttribute('data-theme');
  html.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
});

// === NAVBAR SCROLL ===
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// === ACTIVE NAV LINK ===
const sections = document.querySelectorAll('.section, .hero');
const navLinks = document.querySelectorAll('.nav-link');
window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(s => {
    if (window.scrollY >= s.offsetTop - 200) current = s.getAttribute('id');
  });
  navLinks.forEach(l => {
    l.classList.toggle('active', l.getAttribute('href') === '#' + current);
  });
});

// === MOBILE NAV ===
const burger = document.getElementById('navBurger');
const navLinksContainer = document.getElementById('navLinks');
burger.addEventListener('click', () => {
  burger.classList.toggle('open');
  navLinksContainer.classList.toggle('open');
});
navLinksContainer.querySelectorAll('a').forEach(a => {
  a.addEventListener('click', () => {
    burger.classList.remove('open');
    navLinksContainer.classList.remove('open');
  });
});

// === SCROLL REVEAL ===
const reveals = document.querySelectorAll('[data-reveal]');
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const delay = e.target.getAttribute('data-delay') || 0;
      setTimeout(() => e.target.classList.add('visible'), Number(delay));
      revealObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.1 });
reveals.forEach(el => revealObserver.observe(el));

// === COUNT UP ===
const counters = document.querySelectorAll('[data-count]');
const countObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const target = parseInt(e.target.getAttribute('data-count'));
      let current = 0;
      const step = Math.max(1, Math.floor(target / 40));
      const timer = setInterval(() => {
        current += step;
        if (current >= target) { current = target; clearInterval(timer); }
        e.target.textContent = current;
      }, 30);
      countObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.5 });
counters.forEach(el => countObserver.observe(el));

// === SCREEN CAROUSEL ===
const cards = document.querySelectorAll('.screen-card');
const dots = document.querySelectorAll('.dot');
let idx = 0;
function showScreen(i) {
  cards.forEach(c => c.classList.remove('active'));
  dots.forEach(d => d.classList.remove('active'));
  cards[i].classList.add('active');
  dots[i].classList.add('active');
  idx = i;
}
dots.forEach(d => d.addEventListener('click', () => showScreen(Number(d.getAttribute('data-idx')))));
setInterval(() => showScreen((idx + 1) % cards.length), 4000);

// === SMOOTH SCROLL ===
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    e.preventDefault();
    const target = document.querySelector(a.getAttribute('href'));
    if (target) window.scrollTo({ top: target.offsetTop - 64, behavior: 'smooth' });
  });
});

// === FORM SUBMIT ===
document.querySelector('.contact-form')?.addEventListener('submit', (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('button');
  btn.textContent = '✓ Message envoyé !';
  btn.style.background = 'var(--green)';
  setTimeout(() => { btn.textContent = 'Envoyer le message'; btn.style.background = ''; }, 3000);
  e.target.reset();
});
