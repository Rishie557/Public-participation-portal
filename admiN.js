// ── AUTH ──────────────────────────────────────────────────
function escapeHTML(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

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
    const res = await fetch('admin_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username: u, password: p })
    });
    const result = await res.json();
    if (!res.ok) throw new Error(result.error || 'Login failed');

    document.getElementById('login-screen').style.display = 'none';
    document.getElementById('admin-panel').style.display = 'block';
    loadReports();
    switchOfficialsTab('pending');
  } catch (err) {
    document.getElementById('login-error').textContent = err.message;
    document.getElementById('login-error').style.display = 'block';
    btn.disabled = false;
    btn.textContent = 'Sign In →';
  }
}

async function doLogout() {
  try {
    await fetch('admin_logout.php', { method: 'POST' });
  } catch (err) {
    console.error(err);
  }
  location.reload();
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
      ? escapeHTML(r.description.length > 120
          ? r.description.slice(0, 120) + '...'
          : r.description)
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

// ── OFFICIAL REGISTRATIONS (tabbed) ───────────────────────
let currentOfficialsTab = 'pending';

function switchOfficialsTab(status) {
  currentOfficialsTab = status;
  ['pending', 'approved', 'rejected'].forEach(s => {
    document.getElementById(`tab-officials-${s}`).classList.toggle('active', s === status);
  });
  loadOfficialsByStatus(status);
}

async function loadOfficialsByStatus(status) {
  const tbody = document.getElementById('officials-tbody');
  tbody.innerHTML = '<tr><td colspan="6"><div class="loading">Loading…</div></td></tr>';

  try {
    const res = await fetch(`get_officials_by_status.php?status=${status}`);
    if (!res.ok) throw new Error('Request failed');
    const officials = await res.json();
    renderOfficialsTable(officials, status);
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="6">Failed to load registrations.</td></tr>';
    console.error(err);
  }
}

function renderOfficialsTable(officials, status) {
  const tbody = document.getElementById('officials-tbody');
  const actionHeader = document.getElementById('officials-action-header');
  actionHeader.textContent = status === 'pending' ? 'Action' : 'Reviewed';

  if (officials.length === 0) {
    const emptyText = status === 'pending' ? 'No pending registrations'
      : status === 'approved' ? 'No approved officials yet'
      : 'No rejected registrations';
    tbody.innerHTML = `<tr><td colspan="6">
      <div class="empty-state">
        <div class="empty-icon">${status === 'pending' ? '✅' : '📋'}</div>
        <div class="empty-text">${emptyText}</div>
      </div></td></tr>`;
    return;
  }

  tbody.innerHTML = officials.map(o => {
    const registeredDate = o.created_at
      ? new Date(o.created_at).toLocaleString('en-KE', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        })
      : '—';
    const location = [o.county_name, o.constituency_name].filter(Boolean).join(' / ') || '—';

    let actionCell;
    if (status === 'pending') {
      actionCell = `
        <button class="action-btn" style="background:var(--green);color:#fff" onclick="approveOfficial(${o.user_id})">Approve</button>
        <button class="action-btn btn-delete" onclick="rejectOfficial(${o.user_id})">Reject</button>`;
    } else {
      const reviewedDate = o.reviewed_at
        ? new Date(o.reviewed_at).toLocaleString('en-KE', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
          })
        : '—';
      actionCell = status === 'rejected' && o.notes
        ? `${reviewedDate}<br><span style="font-size:11px;color:var(--gray)">Reason: ${escapeHTML(o.notes)}</span>`
        : reviewedDate;
    }

    return `<tr>
      <td>${escapeHTML(o.full_name)}</td>
      <td>${escapeHTML(o.phone)}</td>
      <td>${escapeHTML(o.office_id_number || '—')}</td>
      <td>${escapeHTML(location)}</td>
      <td class="td-time">${registeredDate}</td>
      <td style="display:flex;gap:6px;align-items:center">${actionCell}</td>
    </tr>`;
  }).join('');
}

async function approveOfficial(userId) {
  if (!confirm('Approve this official? They will be able to post as a verified government official.')) return;
  try {
    const res = await fetch('approve_official.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Approval failed');
    showToast('Official approved.');
    loadOfficialsByStatus(currentOfficialsTab);
  } catch (err) {
    showToast('Approval failed.', true);
    console.error(err);
  }
}

async function rejectOfficial(userId) {
  const reason = prompt('Reason for rejection (optional):');
  if (reason === null) return;

  try {
    const res = await fetch('reject_official.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, notes: reason })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Rejection failed');
    showToast('Official rejected.');
    loadOfficialsByStatus(currentOfficialsTab);
  } catch (err) {
    showToast('Rejection failed.', true);
    console.error(err);
  }
}

// ── TOAST ─────────────────────────────────────────────────
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ── SESSION RESTORE ───────────────────────────────────────
(async function checkSession() {
  try {
    const res = await fetch('admin_check_session.php');
    const result = await res.json();
    if (result.logged_in) {
      document.getElementById('login-screen').style.display = 'none';
      document.getElementById('admin-panel').style.display = 'block';
      loadReports();
      switchOfficialsTab('pending');
    }
  } catch (err) {
    console.error('Session check failed:', err);
  }
})();

