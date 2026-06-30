// ── AUTH ──────────────────────────────────────────────────
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'sauti2026'; // ← change this to your own password

function doLogin() {
  const u = document.getElementById('login-user').value.trim();
  const p = document.getElementById('login-pass').value;
  if (u === ADMIN_USER && p === ADMIN_PASS) {
    sessionStorage.setItem('admin_auth', 'true');
    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('admin-panel').style.display = 'block';
    loadReports();
  } else {
    document.getElementById('login-error').style.display = 'block';
  }
}

function doLogout() {
  sessionStorage.removeItem('admin_auth');
  location.reload();
}

// auto-login if session exists
if (sessionStorage.getItem('admin_auth') === 'true') {
  document.getElementById('login-screen').style.display = 'none';
  document.getElementById('admin-panel').style.display = 'block';
}

// ── STATE ─────────────────────────────────────────────────
let allReports = [];
let filtered = [];
let currentPage = 1;
const perPage = 10;

// ── LOAD ──────────────────────────────────────────────────
async function loadReports() {
  document.getElementById('reports-tbody').innerHTML =
    '<tr><td colspan="4"><div class="loading">Loading comments...</div></td></tr>';

  try {
    const res = await fetch('get_reports.php');
    if (!res.ok) throw new Error('Request failed');
    const data = await res.json();
    allReports = data || [];
    updateKPIs();
    applyFilters();
  } catch (err) {
    showToast('Failed to load comments.', true);
    console.error(err);
  }
}

// ── KPIs ──────────────────────────────────────────────────
function updateKPIs() {
  document.getElementById('kpi-total').textContent = allReports.length;
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('kpi-today').textContent =
    allReports.filter(r => r.created_at && r.created_at.startsWith(today)).length;
}

// ── FILTERS ───────────────────────────────────────────────
function applyFilters() {
  const search = document.getElementById('search-input')?.value.toLowerCase() || '';

  filtered = allReports.filter(r => {
    if (search && !r.description?.toLowerCase().includes(search)) return false;
    return true;
  });

  currentPage = 1;
  renderTable();
}

// ── RENDER ────────────────────────────────────────────────
function renderTable() {
  const tbody = document.getElementById('reports-tbody');
  const start = (currentPage - 1) * perPage;
  const page = filtered.slice(start, start + perPage);

  if (filtered.length === 0) {
    tbody.innerHTML = `<tr><td colspan="4">
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <div class="empty-text">No comments found</div>
        <div class="empty-sub">Try changing your search</div>
      </div></td></tr>`;
    document.getElementById('pagination').style.display = 'none';
    return;
  }

  tbody.innerHTML = page.map((r, i) => {
    const num = start + i + 1;
    const date = r.created_at
      ? new Date(r.created_at).toLocaleString('en-KE', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        })
      : '—';
    const desc = r.description
      ? r.description.length > 120
        ? r.description.slice(0, 120) + '...'
        : r.description
      : '—';
    return `<tr>
      <td style="color:var(--gray);font-family:'DM Mono',monospace;font-size:12px">${num}</td>
      <td class="td-desc">${desc}</td>
      <td class="td-time">${date}</td>
      <td><button class="action-btn btn-delete" onclick="deleteReport(${r.id})">Delete</button></td>
    </tr>`;
  }).join('');

  renderPagination();
}

// ── PAGINATION ────────────────────────────────────────────
function renderPagination() {
  const total = filtered.length;
  const pages = Math.ceil(total / perPage);
  const start = (currentPage - 1) * perPage + 1;
  const end = Math.min(currentPage * perPage, total);
  const pag = document.getElementById('pagination');
  const info = document.getElementById('pagination-info');
  const btns = document.getElementById('page-btns');

  pag.style.display = 'flex';
  info.textContent = `Showing ${start}–${end} of ${total} comments`;

  let html = `<button class="page-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>← Prev</button>`;
  for (let p = 1; p <= pages; p++) {
    html += `<button class="page-btn ${p === currentPage ? 'active' : ''}" onclick="goPage(${p})">${p}</button>`;
  }
  html += `<button class="page-btn" onclick="goPage(${currentPage + 1})" ${currentPage === pages ? 'disabled' : ''}>Next →</button>`;
  btns.innerHTML = html;
}

function goPage(p) {
  currentPage = p;
  renderTable();
}

// ── DELETE ────────────────────────────────────────────────
async function deleteReport(id) {
  if (!confirm('Delete this comment permanently?')) return;

  try {
    const res = await fetch('delete_report.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Delete failed');

    showToast('Comment deleted.');
    allReports = allReports.filter(r => r.id !== id);
    updateKPIs();
    applyFilters();
  } catch (err) {
    showToast('Delete failed.', true);
    console.error(err);
  }
}

// ── EXPORT CSV ────────────────────────────────────────────
function exportCSV() {
  if (filtered.length === 0) { showToast('No data to export.', true); return; }
  const headers = ['#', 'Comment', 'Date'];
  const rows = filtered.map((r, i) => [
    i + 1,
    `"${(r.description || '').replace(/"/g, '""')}"`,
    r.created_at ? new Date(r.created_at).toLocaleString('en-KE') : ''
  ]);
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `public-comments-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  showToast('CSV exported successfully.');
}

// ── TOAST ─────────────────────────────────────────────────
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3000);
}

// load on start if already authenticated
if (sessionStorage.getItem('admin_auth') === 'true') loadReports();