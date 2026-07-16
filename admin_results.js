const ADMIN_BILLS = [
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

async function loadAdminResults() {
  const grid = document.getElementById('admin-results-grid');
  grid.innerHTML = '<p style="color:#888;">Loading results...</p>';

  try {
    const res = await fetch('get_admin_vote_counts.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ slugs: ADMIN_BILLS.map(b => b.slug) })
    });

    if (res.status === 401 || res.status === 403) {
      grid.innerHTML = '<p style="color:#bb0000;">Please log in as an admin to view this page.</p>';
      return;
    }

    const data = await res.json();
    grid.innerHTML = ADMIN_BILLS.map(bill => renderResultCard(bill, data[bill.slug])).join('');
  } catch (err) {
    grid.innerHTML = '<p style="color:#bb0000;">Could not load results.</p>';
    console.error(err);
  }
}

function renderResultCard(bill, counts) {
  counts = counts || { yes: 0, no: 0, total: 0, yes_pct: 0, no_pct: 0 };
  const badgeClass = bill.status === 'Signed into Law' ? 'badge-closed' : 'badge-open';
  const badgeIcon = bill.status === 'Signed into Law' ? '✅' : '🕒';

  return `
    <div class="vote-card">
      <div class="vote-card-header">
        <div class="vote-card-meta">
          <div class="vote-badge ${badgeClass}">${badgeIcon} ${bill.status}</div>
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
        <span class="vote-count">✅ ${counts.yes.toLocaleString()} &nbsp;·&nbsp; ❌ ${counts.no.toLocaleString()}</span>
      </div>
    </div>
  `;
}

document.addEventListener('DOMContentLoaded', loadAdminResults);