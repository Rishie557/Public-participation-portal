const OFFICIAL_BILLS = [
  { slug: 'finance-bill-2026', title: 'Finance Bill, 2026', status: 'Signed into Law', group: 'Finance', level: 'national' },
  { slug: 'appropriation-bill-2026', title: 'Appropriation Bill, 2026', status: 'Signed into Law', group: 'Finance', level: 'national' },
  { slug: 'supp-approp-2026', title: 'Supplementary Appropriation Bill, 2026', status: 'Signed into Law', group: 'Finance', level: 'national' },
  { slug: 'division-revenue-2026', title: 'Division of Revenue Bill, 2026', status: 'In Progress', group: 'Finance', level: 'national' },
  { slug: 'county-alloc-2026', title: 'County Governments Additional Allocations Bill, 2026', status: 'In Progress', group: 'Finance', level: 'national' },
  { slug: 'infra-fund-2026', title: 'National Infrastructure Fund Bill, 2026', status: 'Signed into Law', group: 'Infrastructure', level: 'national' },
  { slug: 'food-feed-safety', title: 'Food and Feed Safety Control Coordination Bill', status: 'Signed into Law', group: 'Agriculture', level: 'national' },
  { slug: 'plant-protection', title: 'Plant Protection Bill, 2026', status: 'In Progress', group: 'Agriculture', level: 'national' },
  { slug: 'forest-conservation', title: 'Forest Conservation and Management (Amendment) Bill', status: 'In Progress', group: 'Environment', level: 'national' },
  { slug: 'competition-amendment', title: 'Competition (Amendment) Bill, 2026', status: 'In Progress', group: 'Trade', level: 'national' },
  { slug: 'procurement-amendment', title: 'Public Procurement and Asset Disposal (Amendment) Bill', status: 'In Progress', group: 'Procurement', level: 'national' },
  { slug: 'culture-bill', title: 'Culture Bill, 2024', status: 'In Progress', group: 'Culture', level: 'national' },
  { slug: 'health-amendment', title: 'Health (Amendment) Bill', status: 'In Progress', group: 'Health', level: 'national' },
];

// ── Tab switching (top-level: Bills / Propose / Reviews / Budget) ────
const OFFICIAL_TABS = ['bills', 'propose', 'reviews', 'budget'];
const loadedTabs = new Set();

function switchOfficialTab(tab) {
  OFFICIAL_TABS.forEach(t => {
    document.getElementById(`tab-panel-${t}`).classList.toggle('active', t === tab);
    document.querySelector(`.official-tab[data-tab="${t}"]`).classList.toggle('active', t === tab);
  });

  if (tab === 'propose' && !loadedTabs.has('propose')) {
    renderNewBillPanel();
    loadedTabs.add('propose');
  }
  if (tab === 'reviews') {
    loadPendingReviews();
  }
  if (tab === 'budget') {
    renderBudgetPanel();
  }
}

// ── Stats row ────────────────────────────────────────────────
let billCountsData = {};
let myProposalsData = null;

function updateOfficialStats() {
  const docketCount = OFFICIAL_BILLS.filter(b => (billCountsData[b.slug] || {}).in_docket).length;
  const votesTotal = OFFICIAL_BILLS.reduce((sum, b) => {
    const c = billCountsData[b.slug];
    return sum + (c && c.in_docket ? (c.total || 0) : 0);
  }, 0);
  const docketEl = document.getElementById('stat-docket-count');
  const votesEl = document.getElementById('stat-votes-count');
  if (docketEl) docketEl.textContent = docketCount;
  if (votesEl) votesEl.textContent = votesTotal.toLocaleString();
}

function updatePendingStat() {
  const el = document.getElementById('stat-pending-count');
  if (!el || !Array.isArray(myProposalsData)) return;
  const pendingCount = myProposalsData.filter(p => p.status === 'pending').length;
  el.textContent = pendingCount;
}

// ── Bills grid: search + docket split ─────────────────────────
function filterOfficialBills() {
  const q = document.getElementById('bills-search-input').value;
  renderBillsSection(q);
}

