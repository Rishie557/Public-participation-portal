const COMMENT_GROUPS = [
  {
    label: 'Budget & Appropriations',
    bills: [
      { slug: 'finance-bill-2026', title: 'Finance Bill, 2026' },
      { slug: 'appropriation-bill-2026', title: 'Appropriation Bill, 2026' },
      { slug: 'supp-approp-2026', title: 'Supplementary Appropriation Bill, 2026' },
      { slug: 'division-revenue-2026', title: 'Division of Revenue Bill, 2026' },
      { slug: 'county-alloc-2026', title: 'County Governments Additional Allocations Bill, 2026' },
    ]
  },
  {
    label: 'Infrastructure & Investment',
    bills: [
      { slug: 'infra-fund-2026', title: 'National Infrastructure Fund Bill, 2026' },
    ]
  },
  {
    label: 'Agriculture, Food & Environment',
    bills: [
      { slug: 'food-feed-safety', title: 'Food and Feed Safety Control Coordination Bill' },
      { slug: 'plant-protection', title: 'Plant Protection Bill, 2026' },
      { slug: 'forest-conservation', title: 'Forest Conservation and Management (Amendment) Bill' },
    ]
  },
  {
    label: 'Governance & Public Administration',
    bills: [
      { slug: 'competition-amendment', title: 'Competition (Amendment) Bill, 2026' },
      { slug: 'procurement-amendment', title: 'Public Procurement and Asset Disposal (Amendment) Bill' },
      { slug: 'culture-bill', title: 'Culture Bill, 2024' },
    ]
  },
  {
    label: 'Health',
    bills: [
      { slug: 'health-amendment', title: 'Health (Amendment) Bill' },
    ]
  },
];

function buildCommentsPage() {
  const root = document.getElementById('comments-page-root');

  root.innerHTML = COMMENT_GROUPS.map(group => `
    <div class="vote-section-group">
      <div class="vote-group-label">${group.label}</div>
      <div class="vote-grid">
        ${group.bills.map(bill => `
          <div class="vote-card">
            <div class="vote-card-header">
              <div class="vote-card-meta">
                <div class="vote-card-title">${bill.title}</div>
              </div>
            </div>
            <div class="comments-section" data-bill="${bill.slug}">
              <div class="comment-list" id="comments-${bill.slug}">
                <p class="comment-loading">Loading comments...</p>
              </div>
            </div>
          </div>
        `).join('')}
      </div>
    </div>
  `).join('');

  COMMENT_GROUPS.forEach(group => {
    group.bills.forEach(bill => loadComments(bill.slug));
  });
}

async function loadComments(slug) {
  const list = document.getElementById(`comments-${slug}`);
  if (!list) return;

  try {
    const res = await fetch(`get_comments.php?bill_slug=${encodeURIComponent(slug)}`);
    const comments = await res.json();

    if (!Array.isArray(comments) || comments.length === 0) {
      list.innerHTML = `<p class="no-comments">No comments yet.<br>Be the first citizen to share your opinion on the <a href="Votes.php">Vote page</a>.</p>`;
      return;
    }

    list.innerHTML = comments.map(c => `
      <div class="comment-item">
        <div class="comment-header">
          <span class="comment-author">${escapeHtml(c.name)}</span>
          <span class="comment-time">${c.time}</span>
        </div>
        <div class="comment-body">${escapeHtml(c.text)}</div>
      </div>
    `).join('');
  } catch (err) {
    console.error(err);
    list.innerHTML = `<p class="no-comments">Unable to load comments.</p>`;
  }
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', buildCommentsPage);