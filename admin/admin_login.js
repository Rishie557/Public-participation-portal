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

// ── CLEAR FIELDS ON SHOW ─────────────────────────────────────
// The browser's back/forward cache can restore previously typed values.
// Always start with a clean, empty form.
function clearLoginForm() {
  document.getElementById('login-user').value = '';
  document.getElementById('login-pass').value = '';
  document.getElementById('login-pass').type = 'password';
  const showBtn = document.querySelector('.show-password-btn');
  if (showBtn) showBtn.textContent = 'SHOW';
}

clearLoginForm();

window.addEventListener('pageshow', function () {
  clearLoginForm();
});