function renderBillsSection(filterText = '') {
  const container = document.getElementById('official-bills-container');
  const q = filterText.trim().toLowerCase();
  const visible = OFFICIAL_BILLS.filter(b => !q || b.title.toLowerCase().includes(q));

  const docketBills = visible.filter(b => (billCountsData[b.slug] || {}).in_docket);
  const otherBills = visible.filter(b => !(billCountsData[b.slug] || {}).in_docket);

  let html = '<div class="official-section-title">📋 Your Docket</div>';
  html += docketBills.length
    ? `<div class="vote-grid">${docketBills.map(b => renderBillCard(b, billCountsData[b.slug])).join('')}</div>`
    : `<div class="official-empty-state"><div class="official-empty-icon">📋</div><div class="official-empty-text">No docket bills match your search.</div></div>`;

  html += '<div class="official-section-title official-section-title-muted">All Other Bills</div>';
  html += otherBills.length
    ? `<div class="vote-grid">${otherBills.map(b => renderBillCard(b, billCountsData[b.slug])).join('')}</div>`
    : `<div class="official-empty-state"><div class="official-empty-icon">🔍</div><div class="official-empty-text">No other bills match your search.</div></div>`;

  container.innerHTML = html;

  OFFICIAL_BILLS.forEach(bill => {
    const btn = document.getElementById(`manage-toggle-${bill.slug}`);
    if (btn) btn.addEventListener('click', () => toggleManagePanel(bill.slug));
  });
}

// ── Pending Reviews ─────────────────────────────────────────
async function loadPendingReviews() {
  const panel = document.getElementById('tab-panel-reviews');
  panel.innerHTML = '<p style="color:#888;">Loading your proposals...</p>';

  try {
    const res = await fetch('get_my_proposals.php');
    const proposals = await res.json();

    if (!res.ok) {
      panel.innerHTML = `<p style="color:#bb0000;">${proposals.error || 'Could not load proposals.'}</p>`;
      return;
    }

    myProposalsData = Array.isArray(proposals) ? proposals : [];
    updatePendingStat();

    if (myProposalsData.length === 0) {
      panel.innerHTML = `
        <div class="official-empty-state">
          <div class="official-empty-icon">✅</div>
          <div class="official-empty-text">Nothing pending right now.</div>
          <div class="official-empty-sub">Try proposing a bill or a tax and spend update.</div>
        </div>`;
      return;
    }

    panel.innerHTML = myProposalsData.map(renderProposalItem).join('');
  } catch (err) {
    panel.innerHTML = '<p style="color:#bb0000;">Could not load proposals.</p>';
    console.error(err);
  }
}

function renderProposalItem(p) {
  const typeLabels = {
    add_bill: '➕ New Bill Proposal',
    remove_bill: '🗑️ Bill Removal Request',
    edit_tax_spend: '✏️ Tax/Spend Update',
  };
  const statusClass = `status-${p.status}`;
  const statusLabel = p.status.charAt(0).toUpperCase() + p.status.slice(1);

  let detail = '';
  if (p.change_type === 'add_bill') {
    detail = `<strong>${escapeHtml(p.payload.title || '')}</strong> (${escapeHtml(p.payload.slug || '')}) — ${escapeHtml(p.payload.bill_status || '')}`;
  } else if (p.change_type === 'remove_bill') {
    detail = `Bill: <strong>${escapeHtml(p.bill_slug)}</strong>`;
  } else if (p.change_type === 'edit_tax_spend') {
    detail = `Bill: <strong>${escapeHtml(p.bill_slug)}</strong> — Year ${escapeHtml(String(p.payload.year || ''))}, Amount ${escapeHtml(String(p.payload.amount || ''))}`;
    if (p.payload.notes) detail += ` — ${escapeHtml(p.payload.notes)}`;
  }

  const reviewedNote = p.reviewed_at ? `<div class="proposal-detail" style="margin-top:4px;">Reviewed: ${escapeHtml(p.reviewed_at)}</div>` : '';

  return `
    <div class="proposal-item">
      <div class="proposal-header">
        <span class="proposal-type">${typeLabels[p.change_type] || p.change_type}</span>
        <span class="proposal-status ${statusClass}">${statusLabel}</span>
      </div>
      <div class="proposal-detail">${detail}</div>
      ${reviewedNote}
    </div>
  `;
}

async function loadOfficialBills() {
  const container = document.getElementById('official-bills-container');
  container.innerHTML = '<p style="color:#888;">Loading...</p>';

  try {
    const res = await fetch('get_official_dashboard.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ slugs: OFFICIAL_BILLS.map(b => b.slug) })
    });

    if (!res.ok) {
      const errBody = await res.json().catch(() => ({}));
      container.innerHTML = `<p style="color:#bb0000;">${errBody.error || 'Could not load results.'}</p>`;
      return;
    }

    billCountsData = await res.json();
    renderBillsSection();
    updateOfficialStats();
  } catch (err) {
    container.innerHTML = '<p style="color:#bb0000;">Could not load results.</p>';
    console.error(err);
  }
}

