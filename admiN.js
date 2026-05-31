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

// ── SUPABASE ──────────────────────────────────────────────
const { createClient } = supabase;
const db = createClient(
  'https://pktxbzbgaeqkyflvkbvj.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBrdHhiemJnYWVxa3lmbHZrYnZqIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODAxOTk2MzEsImV4cCI6MjA5NTc3NTYzMX0.mlLWsiyD7J9UVJYn2UgIqT1onTwlOmM03tJANN4nv30'
);

// ── STATE ─────────────────────────────────────────────────
let allReports = [];
let filtered = [];
let currentPage = 1;
const perPage = 10;

// ── LOAD ──────────────────────────────────────────────────
async function loadReports() {
  document.getElementById('reports-tbody').innerHTML =
    '<tr><td colspan="7"><div class="loading">Loading reports...</div></td></tr>';

  const { data, error } = await db
    .from('reports')
    .select('*')
    .order('created_at', { ascending: false });

  if (error) {
    showToast('Failed to load reports.', true);
    console.error(error);
    return;
  }

  allReports = data || [];
  updateKPIs();
  applyFilters();
}

// ── KPIs ──────────────────────────────────────────────────
function updateKPIs() {
  document.getElementById('kpi-total').textContent = allReports.length;
  document.getElementById('kpi-critical').textContent =
    allReports.filter(r => r.severity === 'Critical' || r.severity === 'High').length;
  document.getElementById('kpi-anon').textContent =
    allReports.filter(r => r.anonymous === true).length;
  const today = new Date().toISOString().slice(0, 10);
  document.getElementById('kpi-today').textContent =
    allReports.filter(r => r.created_at && r.created_at.startsWith(today)).length;
}

// ── FILTERS ───────────────────────────────────────────────
function applyFilters() {
  const sev = document.getElementById('filter-severity').value;
  const anon = document.getElementById('filter-anon').value;
  const search = document.getElementById('search-input').value.toLowerCase();

  filtered = allReports.filter(r => {
    if (sev && r.severity !== sev) return false;
    if (anon !== '' && String(r.anonymous) !== anon) return false;
    if (search && !r.ministry?.toLowerCase().includes(search) &&
        !r.description?.toLowerCase().includes(search)) return false;
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
    tbody.innerHTML = `<tr><td colspan="7">
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <div class="empty-text">No reports found</div>
        <div class="empty-sub">Try changing your filters</div>
      </div></td></tr>`;
    document.getElementById('pagination').style.display = 'none';
    return;
  }

  tbody.innerHTML = page.map((r, i) => {
    const num = start + i + 1;
    const sev = r.severity || 'Low';
    const sevClass = `badge-${sev.toLowerCase().split(' ')[0]}`;
    const date = r.created_at
      ? new Date(r.created_at).toLocaleString('en-KE', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        })
      : '—';
    const desc = r.description
      ? r.description.length > 80
        ? r.description.slice(0, 80) + '...'
        : r.description
      : '—';
    return `<tr>
      <td style="color:var(--gray);font-family:'DM Mono',monospace;font-size:12px">${num}</td>
      <td class="td-ministry">${r.ministry || '—'}</td>
      <td class="td-desc">${desc}</td>
      <td><span class="badge ${sevClass}">${sev}</span></td>
      <td><span class="badge ${r.anonymous ? 'badge-anon' : 'badge-named'}">${r.anonymous ? 'Anonymous' : 'Named'}</span></td>
      <td class="td-time">${date}</td>
      <td><button class="action-btn btn-delete" onclick="deleteReport('${r.id}')">Delete</button></td>
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
  info.textContent = `Showing ${start}–${end} of ${total} reports`;

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
  if (!confirm('Delete this report permanently?')) return;
  const { error } = await db.from('reports').delete().eq('id', id);
  if (error) { showToast('Delete failed.', true); return; }
  showToast('Report deleted.');
  allReports = allReports.filter(r => r.id !== id);
  updateKPIs();
  applyFilters();
}

// ── EXPORT CSV ────────────────────────────────────────────
function exportCSV() {
  if (filtered.length === 0) { showToast('No data to export.', true); return; }
  const headers = ['#', 'Ministry', 'Description', 'Severity', 'Anonymous', 'Date'];
  const rows = filtered.map((r, i) => [
    i + 1,
    `"${(r.ministry || '').replace(/"/g, '""')}"`,
    `"${(r.description || '').replace(/"/g, '""')}"`,
    r.severity || '',
    r.anonymous ? 'Yes' : 'No',
    r.created_at ? new Date(r.created_at).toLocaleString('en-KE') : ''
  ]);
  const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
  const blob = new Blob([csv], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `reports-${new Date().toISOString().slice(0, 10)}.csv`;
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