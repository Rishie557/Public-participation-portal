const votedOn = {};

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3200);
}

function castVote(btn, vote, id) {
  if (votedOn[id]) { showToast('You have already voted on this bill.', true); return; }
  votedOn[id] = vote;
  const footer = btn.closest('.vote-card-footer');
  footer.querySelectorAll('.btn-vote').forEach(b => b.classList.add('voted'));
  showToast(vote === 'yes' ? '✓ Approved — your vote has been recorded.' : '✗ Rejected — your vote has been recorded.');
  const card = btn.closest('.vote-card');
  const fills = card.querySelectorAll('.vote-bar-fill');
  const pcts = card.querySelectorAll('.vote-bar-pct');
  fills.forEach((f, i) => {
    const cur = parseInt(f.style.width);
    const adj = vote === 'yes' ? (i === 0 ? cur + 1 : cur - 1) : (i === 1 ? cur + 1 : cur - 1);
    f.style.width = adj + '%';
    pcts[i].textContent = adj + '%';
  });
  const countEl = footer.querySelector('.vote-count strong');
  const cur = parseInt(countEl.textContent.replace(/,/g, ''));
  countEl.textContent = (cur + 1).toLocaleString();
}

async function submitReport() {
  const desc = document.getElementById('report-desc').value.trim();

  if (!desc || desc.length < 20) {
    showToast('Please share more detail (min. 20 characters).', true);
    return;
  }

  try {
    const res = await fetch('submit_report.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ description: desc })
    });
    const result = await res.json();
    if (!res.ok || result.error) throw new Error(result.error || 'Submission failed');

    showToast('✓ Comment submitted. Reference #KE-' + Math.random().toString(36).substr(2, 8).toUpperCase());
    document.getElementById('report-desc').value = '';
  } catch (err) {
    showToast('Submission failed. Please try again.', true);
    console.error('Error:', err);
  }
}

function pwShowTab(btn, panelId) {
  document.querySelectorAll('.pw-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.pw-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(panelId).classList.add('active');
}