// ── Bill card: slim vote line + kebab menu for Remove ──
function renderBillCard(bill, counts) {
  counts = counts || { yes: 0, no: 0, total: 0, yes_pct: 0, no_pct: 0, in_docket: false, comment_count: 0 };
  const commentCount = counts.comment_count || 0;
  const commentLabel = commentCount === 1 ? '1 comment' : `${commentCount} comments`;
  const badgeClass = bill.status === 'Signed into Law' ? 'badge-closed' : 'badge-open';
  const badgeIcon = bill.status === 'Signed into Law' ? '✅' : '🕒';
  const docketTag = counts.in_docket
    ? `<span class="vote-badge badge-open" style="margin-left:8px;">📋 Your Docket</span>`
    : '';
  const groupTag = bill.group
    ? `<span class="vote-badge badge-group" style="margin-left:8px;">${escapeHtml(bill.group)}</span>`
    : '';
  const levelTag = bill.level
    ? `<span class="vote-badge badge-closed" style="margin-left:8px;">${bill.level === 'national' ? '🏛️ National' : '📍 County'}</span>`
    : '';

  const kebabHtml = counts.in_docket ? `
    <div style="position:relative;">
      <button class="card-kebab-btn" id="kebab-btn-${bill.slug}" onclick="toggleKebabMenu('${bill.slug}', event)">⋮</button>
      <div class="card-kebab-menu" id="kebab-menu-${bill.slug}" style="display:none;">
        <button onclick="confirmRemoveBill('${bill.slug}')">🗑️ Propose Removing Bill</button>
      </div>
    </div>
  ` : '';

  return `
    <div class="vote-card">
      <div class="vote-card-header">
        <div class="vote-card-meta">
          <div class="vote-badge ${badgeClass}">${badgeIcon} ${bill.status}</div>${docketTag}${groupTag}${levelTag}
          <div class="vote-card-title">${bill.title}</div>
        </div>
      </div>
      <div class="vote-card-body" style="padding:5px 1.25rem 0;">
  <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px;margin-bottom:4px;">
    <span><span style="color:#4caf50;font-weight:600;">${counts.yes_pct}%</span> <span style="color:#999;">Approve</span></span>
    <span><span style="color:#e06666;font-weight:600;">${counts.no_pct}%</span> <span style="color:#999;">Reject</span></span>
  </div>
  <div style="display:flex;height:4px;border-radius:2px;overflow:hidden;background:rgba(255,255,255,0.08);">
    <div style="width:${counts.yes_pct}%;background:#4caf50;"></div>
    <div style="width:${counts.no_pct}%;background:#e06666;"></div>
  </div>
</div>
      <div class="vote-card-footer">
        <span class="vote-count"><strong>${counts.total.toLocaleString()}</strong> votes cast</span>
        <div style="display:flex;align-items:center;gap:4px;">
          <button class="btn-vote btn-comments-toggle" id="manage-toggle-${bill.slug}">${commentLabel}</button>
          ${kebabHtml}
        </div>
      </div>
      <div class="comments-section" id="manage-panel-${bill.slug}" style="display:none;" data-in-docket="${counts.in_docket}"></div>
    </div>
  `;
}

function toggleKebabMenu(slug, e) {
  e.stopPropagation();
  document.querySelectorAll('.card-kebab-menu').forEach(m => {
    if (m.id !== `kebab-menu-${slug}`) m.style.display = 'none';
  });
  const menu = document.getElementById(`kebab-menu-${slug}`);
  if (menu) menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', () => {
  document.querySelectorAll('.card-kebab-menu').forEach(m => m.style.display = 'none');
});

function confirmRemoveBill(slug) {
  const menu = document.getElementById(`kebab-menu-${slug}`);
  if (menu) menu.style.display = 'none';
  submitRemoveBill(slug);
}

function toggleManagePanel(slug) {
  const panel = document.getElementById(`manage-panel-${slug}`);
  const isOpen = panel.style.display !== 'none';
  panel.style.display = isOpen ? 'none' : 'block';
  if (!isOpen) renderManagePanel(slug);
}

