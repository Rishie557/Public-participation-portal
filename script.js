const votedOn = {};

function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast' + (isError ? ' error' : '');
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => t.classList.remove('show'), 3200);
}

// ── Extract bill slugs from the page and load real vote counts ──
function getAllBillSlugs() {
  const slugs = [];
  document.querySelectorAll('.btn-vote-yes').forEach(btn => {
    const match = btn.getAttribute('onclick').match(/castVote\(this,'yes','([^']+)'\)/);
    if (match) slugs.push(match[1]);
  });
  return slugs;
}

function findCardBySlug(slug) {
  const btn = document.querySelector(`.btn-vote-yes[onclick*="'${slug}'"]`);
  return btn ? btn.closest('.vote-card') : null;
}

function applyVoteDataToCard(card, data) {
  const fills = card.querySelectorAll('.vote-bar-fill');
  const pcts = card.querySelectorAll('.vote-bar-pct');
  const countEl = card.querySelector('.vote-count strong');

  if (fills[0]) fills[0].style.width = data.yes_pct + '%';
  if (fills[1]) fills[1].style.width = data.no_pct + '%';
  if (pcts[0]) pcts[0].textContent = data.yes_pct + '%';
  if (pcts[1]) pcts[1].textContent = data.no_pct + '%';
  if (countEl) countEl.textContent = data.total.toLocaleString();

  if (data.user_vote) {
    const footer = card.querySelector('.vote-card-footer');
    footer.querySelectorAll('.btn-vote').forEach(b => b.classList.add('voted'));
    const slugMatch = footer.querySelector('.btn-vote-yes').getAttribute('onclick').match(/'([^']+)'\)$/);
    if (slugMatch) votedOn[slugMatch[1]] = data.user_vote;
  }
}

async function loadVoteCounts() {
  const slugs = getAllBillSlugs();
  if (slugs.length === 0) return;

  try {
    const res = await fetch('get_vote_counts.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ slugs })
    });
    if (!res.ok) throw new Error('Failed to load vote counts');
    const data = await res.json();

    slugs.forEach(slug => {
      const card = findCardBySlug(slug);
      if (card && data[slug]) applyVoteDataToCard(card, data[slug]);
    });
  } catch (err) {
    console.error('Could not load vote counts:', err);
  }
}

document.addEventListener('DOMContentLoaded', loadVoteCounts);

// ── Cast a vote (real, server-backed, one-per-user) ──
async function castVote(btn, vote, id) {
  if (votedOn[id]) {
    showToast('You have already voted on this bill.', true);
    return;
  }

  const footer = btn.closest('.vote-card-footer');
  const card = btn.closest('.vote-card');

  try {
    const res = await fetch('cast_vote.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bill_slug: id, vote })
    });
    const result = await res.json();

    if (!res.ok || result.error) {
      throw new Error(result.error || 'Could not record your vote.');
    }

    votedOn[id] = vote;
    footer.querySelectorAll('.btn-vote').forEach(b => b.classList.add('voted'));
    showToast(vote === 'yes' ? '✓ Approved — your vote has been recorded.' : '✗ Rejected — your vote has been recorded.');

    applyVoteDataToCard(card, result.counts);
  } catch (err) {
    if (err.message.includes('log in')) {
      showToast('Please log in to vote.', true);
    } else if (err.message.includes('already voted')) {
      votedOn[id] = vote; // sync local state so button disables even if UI missed it
      footer.querySelectorAll('.btn-vote').forEach(b => b.classList.add('voted'));
      showToast('You have already voted on this bill.', true);
    } else {
      showToast(err.message, true);
    }
    console.error('Vote error:', err);
  }
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

// ══════════════════════════════════════════════════════════
// ACCESSIBILITY — Read Aloud Mode, voice picker, Listen buttons, Larger Text
// (these were referenced in the HTML via onclick but never defined,
// which is why the Listen button and accessibility bar weren't working)
// ══════════════════════════════════════════════════════════

let selectedVoice = null;
let readAloudModeOn = false;

function populateVoiceList() {
  const picker = document.getElementById('voice-picker');
  if (!picker || !('speechSynthesis' in window)) return;

  const voices = speechSynthesis.getVoices();
  if (!voices.length) return;

  const previouslySelected = selectedVoice ? selectedVoice.name : null;
  picker.innerHTML = '';

  voices.forEach(voice => {
    const opt = document.createElement('option');
    opt.value = voice.name;
    opt.textContent = `${voice.name} (${voice.lang})`;
    picker.appendChild(opt);
  });

  // Restore prior selection if it still exists, otherwise default/first voice
  const restored = previouslySelected && voices.find(v => v.name === previouslySelected);
  selectedVoice = restored || voices.find(v => v.default) || voices[0];
  picker.value = selectedVoice.name;
}

