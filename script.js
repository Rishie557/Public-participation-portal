const { createClient } = supabase;
const db = createClient(
  'https://pktxbzbgaeqkyflvkbvj.supabase.co',
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBrdHhiemJnYWVxa3lmbHZrYnZqIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODAxOTk2MzEsImV4cCI6MjA5NTc3NTYzMX0.mlLWsiyD7J9UVJYn2UgIqT1onTwlOmM03tJANN4nv30'
);

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

function setSeverity(btn) {
  document.querySelectorAll('.severity-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function toggleAnon() {
  const track = document.getElementById('anon-track');
  const label = document.getElementById('anon-label');
  track.classList.toggle('on');
  label.textContent = track.classList.contains('on')
    ? 'Submit anonymously (recommended)'
    : 'Submit with my name (contact me for follow-up)';
}

async function submitReport() {
  const ministry = document.getElementById('ministry-select').value;
  const desc = document.getElementById('report-desc').value.trim();
  const severity = document.querySelector('.severity-btn.active')?.textContent || 'Low';
  const anonymous = document.getElementById('anon-track').classList.contains('on');

  if (!ministry) { showToast('Please select a ministry.', true); return; }
  if (!desc || desc.length < 20) { showToast('Please provide more detail (min. 20 characters).', true); return; }

  const { error } = await db.from('reports').insert([{
    ministry,
    description: desc,
    severity,
    anonymous
  }]);

  console.log('Supabase response:', error);

  if (error) {
    showToast('Submission failed. Please try again.', true);
    console.error('Full error:', error);
    return;
  }

  showToast('🔒 Report submitted. Reference #KE-' + Math.random().toString(36).substr(2, 8).toUpperCase());
  document.getElementById('ministry-select').value = '';
  document.getElementById('report-desc').value = '';
}