// ── Manage panel: Response / Comments only (Tax/Spend moved to Budget
//    tab, Remove moved to card kebab menu). ──
let manageTabStylesInjected = false;
function injectManageTabStyles() {
  if (manageTabStylesInjected) return;
  manageTabStylesInjected = true;
  const style = document.createElement('style');
  style.textContent = `
    .manage-tabs { display:flex; gap:4px; border-bottom:1px solid var(--gray-light); margin-bottom:14px; padding-bottom:8px; flex-wrap:wrap; }
    .manage-tab-btn { background:#fff; border:1px solid var(--gray-light); color:var(--gray); font-size:12.5px; font-weight:600; padding:6px 12px; border-radius:16px; cursor:pointer; font-family:'DM Sans',sans-serif; transition:all .15s; }
    .manage-tab-btn:hover { border-color:#b8d8b8; color:var(--green); }
    .manage-tab-btn.active { background:var(--green-pale); border-color:#b8d8b8; color:var(--green); }
    .manage-tab-btn.manage-tab-danger.active { background:var(--red-pale); border-color:#dbb8b8; color:var(--red); }
    .manage-tab-badge { display:inline-block; background:var(--gray-light); color:var(--gray); font-size:11px; font-weight:700; padding:0 6px; border-radius:8px; margin-left:4px; }
    .manage-tab-panel { display:none; }
  `;
  document.head.appendChild(style);
}

const manageActiveTab = {};

function renderManagePanel(slug) {
  const panel = document.getElementById(`manage-panel-${slug}`);
  const inDocket = panel.dataset.inDocket === 'true';

  if (!inDocket) {
    panel.innerHTML = `
      <div class="official-panel-section">
        <h4 class="comments-title">💬 Citizen Comments</h4>
        <div class="comment-list" id="moderate-list-${slug}"><p class="comment-loading">Loading comments...</p></div>
      </div>
    `;
    loadModerationComments(slug, false);
    return;
  }

  injectManageTabStyles();
  const activeTab = manageActiveTab[slug] || 'response';

  panel.innerHTML = `
    <div class="manage-tabs">
      <button class="manage-tab-btn" data-tab="response" onclick="switchManageTab('${slug}','response')">📢 Response</button>
      <button class="manage-tab-btn" data-tab="comments" onclick="switchManageTab('${slug}','comments')">🛡️ Comments<span class="manage-tab-badge" id="comments-badge-${slug}">…</span></button>
    </div>

    <div class="manage-tab-panel" data-tab-panel="response">
      <div class="comment-form">
        <textarea class="comment-input" id="response-input-${slug}" placeholder="Write an official statement on this bill..."></textarea>
        <button class="comment-submit-btn" onclick="submitOfficialResponse('${slug}')">Post Response</button>
      </div>
    </div>

    <div class="manage-tab-panel" data-tab-panel="comments">
      <div class="comment-list" id="moderate-list-${slug}"><p class="comment-loading">Loading comments...</p></div>
    </div>
  `;

  applyManageTab(slug, activeTab);
  loadModerationComments(slug, true);
}

function switchManageTab(slug, tab) {
  manageActiveTab[slug] = tab;
  applyManageTab(slug, tab);
}

function applyManageTab(slug, tab) {
  const panel = document.getElementById(`manage-panel-${slug}`);
  if (!panel) return;
  panel.querySelectorAll('.manage-tab-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.tab === tab);
  });
  panel.querySelectorAll('.manage-tab-panel').forEach(p => {
    p.style.display = p.dataset.tabPanel === tab ? 'block' : 'none';
  });
}

async function submitOfficialResponse(slug) {
  const input = document.getElementById(`response-input-${slug}`);
  const text = input.value.trim();
  if (text.length < 5) {
    showToast('Please write a longer response.', true);
    return;
  }

  try {
    const res = await fetch('post_official_response.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bill_slug: slug, response_text: text })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Could not post.');

    input.value = '';
    showToast('✓ Official response posted.');
  } catch (err) {
    showToast(err.message, true);
  }
}

