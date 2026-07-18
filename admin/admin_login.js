// ── AUTH (login page only) ─────────────────────────────────

function toggleAdminPass(btn) {
  const input = document.getElementById('login-pass');
  const hidden = input.type === 'password';
  input.type = hidden ? 'text' : 'password';
  btn.textContent = hidden ? 'HIDE' : 'SHOW';
}

async function doLogin() {
  const u = document.getElementById('login-user').value.trim();
  const p = document.getElementById('login-pass').value;
  const btn = document.getElementById('login-btn');
  document.getElementById('login-error').style.display = 'none';

  btn.disabled = true;
  btn.textContent = 'Signing in…';

  try {
    const res = await fetch('../auth/admin_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: u, password: p })
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.error || 'Login failed');

    // Success — hand off to the admin panel page.
    window.location.href = 'admin.html';
  } catch (err) {
    document.getElementById('login-error').textContent = err.message;
    document.getElementById('login-error').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Sign In →';
  }
}

// ── SESSION CHECK ───────────────────────────────────────────
// If the admin is already logged in (e.g. they hit this page directly,
// or hit Back), skip the form and go straight to the panel.
async function checkLoginSession() {
  try {
    const res = await fetch('admin_check_session.php');
    const result = await res.json();
    if (result.logged_in) {
      window.location.href = 'admin.html';
    }
  } catch (err) {
    console.error('Session check failed:', err);
  }
}

checkLoginSession();

// Re-check when restored from bfcache (e.g. navigating Back from admin.html)
window.addEventListener('pageshow', function (event) {
  if (event.persisted) checkLoginSession();
});