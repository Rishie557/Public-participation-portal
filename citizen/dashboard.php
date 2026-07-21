<?php require __DIR__ . '/../auth/auth_gate.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard — Sauti ya Wananchi</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style.css" />
<style>
  #dash-section{background:var(--black);min-height:100vh;padding:2rem 1.5rem 4rem}
  .dash-welcome{text-align:center;margin:2rem 0 3rem}
  .dash-welcome h1{font-family:'Playfair Display',serif;color:#fff;font-size:32px;margin-bottom:0.5rem}
  .dash-welcome p{color:#999;font-size:15px}
  .dash-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;max-width:1000px;margin:0 auto}
  .dash-card{background:#141412;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:2rem 1.5rem;text-align:center;text-decoration:none;transition:all .2s;position:relative;display:block;cursor:pointer}
  .dash-card:hover{border-color:var(--green-light);transform:translateY(-3px)}
  .dash-icon{font-size:48px;margin-bottom:1rem;display:block}
  .dash-label{font-size:18px;font-weight:700;color:#fff;margin-bottom:0.4rem;font-family:'DM Sans',sans-serif}
  .dash-sub{font-size:13px;color:#999}
  .dash-listen{position:absolute;top:10px;right:10px;background:none;border:1px solid rgba(255,255,255,0.15);color:#aaa;font-size:11px;padding:5px 9px;border-radius:16px;cursor:pointer}
  .dash-listen.speaking{border-color:var(--green-light);color:#fff}
  .accessibility-bar{display:flex;flex-wrap:wrap;justify-content:center;gap:0.6rem 1rem;padding:16px;margin:3rem auto 0;max-width:1000px;background:#0a0a08;border-radius:8px;border:1px solid rgba(255,255,255,0.08)}
  .a11y-btn{background:none;border:1px solid rgba(255,255,255,0.15);color:#aaa;font-size:13px;padding:8px 14px;border-radius:20px;cursor:pointer;font-family:'DM Sans',sans-serif}
  .a11y-btn:hover,.a11y-btn.on{border-color:var(--green-light);color:#fff}
  body.big-text .dash-label{font-size:22px}
  body.big-text .dash-sub{font-size:16px}
  body.big-text .dash-icon{font-size:60px}

  /* ── Year-picker dropdown for BILLS / TRANSPARENCY cards ── */
  .dash-card-picker{position:relative}
  .dash-year-menu{
    display:none;
    position:absolute;
    top:calc(100% + 6px);
    left:50%;
    transform:translateX(-50%);
    background:#1c1c19;
    border:1px solid rgba(255,255,255,0.1);
    border-radius:6px;
    overflow:hidden;
    min-width:140px;
    z-index:20;
    box-shadow:0 8px 24px rgba(0,0,0,0.4);
  }
  .dash-year-menu.open{display:block}
  .dash-year-menu a{
    display:block;
    padding:10px 16px;
    font-size:13px;
    font-weight:600;
    color:#ddd;
    text-decoration:none;
    text-align:left;
    font-family:'DM Sans',sans-serif;
    transition:background .15s;
  }
  .dash-year-menu a:hover{background:rgba(0,153,0,0.15);color:#fff}
  .dash-year-menu a + a{border-top:1px solid rgba(255,255,255,0.06)}
  body.big-text .dash-year-menu a{font-size:16px;padding:12px 18px}

  /* ── Notification bell ── */
  .notif-bell-wrap{position:relative}
  .notif-bell-btn{
    background:none;border:none;font-size:20px;cursor:pointer;
    position:relative;color:#ddd;padding:4px 8px;line-height:1;
  }
  .notif-badge{
    position:absolute;top:-2px;right:0;background:#c0392b;color:#fff;
    font-size:10px;font-weight:700;border-radius:10px;padding:1px 5px;
    min-width:16px;text-align:center;line-height:1.4;
  }
  .notif-panel{
    display:none;position:absolute;top:calc(100% + 10px);right:0;
    width:320px;max-height:420px;overflow-y:auto;background:#1c1c19;
    border:1px solid rgba(255,255,255,0.1);border-radius:8px;
    box-shadow:0 8px 24px rgba(0,0,0,0.4);z-index:50;
  }
  .notif-panel.open{display:block}
  .notif-panel-header{
    padding:12px 16px;font-weight:700;color:#fff;font-size:14px;
    border-bottom:1px solid rgba(255,255,255,0.08);
    font-family:'DM Sans',sans-serif;
  }
  .notif-item{
    padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.06);
    font-size:13px;color:#ccc;
  }
  .notif-item:last-child{border-bottom:none}
  .notif-item.unread{background:rgba(0,153,0,0.08)}
  .notif-item-type{font-weight:600;font-size:12px;margin-bottom:4px}
  .notif-item-type.deleted{color:#e06666}
  .notif-item-type.appreciated{color:var(--green-light,#4caf50)}
  .notif-item-time{font-size:11px;color:#888;margin-top:4px}
  .notif-empty{padding:20px;text-align:center;color:#888;font-size:13px}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">SW</div>
    <span class="nav-brand">Sauti ya <span>Wananchi</span></span>
  </div>
  <div class="nav-links">
    <div class="notif-bell-wrap">
      <button class="notif-bell-btn" id="notif-bell-btn" onclick="toggleNotifPanel()" aria-label="Notifications">
        🔔<span class="notif-badge" id="notif-badge" style="display:none">0</span>
      </button>
      <div class="notif-panel" id="notif-panel">
        <div class="notif-panel-header">Notifications</div>
        <div class="notif-panel-list" id="notif-panel-list"><p class="notif-empty">Loading…</p></div>
      </div>
    </div>
    <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../auth/logout.php" class="nav-logout">Log out</a>
  </div>
</nav>

<section id="dash-section">
  <div class="dash-welcome">
    <h1>Karibu, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?></h1>
    <p>Choose what you want to do / Chagua unachotaka kufanya</p>
  </div>

  <div class="dash-grid">

    <a href="Votes.php" class="dash-card">
      <button class="dash-listen" onclick="event.preventDefault(); readDashCard(this,'Vote. Vote on new laws before they pass.','Piga kura. Piga kura kwa sheria mpya kabla hazijapitishwa.')">🔊</button>
      <span class="dash-icon">🗳️</span>
      <div class="dash-label">VOTE</div>
      <div class="dash-sub">Piga Kura / Vote on bills</div>
    </a>

    <!-- BILLS — click to choose a year -->
    <div class="dash-card dash-card-picker" onclick="toggleYearMenu(event, 'bills-menu')">
      <button class="dash-listen" onclick="event.preventDefault(); event.stopPropagation(); readDashCard(this,'Bills. Read what new laws say.','Miswada. Soma sheria mpya zinasema nini.')">🔊</button>
      <span class="dash-icon">📜</span>
      <div class="dash-label">BILLS</div>
      <div class="dash-sub">Miswada / Read new laws</div>
      <div class="dash-year-menu" id="bills-menu" onclick="event.stopPropagation()">
        <a href="bills/Bills2026.php">2026 BILLS</a>
        <a href="bills/Bills2025.html">2025 BILLS</a>
        <a href="bills/Bills2024.html">2024 BILLS</a>
      </div>
    </div>

    <!-- TRANSPARENCY — click to choose a year -->
    <div class="dash-card dash-card-picker" onclick="toggleYearMenu(event, 'spend-menu')">
      <button class="dash-listen" onclick="event.preventDefault(); event.stopPropagation(); readDashCard(this,'Spending. See how the government spends money.','Matumizi. Ona jinsi serikali inatumia pesa.')">🔊</button>
      <span class="dash-icon">💰</span>
      <div class="dash-label">TRANSPARENCY</div>
      <div class="dash-sub">Matumizi / Track spending</div>
      <div class="dash-year-menu" id="spend-menu" onclick="event.stopPropagation()">
        <a href="spend/Spend2026.php">2026 SPEND</a>
        <a href="spend/Spend2025.html">2025 SPEND</a>
        <a href="spend/Spend2024.html">2024 SPEND</a>
      </div>
    </div>

    <a href="comments.php" class="dash-card">
      <button class="dash-listen" onclick="event.preventDefault(); readDashCard(this,'Comments. Share your opinion on bills.','Maoni. Toa maoni yako kuhusu miswada.')">🔊</button>
      <span class="dash-icon">📢</span>
      <div class="dash-label">COMMENTS</div>
      <div class="dash-sub">Maoni yako../Share your opinions...</div>
    </a>

  </div>

  <div class="accessibility-bar">
    <button class="a11y-btn" id="btn-lang" onclick="toggleDashLang()">🌐 Kiswahili</button>
    <button class="a11y-btn" id="btn-bigtext" onclick="toggleBigText()">A+ Larger Text</button>
    <select class="a11y-btn" id="voice-picker" style="display:none"></select>
</div>
</section>

<div class="toast" id="toast"></div>
<script src="../script.js"></script>
<script>
let dashKiswahili = false;
let dashSelectedVoice = null;
let dashMatchedVoices = [];

const dashVoiceOptions = [
  { match: 'Zira',   label: 'Female',   lang: 'en' },
  { match: 'Mark',   label: 'Male',     lang: 'en' },
  { match: 'Rafiki', label: 'Mwanaume', lang: 'sw' },
  { match: 'Zuri',   label: 'Mwanamke', lang: 'sw' }
];

function dashLoadVoices(){
  const voices = window.speechSynthesis.getVoices();
  const picker = document.getElementById('voice-picker');
  if (!picker) return;
  if (voices.length === 0) return;

  const currentLang = dashKiswahili ? 'sw' : 'en';

  picker.innerHTML = '';
  picker.setAttribute('aria-label', 'Voices / Sauti');

  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Voices / Sauti';
  placeholder.disabled = true;
  placeholder.selected = true;
  picker.appendChild(placeholder);

  dashMatchedVoices = [];
  dashVoiceOptions
    .filter(w => w.lang === currentLang)
    .forEach(w => {
      const voice = voices.find(v => v.name.includes(w.match));
      if (voice) {
        dashMatchedVoices.push({ voice, label: w.label });
        const opt = document.createElement('option');
        opt.value = dashMatchedVoices.length - 1;
        opt.textContent = w.label;
        picker.appendChild(opt);
      }
    });

  picker.style.display = dashMatchedVoices.length > 0 ? 'inline-block' : 'none';
  picker.onchange = () => {
    if (picker.value === '') { dashSelectedVoice = null; return; }
    dashSelectedVoice = dashMatchedVoices[picker.value].voice;
  };
  dashSelectedVoice = dashMatchedVoices[0] ? dashMatchedVoices[0].voice : null;
}

window.speechSynthesis.onvoiceschanged = dashLoadVoices;
dashLoadVoices();

function toggleDashLang(){
  dashKiswahili = !dashKiswahili;
  document.getElementById('btn-lang').classList.toggle('on', dashKiswahili);
  document.getElementById('btn-lang').textContent = dashKiswahili ? '🌐 English' : '🌐 Kiswahili';
  dashLoadVoices();
}

let dashCurrentUtterance = null;

function readDashCard(btn, textEn, textSw){
  if (!('speechSynthesis' in window)) { showToast('Read-aloud not supported on this browser.', true); return; }

  const wasSpeaking = btn.classList.contains('speaking');
  speechSynthesis.cancel();
  document.querySelectorAll('.dash-listen.speaking').forEach(b => b.classList.remove('speaking'));
  if (wasSpeaking) { dashCurrentUtterance = null; return; }

  setTimeout(() => {
    dashCurrentUtterance = new SpeechSynthesisUtterance(dashKiswahili ? textSw : textEn);
    if (dashSelectedVoice) {
      dashCurrentUtterance.voice = dashSelectedVoice;
    } else {
      dashCurrentUtterance.lang = dashKiswahili ? 'sw-KE' : 'en-US';
    }
    dashCurrentUtterance.onend = () => btn.classList.remove('speaking');
    dashCurrentUtterance.onerror = (e) => {
      console.error('Speech error:', e);
      btn.classList.remove('speaking');
    };
    btn.classList.add('speaking');
    speechSynthesis.speak(dashCurrentUtterance);
  }, 50);
}

function toggleYearMenu(event, menuId){
  event.preventDefault();
  const menu = document.getElementById(menuId);
  const isOpen = menu.classList.contains('open');
  document.querySelectorAll('.dash-year-menu.open').forEach(m => m.classList.remove('open'));
  if (!isOpen) menu.classList.add('open');
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.dash-card-picker')) {
    document.querySelectorAll('.dash-year-menu.open').forEach(m => m.classList.remove('open'));
  }
  if (!e.target.closest('.notif-bell-wrap')) {
    document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
  }
});

// ── Notifications ──────────────────────────────────────────
function escapeNotif(str){
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function updateNotifBadge(count){
  const badge = document.getElementById('notif-badge');
  if (!badge) return;
  if (count > 0) {
    badge.style.display = 'inline-block';
    badge.textContent = count > 9 ? '9+' : count;
  } else {
    badge.style.display = 'none';
  }
}

async function loadNotifBadge(){
  try {
    const res = await fetch('get_notifications.php');
    if (!res.ok) return;
    const data = await res.json();
    updateNotifBadge(data.unread_count || 0);
  } catch (err) {
    console.error(err);
  }
}

function renderNotifItem(n){
  const isDeleted = n.type === 'comment_deleted';
  const label = isDeleted ? '🗑️ Comment removed' : '👍 Marked as useful';
  const typeClass = isDeleted ? 'deleted' : 'appreciated';
  const time = new Date(n.created_at).toLocaleString('en-KE', {
    day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
  });
  return `
    <div class="notif-item ${n.is_read ? '' : 'unread'}">
      <div class="notif-item-type ${typeClass}">${label}</div>
      <div>${escapeNotif(n.message)}</div>
      <div class="notif-item-time">${time}</div>
    </div>
  `;
}

async function loadNotifList(){
  const list = document.getElementById('notif-panel-list');
  list.innerHTML = '<p class="notif-empty">Loading…</p>';
  try {
    const res = await fetch('get_notifications.php');
    const data = await res.json();
    const notifications = data.notifications || [];

    list.innerHTML = notifications.length
      ? notifications.map(renderNotifItem).join('')
      : '<p class="notif-empty">No notifications yet.</p>';

    if ((data.unread_count || 0) > 0) {
      fetch('mark_notifications_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      });
      updateNotifBadge(0);
    }
  } catch (err) {
    list.innerHTML = '<p class="notif-empty">Could not load notifications.</p>';
    console.error(err);
  }
}

function toggleNotifPanel(){
  const panel = document.getElementById('notif-panel');
  const isOpen = panel.classList.contains('open');
  document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
  if (isOpen) return;
  panel.classList.add('open');
  loadNotifList();
}

loadNotifBadge();
</script>
</body>
</html>