// ── Comment list ──
async function loadModerationComments(slug, inDocket) {
  const list = document.getElementById(`moderate-list-${slug}`);
  try {
    const res = await fetch(`get_official_comments.php?bill_slug=${encodeURIComponent(slug)}`);
    const comments = await res.json();

    const badge = document.getElementById(`comments-badge-${slug}`);
    if (badge && Array.isArray(comments)) badge.textContent = comments.length;

    if (!Array.isArray(comments) || comments.length === 0) {
      list.innerHTML = `
        <div class="official-empty-state" style="padding:20px;">
          <div class="official-empty-icon" style="font-size:24px;">💬</div>
          <div class="official-empty-text" style="font-size:13px;">No comments on this bill yet.</div>
        </div>`;
      return;
    }

    list.innerHTML = comments.map(c => {
      const isPending = c.status === 'pending_deletion';
      return `
        <div class="comment-item" style="${isPending ? 'opacity:0.6;' : ''}border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:10px 12px;margin-bottom:8px;">
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;">
            <span class="comment-author" style="font-size:13px;">${escapeHtml(c.name)}</span>
            <span class="comment-time" style="font-size:11px;color:#888;">${c.time}${isPending ? ' · Pending admin review' : ''}</span>
          </div>
          <div class="comment-body" style="font-size:13px;margin-bottom:${inDocket && !isPending ? '8px' : '0'};">${escapeHtml(c.text)}</div>
          ${inDocket && !isPending
            ? `<div style="display:flex;gap:6px;justify-content:flex-end;">
                 <button style="font-size:12px;padding:5px 10px;border-radius:14px;border:1px solid rgba(76,175,80,0.4);background:rgba(76,175,80,0.12);color:#7ed17e;cursor:pointer;" id="useful-btn-${c.id}" onclick="markUseful(${c.id})">👍 Useful</button>
                 <button style="font-size:12px;padding:5px 10px;border-radius:14px;border:1px solid rgba(224,102,102,0.4);background:rgba(224,102,102,0.12);color:#e88a8a;cursor:pointer;" onclick="flagComment(${c.id}, '${slug}')">🚩 Flag</button>
               </div>`
            : ''
          }
        </div>
      `;
    }).join('');
  } catch (err) {
    list.innerHTML = '<p class="no-comments">Could not load comments.</p>';
  }
}