if ('speechSynthesis' in window) {
  populateVoiceList();
  // Chrome loads the voice list asynchronously, so it's often empty on first call
  speechSynthesis.onvoiceschanged = populateVoiceList;
}

function setVoice(voiceName) {
  if (!('speechSynthesis' in window)) return;
  const match = speechSynthesis.getVoices().find(v => v.name === voiceName);
  if (match) selectedVoice = match;
}

function stopAllListenButtons() {
  document.querySelectorAll('.card-listen-btn.speaking').forEach(b => {
    b.classList.remove('speaking');
    b.textContent = '🔊 Listen';
  });
}

function toggleReadAloudMode() {
  if (!('speechSynthesis' in window)) {
    showToast('Sorry, your browser does not support read-aloud.', true);
    return;
  }

  readAloudModeOn = !readAloudModeOn;
  const btn = document.getElementById('btn-readaloud');
  if (btn) btn.classList.toggle('on', readAloudModeOn);

  if (readAloudModeOn) {
    showToast('Read Aloud Mode is on — tap 🔊 Listen on any bill.');
  } else {
    speechSynthesis.cancel();
    stopAllListenButtons();
  }
}

function toggleBigText() {
  const isBig = document.body.classList.toggle('big-text');
  const btn = document.getElementById('btn-bigtext');
  if (btn) btn.classList.toggle('on', isBig);
}

function readCard(btn) {
  if (!('speechSynthesis' in window)) {
    showToast('Sorry, your browser does not support read-aloud.', true);
    return;
  }

  const wasSpeaking = btn.classList.contains('speaking');

  // Always stop whatever is currently playing first
  speechSynthesis.cancel();
  stopAllListenButtons();

  // If this button was the one speaking, treat the click as "stop"
  if (wasSpeaking) return;

  const card = btn.closest('.vote-card');
  const title = card.querySelector('.vote-card-title')?.textContent.trim() || '';
  const desc = card.querySelector('.vote-card-desc')?.textContent.trim() || '';

  const utterance = new SpeechSynthesisUtterance(`${title}. ${desc}`);
  if (selectedVoice) utterance.voice = selectedVoice;

  utterance.onend = () => {
    btn.classList.remove('speaking');
    btn.textContent = '🔊 Listen';
  };
  utterance.onerror = () => {
    btn.classList.remove('speaking');
    btn.textContent = '🔊 Listen';
  };

  btn.classList.add('speaking');
  btn.textContent = '⏹ Stop';
  speechSynthesis.speak(utterance);
}
// ═══════════════════════════════════════════════
// BILL COMMENTS
// ═══════════════════════════════════════════════

async function loadComments(slug) {
    const list = document.getElementById(`comments-${slug}`);
    if (!list) return;

    list.innerHTML = '<p class="comment-loading">Loading comments...</p>';

    try {
        const res = await fetch(`get_comments.php?bill_slug=${encodeURIComponent(slug)}`);
        const comments = await res.json();

        if (!Array.isArray(comments) || comments.length === 0) {
            list.innerHTML = `
                <p class="no-comments">
                    No comments yet.<br>
                    Be the first citizen to share your opinion.
                </p>`;
            return;
        }

        list.innerHTML = comments.map(c => `
            <div class="comment-item">
                <div class="comment-header">
                    <span class="comment-author">${escapeHtml(c.name)}</span>
                    <span class="comment-time">${c.time}</span>
                </div>

                <div class="comment-body">
                    ${escapeHtml(c.text)}
                </div>
            </div>
        `).join('');

    } catch (err) {
        console.error(err);
        list.innerHTML = `
            <p class="no-comments">
                Unable to load comments.
            </p>`;
    }
}

async function submitComment(btn, slug) {

    const form = btn.closest('.comment-form');
    const textarea = form.querySelector('.comment-input');

    const comment = textarea.value.trim();

    if (comment.length < 5) {
        showToast('Please write a longer comment.', true);
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Posting...';

    try {

        const res = await fetch('submit_comment.php', {

            method: 'POST',

            headers: {
                'Content-Type':'application/json'
            },

            body: JSON.stringify({
                bill_slug: slug,
                comment: comment
            })

        });

        const result = await res.json();

        if (!res.ok || result.error)
            throw new Error(result.error || 'Unable to post.');

        textarea.value = '';

        showToast('✓ Comment posted successfully.');

        loadComments(slug);

    } catch(err){

        if(err.message.toLowerCase().includes('log')){
            showToast('Please log in before commenting.',true);
        }else{
            showToast(err.message,true);
        }

        console.error(err);

    }

    btn.disabled = false;
    btn.textContent = 'Post Comment';

}

function escapeHtml(text){

    const div=document.createElement('div');

    div.textContent=text;

    return div.innerHTML;

}


