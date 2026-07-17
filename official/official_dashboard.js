const OFFICIAL_BILLS = [
  { slug: 'finance-bill-2026', title: 'Finance Bill, 2026', status: 'Signed into Law' },
  { slug: 'appropriation-bill-2026', title: 'Appropriation Bill, 2026', status: 'Signed into Law' },
  { slug: 'supp-approp-2026', title: 'Supplementary Appropriation Bill, 2026', status: 'Signed into Law' },
  { slug: 'division-revenue-2026', title: 'Division of Revenue Bill, 2026', status: 'In Progress' },
  { slug: 'county-alloc-2026', title: 'County Governments Additional Allocations Bill, 2026', status: 'In Progress' },
  { slug: 'infra-fund-2026', title: 'National Infrastructure Fund Bill, 2026', status: 'Signed into Law' },
  { slug: 'food-feed-safety', title: 'Food and Feed Safety Control Coordination Bill', status: 'Signed into Law' },
  { slug: 'plant-protection', title: 'Plant Protection Bill, 2026', status: 'In Progress' },
  { slug: 'forest-conservation', title: 'Forest Conservation and Management (Amendment) Bill', status: 'In Progress' },
  { slug: 'competition-amendment', title: 'Competition (Amendment) Bill, 2026', status: 'In Progress' },
  { slug: 'procurement-amendment', title: 'Public Procurement and Asset Disposal (Amendment) Bill', status: 'In Progress' },
  { slug: 'culture-bill', title: 'Culture Bill, 2024', status: 'In Progress' },
  { slug: 'health-amendment', title: 'Health (Amendment) Bill', status: 'In Progress' },
];
// ── Tab switching ──────────────────────────────────────────
const OFFICIAL_TABS = ['bills', 'propose', 'reviews'];
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

    if (!Array.isArray(proposals) || proposals.length === 0) {
      panel.innerHTML = '<p class="no-comments">You haven\'t submitted any proposals yet.</p>';
      return;
    }

    panel.innerHTML = proposals.map(renderProposalItem).join('');
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
  const grid = document.getElementById('official-bills-grid');
  grid.innerHTML = '<p style="color:#888;">Loading...</p>';

  try {
    const res = await fetch('get_official_dashboard.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ slugs: OFFICIAL_BILLS.map(b => b.slug) })
    });

    if (!res.ok) {
      const errBody = await res.json().catch(() => ({}));
      grid.innerHTML = `<p style="color:#bb0000;">${errBody.error || 'Could not load results.'}</p>`;
      return;
    }

    const data = await res.json();
    grid.innerHTML = OFFICIAL_BILLS.map(bill => renderBillCard(bill, data[bill.slug])).join('');

    OFFICIAL_BILLS.forEach(bill => {
      const btn = document.getElementById(`manage-toggle-${bill.slug}`);
      if (btn) btn.addEventListener('click', () => toggleManagePanel(bill.slug));
    });
  } catch (err) {
    grid.innerHTML = '<p style="color:#bb0000;">Could not load results.</p>';
    console.error(err);
  }
}
function renderBillCard(bill, counts) {
  counts = counts || { yes: 0, no: 0, total: 0, yes_pct: 0, no_pct: 0, in_docket: false };
  const badgeClass = bill.status === 'Signed into Law' ? 'badge-closed' : 'badge-open';
  const badgeIcon = bill.status === 'Signed into Law' ? '✅' : '🕒';
  const docketTag = counts.in_docket
    ? `<span class="vote-badge badge-open" style="margin-left:8px;">📋 Your Docket</span>`
    : '';

  return `
    <div class="vote-card">
      <div class="vote-card-header">
        <div class="vote-card-meta">
          <div class="vote-badge ${badgeClass}">${badgeIcon} ${bill.status}</div>${docketTag}
          <div class="vote-card-title">${bill.title}</div>
        </div>
      </div>
      <div class="vote-card-body">
        <div class="vote-bar-row">
          <span class="vote-bar-label yes">Approve</span>
          <div class="vote-bar-track"><div class="vote-bar-fill fill-yes" style="width:${counts.yes_pct}%"></div></div>
          <span class="vote-bar-pct">${counts.yes_pct}%</span>
        </div>
        <div class="vote-bar-row">
          <span class="vote-bar-label no">Reject</span>
          <div class="vote-bar-track"><div class="vote-bar-fill fill-no" style="width:${counts.no_pct}%"></div></div>
          <span class="vote-bar-pct">${counts.no_pct}%</span>
        </div>
      </div>
      <div class="vote-card-footer">
        <span class="vote-count"><strong>${counts.total.toLocaleString()}</strong> votes cast</span>
        <button class="btn-vote btn-vote-yes" id="manage-toggle-${bill.slug}">View Comments →</button>
      </div>
      <div class="comments-section" id="manage-panel-${bill.slug}" style="display:none;" data-in-docket="${counts.in_docket}"></div>
    </div>
  `;
}

function toggleManagePanel(slug) {
  const panel = document.getElementById(`manage-panel-${slug}`);
  const isOpen = panel.style.display !== 'none';
  panel.style.display = isOpen ? 'none' : 'block';
  if (!isOpen) renderManagePanel(slug);
}