async function flagComment(commentId, slug) {
  const reason = prompt('Why should this comment be removed?');
  if (reason === null) return;
  if (reason.trim().length < 3) {
    showToast('Please provide a reason.', true);
    return;
  }

  try {
    const res = await fetch('moderate_comment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: commentId, action: 'flag', reason: reason.trim() })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Action failed.');

    showToast('Comment flagged for admin review.');
    loadModerationComments(slug, true);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function markUseful(commentId) {
  const btn = document.getElementById(`useful-btn-${commentId}`);
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Marking...';
  }

  try {
    const res = await fetch('mark_useful.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: commentId })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Action failed.');

    if (result.already_marked) {
      showToast('Already marked as useful.');
    } else {
      showToast('✓ Commenter notified that their input was useful.');
    }

    if (btn) {
      btn.textContent = '✓ Marked useful';
    }
  } catch (err) {
    showToast(err.message, true);
    if (btn) {
      btn.disabled = false;
      btn.textContent = '👍 Useful';
    }
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3200);
}

document.addEventListener('DOMContentLoaded', () => {
  loadOfficialBills();
  loadPendingReviews();
});

function renderNewBillPanel() {
  const panel = document.getElementById('tab-panel-propose');
  panel.innerHTML = `
    <h4 class="comments-title">➕ Propose a New Bill</h4>
    <div class="comment-form" style="display:flex;flex-direction:column;gap:8px;max-width:500px;">
      <input class="comment-input" id="new-bill-slug" placeholder="Slug (e.g. water-bill-2026)" />
      <input class="comment-input" id="new-bill-title" placeholder="Title" />
      <input class="comment-input" id="new-bill-status" placeholder="Status label (e.g. In Progress)" />
      <input class="comment-input" id="new-bill-group" placeholder="Group label (e.g. Health)" />
      <div class="official-field-group">
        <label class="official-field-label">Bill Document (PDF, required)</label>
        <input class="comment-input" type="file" id="new-bill-document" accept="application/pdf" style="padding:10px 12px;" />
      </div>
      <button class="comment-submit-btn" onclick="submitNewBill()">Submit for Admin Review</button>
    </div>
  `;
}

async function submitNewBill() {
  const slug = document.getElementById('new-bill-slug').value.trim();
  const title = document.getElementById('new-bill-title').value.trim();
  const bill_status = document.getElementById('new-bill-status').value.trim();
  const group_label = document.getElementById('new-bill-group').value.trim();
  const fileInput = document.getElementById('new-bill-document');
  const file = fileInput.files[0] || null;

  if (!slug || !title || !bill_status) {
    showToast('Slug, title, and status are required.', true);
    return;
  }

  if (!file) {
    showToast('Please attach the bill document (PDF).', true);
    return;
  }

  if (file.type !== 'application/pdf') {
    showToast('Only PDF files are accepted.', true);
    return;
  }

  if (file.size > 15 * 1024 * 1024) {
    showToast('File is too large (max 15MB).', true);
    return;
  }

  const formData = new FormData();
  formData.append('change_type', 'add_bill');
  formData.append('payload', JSON.stringify({ slug, title, bill_status, group_label }));
  formData.append('document', file);

  try {
    const res = await fetch('propose_change.php', {
      method: 'POST',
      body: formData
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Could not submit.');

    showToast('✓ New bill submitted for admin review.');
    document.getElementById('new-bill-slug').value = '';
    document.getElementById('new-bill-title').value = '';
    document.getElementById('new-bill-status').value = '';
    document.getElementById('new-bill-group').value = '';
    fileInput.value = '';
  } catch (err) {
    showToast(err.message, true);
  }
}

// ── Budget tab: Tax/Spend updates for all docket bills in one place ──
function renderBudgetPanel() {
  const panel = document.getElementById('tab-panel-budget');
  const docketBills = OFFICIAL_BILLS.filter(b => (billCountsData[b.slug] || {}).in_docket);

  if (docketBills.length === 0) {
    panel.innerHTML = `
      <div class="official-empty-state">
        <div class="official-empty-icon">💰</div>
        <div class="official-empty-text">No bills in your docket yet.</div>
        <div class="official-empty-sub">Tax/Spend updates apply to bills in your docket.</div>
      </div>`;
    return;
  }

  panel.innerHTML = `<h4 class="comments-title" style="margin-bottom:1rem;">✏️ Tax / Spend Updates</h4>` +
    docketBills.map(b => `
      <div class="official-panel-section">
        <div style="font-weight:700;font-family:'Playfair Display',serif;font-size:15px;margin-bottom:10px;">${escapeHtml(b.title)}</div>
        <div class="official-field-row">
          <div class="official-field-group" style="max-width:100px;">
            <label class="official-field-label">Year</label>
            <input class="comment-input" type="number" id="tax-year-${b.slug}" placeholder="2026" />
          </div>
          <div class="official-field-group" style="max-width:150px;">
            <label class="official-field-label">Amount</label>
            <input class="comment-input" type="number" step="0.01" id="tax-amount-${b.slug}" placeholder="0.00" />
          </div>
          <div class="official-field-group" style="flex:2;">
            <label class="official-field-label">Notes (optional)</label>
            <input class="comment-input" id="tax-notes-${b.slug}" placeholder="Notes" />
          </div>
          <button class="comment-submit-btn" onclick="submitTaxSpendChange('${b.slug}')">Submit for Review</button>
        </div>
      </div>
    `).join('');
}

async function submitTaxSpendChange(slug) {
  const year = parseInt(document.getElementById(`tax-year-${slug}`).value, 10);
  const amount = parseFloat(document.getElementById(`tax-amount-${slug}`).value);
  const notes = document.getElementById(`tax-notes-${slug}`).value.trim();

  if (!year || isNaN(amount)) {
    showToast('Please provide a valid year and amount.', true);
    return;
  }

  try {
    const res = await fetch('propose_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ change_type: 'edit_tax_spend', bill_slug: slug, payload: { year, amount, notes: notes || null } })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Could not submit.');

    showToast('✓ Tax/spend update submitted for admin review.');
    document.getElementById(`tax-year-${slug}`).value = '';
    document.getElementById(`tax-amount-${slug}`).value = '';
    document.getElementById(`tax-notes-${slug}`).value = '';
  } catch (err) {
    showToast(err.message, true);
  }
}

async function submitRemoveBill(slug) {
  if (!confirm('Propose removing this bill? An admin must approve before it is archived.')) return;

  try {
    const res = await fetch('propose_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ change_type: 'remove_bill', bill_slug: slug, payload: {} })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Could not submit.');

    showToast('✓ Bill removal submitted for admin review.');
  } catch (err) {
    showToast(err.message, true);
  }
}