async function loadPendingDeletions() {
  const tbody = document.getElementById('pending-deletions-tbody');
  tbody.innerHTML = '<tr><td colspan="5"><div class="loading">Loading…</div></td></tr>';

  try {
    const res = await fetch('get_pending_deletions.php');
    if (!res.ok) throw new Error('Request failed');
    const pending = await res.json();
    renderPendingDeletions(pending);
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="5">Failed to load pending deletions.</td></tr>';
    console.error(err);
  }
}

function renderPendingDeletions(pending) {
  const tbody = document.getElementById('pending-deletions-tbody');

  if (pending.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5">
      <div class="empty-state">
        <div class="empty-icon">✅</div>
        <div class="empty-text">No comments pending review</div>
      </div></td></tr>`;
    return;
  }

  tbody.innerHTML = pending.map(p => `
    <tr>
      <td class="td-desc">${escapeHTML(p.text)}</td>
      <td>${escapeHTML(p.bill_slug)}</td>
      <td>${escapeHTML(p.flagged_by_name)}</td>
      <td>${escapeHTML(p.flagged_reason)}<br><span style="font-size:11px;color:var(--gray)">${p.flagged_at}</span></td>
      <td style="display:flex;gap:6px">
        <button class="action-btn" style="background:var(--green);color:#fff" onclick="reviewDeletion(${p.id}, 'approve')">Approve</button>
        <button class="action-btn btn-delete" onclick="reviewDeletion(${p.id}, 'reject')">Reject</button>
      </td>
    </tr>
  `).join('');
}

async function reviewDeletion(id, action) {
  const label = action === 'approve' ? 'delete' : 'restore';
  if (!confirm(`Are you sure you want to ${label} this comment?`)) return;

  try {
    const res = await fetch('review_deletion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: id, action })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Action failed');
    showToast(action === 'approve' ? 'Comment deleted.' : 'Comment restored.');
    loadPendingDeletions();
  } catch (err) {
    showToast('Action failed.', true);
    console.error(err);
  }
}
async function loadPendingChanges() {
  const tbody = document.getElementById('pending-changes-tbody');
  tbody.innerHTML = '<tr><td colspan="6"><div class="loading">Loading…</div></td></tr>';

  try {
    const res = await fetch('get_pending_changes.php');
    if (!res.ok) throw new Error('Request failed');
    const pending = await res.json();
    renderPendingChanges(pending);
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="6">Failed to load pending changes.</td></tr>';
    console.error(err);
  }
}

const CHANGE_TYPE_LABELS = {
  add_bill: '➕ Add Bill',
  remove_bill: '🗑️ Remove Bill',
  edit_tax_spend: '✏️ Edit Tax/Spend'
};

function describeChangePayload(change) {
  const p = change.payload || {};
  if (change.change_type === 'add_bill') {
    return `<strong>${escapeHTML(p.title || '')}</strong><br>
      <span style="font-size:11px;color:var(--gray)">slug: ${escapeHTML(p.slug || '')} · ${escapeHTML(p.bill_status || '')} · ${escapeHTML(p.group_label || '—')}</span>`;
  }
  if (change.change_type === 'edit_tax_spend') {
    return `Year: <strong>${escapeHTML(String(p.year ?? ''))}</strong> · Amount: <strong>${escapeHTML(String(p.amount ?? ''))}</strong>
      ${p.notes ? `<br><span style="font-size:11px;color:var(--gray)">${escapeHTML(p.notes)}</span>` : ''}`;
  }
  if (change.change_type === 'remove_bill') {
    return `<span style="font-size:11px;color:var(--gray)">Bill will be archived (hidden from citizens, kept in DB).</span>`;
  }
  return '—';
}

function renderPendingChanges(pending) {
  const tbody = document.getElementById('pending-changes-tbody');

  if (pending.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6">
      <div class="empty-state">
        <div class="empty-icon">✅</div>
        <div class="empty-text">No pending bill or tax changes</div>
      </div></td></tr>`;
    return;
  }

  tbody.innerHTML = pending.map(c => {
    const proposedDate = c.proposed_at
      ? new Date(c.proposed_at).toLocaleString('en-KE', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        })
      : '—';
    return `<tr>
      <td>${CHANGE_TYPE_LABELS[c.change_type] || escapeHTML(c.change_type)}</td>
      <td>${escapeHTML(c.bill_slug || '—')}</td>
      <td class="td-desc">${describeChangePayload(c)}</td>
      <td>${escapeHTML(c.proposed_by_name)}</td>
      <td class="td-time">${proposedDate}</td>
      <td style="display:flex;gap:6px">
        <button class="action-btn" style="background:var(--green);color:#fff" onclick="reviewChange(${c.id}, 'approve')">Approve</button>
        <button class="action-btn btn-delete" onclick="reviewChange(${c.id}, 'reject')">Reject</button>
      </td>
    </tr>`;
  }).join('');
}

async function reviewChange(id, action) {
  const label = action === 'approve' ? 'apply' : 'reject';
  if (!confirm(`Are you sure you want to ${label} this change?`)) return;

  try {
    const res = await fetch('review_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ change_id: id, action })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Action failed');
    showToast(action === 'approve' ? 'Change applied.' : 'Change rejected.');
    loadPendingChanges();
  } catch (err) {
    showToast('Action failed.', true);
    console.error(err);
  }
}