function renderManagePanel(slug) {
  const panel = document.getElementById(`manage-panel-${slug}`);
  const inDocket = panel.dataset.inDocket === 'true';

  panel.innerHTML = `
    ${inDocket ? `
      <div class="official-panel-section">
        <h4 class="comments-title">📢 Post Official Response</h4>
        <div class="comment-form">
          <textarea class="comment-input" id="response-input-${slug}" placeholder="Write an official statement on this bill..."></textarea>
          <button class="comment-submit-btn" onclick="submitOfficialResponse('${slug}')">Post Response</button>
        </div>
      </div>

      <div class="official-panel-section">
        <h4 class="comments-title">✏️ Propose Tax/Spend Update</h4>
        <div class="official-field-row">
          <div class="official-field-group" style="max-width:100px;">
            <label class="official-field-label">Year</label>
            <input class="comment-input" type="number" id="tax-year-${slug}" placeholder="2026" />
          </div>
          <div class="official-field-group" style="max-width:150px;">
            <label class="official-field-label">Amount</label>
            <input class="comment-input" type="number" step="0.01" id="tax-amount-${slug}" placeholder="0.00" />
          </div>
          <div class="official-field-group" style="flex:2;">
            <label class="official-field-label">Notes (optional)</label>
            <input class="comment-input" id="tax-notes-${slug}" placeholder="Notes" />
          </div>
          <button class="comment-submit-btn" onclick="submitTaxSpendChange('${slug}')">Submit for Review</button>
        </div>
      </div>

      <div class="official-panel-section official-danger-section">
        <h4 class="comments-title">🗑️ Remove This Bill</h4>
        <p class="official-danger-note">This will archive the bill from the citizen site only after an admin approves the request.</p>
        <button class="comment-submit-btn" style="background:var(--red);" onclick="submitRemoveBill('${slug}')">Propose Removing This Bill</button>
      </div>
    ` : ''}

    <div class="official-panel-section">
      <h4 class="comments-title">${inDocket ? '🛡️ Moderate Citizen Comments' : '💬 Citizen Comments'}</h4>
      <div class="comment-list" id="moderate-list-${slug}"><p class="comment-loading">Loading comments...</p></div>
    </div>
  `;
  loadModerationComments(slug, inDocket);
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
async function loadModerationComments(slug, inDocket) {
  const list = document.getElementById(`moderate-list-${slug}`);
  try {
    const res = await fetch(`get_official_comments.php?bill_slug=${encodeURIComponent(slug)}`);
    const comments = await res.json();

    if (!Array.isArray(comments) || comments.length === 0) {
      list.innerHTML = '<p class="no-comments">No comments on this bill yet.</p>';
      return;
    }

    list.innerHTML = comments.map(c => {
      const isPending = c.status === 'pending_deletion';
      return `
        <div class="comment-item" style="${isPending ? 'opacity:0.6;' : ''}">
          <div class="comment-header">
            <span class="comment-author">${escapeHtml(c.name)}</span>
            <span class="comment-time">${c.time}${isPending ? ' · Pending admin review' : ''}</span>
          </div>
          <div class="comment-body">${escapeHtml(c.text)}</div>
          ${inDocket && !isPending
            ? `<button class="comment-submit-btn" style="margin-top:6px;background:var(--red);" onclick="flagComment(${c.id}, '${slug}')">Flag for deletion</button>`
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
    loadModerationComments(slug, true); // this official reached the Flag button, so they're in-docket
  } catch (err) {
    showToast(err.message, true);
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

document.addEventListener('DOMContentLoaded', loadOfficialBills);


function renderNewBillPanel() {
  const panel = document.getElementById('tab-panel-propose');
  panel.innerHTML = `
    <h4 class="comments-title">➕ Propose a New Bill</h4>
    <div class="comment-form" style="display:flex;flex-direction:column;gap:8px;max-width:500px;">
      <input class="comment-input" id="new-bill-slug" placeholder="Slug (e.g. water-bill-2026)" />
      <input class="comment-input" id="new-bill-title" placeholder="Title" />
      <input class="comment-input" id="new-bill-status" placeholder="Status label (e.g. In Progress)" />
      <input class="comment-input" id="new-bill-group" placeholder="Group label (e.g. Health)" />
      <button class="comment-submit-btn" onclick="submitNewBill()">Submit for Admin Review</button>
    </div>
  `;
}

async function submitNewBill() {
  const slug = document.getElementById('new-bill-slug').value.trim();
  const title = document.getElementById('new-bill-title').value.trim();
  const bill_status = document.getElementById('new-bill-status').value.trim();
  const group_label = document.getElementById('new-bill-group').value.trim();

  if (!slug || !title || !bill_status) {
    showToast('Slug, title, and status are required.', true);
    return;
  }

  try {
    const res = await fetch('propose_change.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ change_type: 'add_bill', payload: { slug, title, bill_status, group_label } })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Could not submit.');

    showToast('✓ New bill submitted for admin review.');
    document.getElementById('new-bill-slug').value = '';
    document.getElementById('new-bill-title').value = '';
    document.getElementById('new-bill-status').value = '';
    document.getElementById('new-bill-group').value = '';
  } catch (err) {
    showToast(err.message, true);
  }
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