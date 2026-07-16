<?php require 'auth_gate.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Dashboard — Sauti ya Wananchi</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css" />
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
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">SW</div>
    <span class="nav-brand">Sauti ya <span>Wananchi</span></span>
  </div>
  <div class="nav-links">
    <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="logout.php" class="nav-logout">Log out</a>
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
        <a href="Bills2026.html">2026 BILLS</a>
        <a href="Bills2025.html">2025 BILLS</a>
        <a href="Bills2024.html">2024 BILLS</a>
      </div>
    </div>

    <!-- TRANSPARENCY — click to choose a year -->
    <div class="dash-card dash-card-picker" onclick="toggleYearMenu(event, 'spend-menu')">
      <button class="dash-listen" onclick="event.preventDefault(); event.stopPropagation(); readDashCard(this,'Spending. See how the government spends money.','Matumizi. Ona jinsi serikali inatumia pesa.')">🔊</button>
      <span class="dash-icon">💰</span>
      <div class="dash-label">TRANSPARENCY</div>
      <div class="dash-sub">Matumizi / Track spending</div>
      <div class="dash-year-menu" id="spend-menu" onclick="event.stopPropagation()">
        <a href="Spend2026.html">2026 SPEND</a>
        <a href="Spend2025.html">2025 SPEND</a>
        <a href="Spend2024.html">2024 SPEND</a>
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
<script src="script.js"></script>
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
});
</script>
</body>
</html>