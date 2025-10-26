// script.js — interactive touches: ripple, simple validation, and small success animation

// Ripple effect on clicks for water-like feedback
document.addEventListener('click', function (e) {
  // ignore clicks on form inputs to avoid visual noise
  const tag = e.target.tagName.toLowerCase();
  if (['input','textarea','select','button','a'].includes(tag)) return;
  const ripple = document.createElement('div');
  ripple.className = 'click-ripple';
  const size = Math.max(window.innerWidth, window.innerHeight) * 0.04;
  ripple.style.width = ripple.style.height = size + 'px';
  ripple.style.left = (e.clientX - size/2) + 'px';
  ripple.style.top = (e.clientY - size/2) + 'px';
  document.body.appendChild(ripple);
  setTimeout(()=> ripple.remove(), 900);
});

// Minimal form handling
const form = document.getElementById('registrationForm');
if (form) {
  form.addEventListener('submit', function (ev) {
    // Prevent default so we can validate first
    ev.preventDefault();

    // Use the current field names used in the HTML/PHP
    const pwdField = form.elements['dealer_password'];
    const confField = form.elements['dealer_confirm'];
    const pwd = (pwdField && pwdField.value) || '';
    const conf = (confField && confField.value) || '';

    // Basic validation consistent with server rules (min 6)
    if (pwd.length < 6) {
      alert('Password must be at least 6 characters.');
      if (pwdField) pwdField.focus();
      return;
    }
    if (pwd !== conf) {
      alert('Passwords do not match.');
      if (confField) confField.focus();
      return;
    }

    // Validation passed — submit the form to the server so registration.php can store it
    form.submit();
  });
}

function showSuccess(){
  const overlay = document.createElement('div');
  overlay.style.position = 'fixed';
  overlay.style.inset = 0;
  overlay.style.background = 'radial-gradient(circle at 20% 30%, rgba(255,255,255,0.03), rgba(0,0,0,0.6))';
  overlay.style.zIndex = 9999;
  overlay.style.display = 'flex';
  overlay.style.alignItems = 'center';
  overlay.style.justifyContent = 'center';
  overlay.style.backdropFilter = 'blur(4px)';

  const card = document.createElement('div');
  card.style.padding = '28px 36px';
  card.style.background = 'linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01))';
  card.style.borderRadius = '12px';
  card.style.textAlign = 'center';
  card.style.boxShadow = '0 10px 30px rgba(0,0,0,0.6)';

  const h = document.createElement('h2');
  h.textContent = 'Registration submitted';
  h.style.color = '#ffd700';
  h.style.margin = '0 0 8px';
  const p = document.createElement('p');
  p.textContent = 'Thank you. We will get in touch shortly.';
  p.style.color = 'rgba(255,255,255,0.85)';
  p.style.margin = '0 0 12px';

  card.appendChild(h);
  card.appendChild(p);
  overlay.appendChild(card);
  document.body.appendChild(overlay);

  // subtle ripple pulse behind card
  const pulse = document.createElement('div');
  pulse.style.position = 'absolute';
  pulse.style.width = '200px';
  pulse.style.height = '200px';
  pulse.style.borderRadius = '50%';
  pulse.style.background = 'radial-gradient(circle, rgba(255,215,0,0.12), rgba(255,215,0,0.02))';
  pulse.style.zIndex = -1;
  pulse.style.animation = 'pulse 1.6s ease-out forwards';
  card.appendChild(pulse);

  setTimeout(()=>{ overlay.remove(); form.reset(); }, 2000);
}

// pulse keyframes injection
const styleEl = document.createElement('style');
styleEl.textContent = `@keyframes pulse{0%{transform:scale(0.6);opacity:0.9}100%{transform:scale(1.8);opacity:0}} .click-ripple{position:fixed;border-radius:50%;background:radial-gradient(circle, rgba(255,255,255,0.7), rgba(255,255,255,0.08));opacity:0.6;transform:scale(0);animation:clickRip 0.9s ease-out forwards;pointer-events:none}@keyframes clickRip{to{transform:scale(8);opacity:0}}`;
document.head.appendChild(styleEl);
