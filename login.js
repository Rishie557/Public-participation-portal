function switchTab(which){
  document.getElementById('tab-signin').classList.toggle('active', which==='signin');
  document.getElementById('tab-register').classList.toggle('active', which==='register');
  document.getElementById('panel-signin').classList.toggle('active', which==='signin');
  document.getElementById('panel-register').classList.toggle('active', which==='register');
}
// Positions available at each government level.
const NATIONAL_POSITIONS = [
  'President',
  'Deputy President',
  'Cabinet Secretary',
  'Member of Parliament (MP)',
  'Senator',
  'Clerk (National Assembly/Senate)'
];
const COUNTY_POSITIONS = [
  'Governor',
  'Deputy Governor',
  'County Executive Committee Member (CECM)',
  'Member of County Assembly (MCA)',
  'County Clerk'
];
// Positions that hold a single ministry/docket and must pick one.
const MINISTRY_POSITIONS = ['Cabinet Secretary', 'County Executive Committee Member (CECM)'];
// Everyone else (chief executives, legislators, clerks) oversees/serves all bills at their level.
const ALL_DOCKET_VALUE = 'All';

function onRoleChange(){
  const role = document.querySelector('input[name="reg-role"]:checked').value;
  const showOfficial = role === 'official';
  document.getElementById('official-id-field').style.display = showOfficial ? 'block' : 'none';
  document.getElementById('official-level-field').style.display = showOfficial ? 'block' : 'none';
  document.getElementById('official-position-field').style.display = showOfficial ? 'block' : 'none';
  document.getElementById('official-dept-field').style.display = 'none';
  document.getElementById('gov-note').classList.toggle('show', showOfficial);

  // Reset the cascade whenever role changes
  document.getElementById('reg-gov-level').value = '';
  resetPositionField();
  document.getElementById('reg-department').value = '';
}

function resetPositionField(){
  const positionSelect = document.getElementById('reg-position');
  positionSelect.innerHTML = '<option value="">Select government level first</option>';
  positionSelect.disabled = true;
}

function onGovLevelChange(){
  const level = document.getElementById('reg-gov-level').value;
  const positionSelect = document.getElementById('reg-position');
  const deptField = document.getElementById('official-dept-field');
  const deptSelect = document.getElementById('reg-department');

  const positions = level === 'national' ? NATIONAL_POSITIONS
                   : level === 'county'   ? COUNTY_POSITIONS
                   : [];

  positionSelect.innerHTML = '<option value="">Select your position</option>' +
    positions.map(p => `<option value="${p}">${p}</option>`).join('');
  positionSelect.disabled = positions.length === 0;

  // Changing level invalidates whatever ministry/docket state was set for the old level
  deptField.style.display = 'none';
  deptSelect.value = '';
}

function onPositionChange(){
  const position = document.getElementById('reg-position').value;
  const deptField = document.getElementById('official-dept-field');
  const deptSelect = document.getElementById('reg-department');

  if (MINISTRY_POSITIONS.includes(position)) {
    // Cabinet Secretary / CECM: holds one ministry, must pick it
    deptField.style.display = 'block';
    deptSelect.value = '';
  } else if (position) {
    // Chief executives, legislators, clerks: oversee/serve all dockets at their level
    deptField.style.display = 'none';
    deptSelect.value = ALL_DOCKET_VALUE;
  } else {
    deptField.style.display = 'none';
    deptSelect.value = '';
  }
}
function togglePass(id, btn){
  const input = document.getElementById(id);
  const hidden = input.type === 'password';
  input.type = hidden ? 'text' : 'password';
  btn.textContent = hidden ? 'HIDE' : 'SHOW';
}
function showToast(msg, isError){
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.toggle('error', !!isError);
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 3500);
}

// ── Kenya counties & constituencies (2012 IEBC boundaries) ──
const KENYA_COUNTIES = {
  "Mombasa": ["Changamwe","Jomvu","Kisauni","Nyali","Likoni","Mvita"],
  "Kwale": ["Msambweni","Lunga Lunga","Matuga","Kinango"],
  "Kilifi": ["Kilifi North","Kilifi South","Kaloleni","Rabai","Ganze","Malindi","Magarini"],
  "Tana River": ["Garsen","Galole","Bura"],
  "Lamu": ["Lamu East","Lamu West"],
  "Taita Taveta": ["Taveta","Wundanyi","Mwatate","Voi"],
  "Garissa": ["Garissa Township","Balambala","Lagdera","Dadaab","Fafi","Ijara"],
  "Wajir": ["Wajir North","Wajir East","Tarbaj","Wajir West","Eldas","Wajir South"],
  "Mandera": ["Mandera West","Banissa","Mandera North","Mandera South","Mandera East","Lafey"],
  "Marsabit": ["Moyale","North Horr","Saku","Laisamis"],
  "Isiolo": ["Isiolo North","Isiolo South"],
  "Meru": ["Igembe South","Igembe Central","Igembe North","Tigania West","Tigania East","North Imenti","Buuri","Central Imenti","South Imenti"],
  "Tharaka Nithi": ["Maara","Chuka/Igambang'ombe","Tharaka"],
  "Embu": ["Manyatta","Runyenjes","Mbeere South","Mbeere North"],
  "Kitui": ["Mwingi North","Mwingi West","Mwingi Central","Kitui West","Kitui Rural","Kitui Central","Kitui East","Kitui South"],
  "Machakos": ["Masinga","Yatta","Kangundo","Matungulu","Kathiani","Mavoko","Machakos Town","Mwala"],
  "Makueni": ["Mbooni","Kilome","Kaiti","Kibwezi West","Kibwezi East","Makueni"],
  "Nyandarua": ["Kinangop","Kipipiri","Ol Kalou","Ol Jorok","Ndaragwa"],
  "Nyeri": ["Tetu","Kieni","Mathira","Othaya","Mukurweini","Nyeri Town"],
  "Kirinyaga": ["Mwea","Gichugu","Ndia","Kirinyaga Central"],
  "Murang'a": ["Kangema","Mathioya","Kiharu","Kigumo","Maragua","Kandara","Gatanga"],
  "Kiambu": ["Gatundu South","Gatundu North","Juja","Thika Town","Ruiru","Githunguri","Kiambu","Kiambaa","Kabete","Kikuyu","Limuru","Lari"],
  "Turkana": ["Turkana North","Turkana West","Turkana Central","Loima","Turkana South","Turkana East"],
  "West Pokot": ["Kapenguria","Sigor","Kacheliba","Pokot South"],
  "Samburu": ["Samburu West","Samburu North","Samburu East"],
  "Trans Nzoia": ["Kwanza","Endebess","Saboti","Kiminini","Cherangany"],
  "Uasin Gishu": ["Soy","Turbo","Moiben","Ainabkoi","Kapseret","Kesses"],
  "Elgeyo Marakwet": ["Marakwet East","Marakwet West","Keiyo North","Keiyo South"],
  "Nandi": ["Tinderet","Aldai","Nandi Hills","Chesumei","Emgwen","Mosop"],
  "Baringo": ["Tiaty","Baringo North","Baringo Central","Baringo South","Mogotio","Eldama Ravine"],
  "Laikipia": ["Laikipia West","Laikipia East","Laikipia North"],
  "Nakuru": ["Molo","Njoro","Naivasha","Gilgil","Kuresoi South","Kuresoi North","Subukia","Rongai","Bahati","Nakuru Town West","Nakuru Town East"],
  "Narok": ["Kilgoris","Emurua Dikirr","Narok North","Narok East","Narok South","Narok West"],
  "Kajiado": ["Kajiado North","Kajiado Central","Kajiado East","Kajiado West","Kajiado South"],
  "Kericho": ["Kipkelion East","Kipkelion West","Ainamoi","Bureti","Belgut","Sigowet/Soin"],
  "Bomet": ["Sotik","Chepalungu","Bomet East","Bomet Central","Konoin"],
  "Kakamega": ["Lugari","Likuyani","Malava","Lurambi","Navakholo","Mumias West","Mumias East","Matungu","Butere","Khwisero","Shinyalu","Ikolomani"],
  "Vihiga": ["Vihiga","Sabatia","Hamisi","Luanda","Emuhaya"],
  "Bungoma": ["Mt Elgon","Sirisia","Kabuchai","Bumula","Kanduyi","Webuye East","Webuye West","Kimilili","Tongaren"],
  "Busia": ["Teso North","Teso South","Nambale","Matayos","Butula","Funyula","Budalangi"],
  "Siaya": ["Ugenya","Ugunja","Alego Usonga","Gem","Bondo","Rarieda"],
  "Kisumu": ["Kisumu East","Kisumu West","Kisumu Central","Seme","Nyando","Muhoroni","Nyakach"],
  "Homa Bay": ["Kasipul","Kabondo Kasipul","Karachuonyo","Rangwe","Homa Bay Town","Ndhiwa","Suba North","Suba South"],
  "Migori": ["Rongo","Awendo","Suna East","Suna West","Uriri","Nyatike","Kuria West","Kuria East"],
  "Kisii": ["Bonchari","South Mugirango","Bomachoge Borabu","Bobasi","Bomachoge Chache","Nyaribari Masaba","Nyaribari Chache","Kitutu Chache North","Kitutu Chache South"],
  "Nyamira": ["Kitutu Masaba","West Mugirango","North Mugirango","Borabu"],
  "Nairobi": ["Westlands","Dagoretti North","Dagoretti South","Langata","Kibra","Roysambu","Kasarani","Ruaraka","Embakasi South","Embakasi North","Embakasi Central","Embakasi East","Embakasi West","Makadara","Kamukunji","Starehe","Mathare"]
};

function initCountyDropdowns(){
  const countyList = document.getElementById('county-list');
  countyList.innerHTML = Object.keys(KENYA_COUNTIES)
    .sort()
    .map(c => `<option value="${c}">`)
    .join('');
}

function onCountyInput(){
  const countyInput = document.getElementById('reg-county');
  const constInput = document.getElementById('reg-constituency');
  const constList = document.getElementById('constituency-list');
  const county = countyInput.value.trim();

  const constituencies = KENYA_COUNTIES[county];

  if (constituencies) {
    constList.innerHTML = constituencies.map(c => `<option value="${c}">`).join('');
    constInput.disabled = false;
    constInput.placeholder = 'Type to search…';
    if (!constituencies.includes(constInput.value.trim())) {
      constInput.value = '';
    }
  } else {
    constList.innerHTML = '';
    constInput.disabled = true;
    constInput.value = '';
    constInput.placeholder = 'Select a valid county first';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initCountyDropdowns();
  document.getElementById('reg-county').addEventListener('input', onCountyInput);
});

// Wired to login.php / register.php (MySQL via mysqli on XAMPP)

async function handleSignIn(e){
  e.preventDefault();
  const role = document.querySelector('input[name="signin-role"]:checked').value;
  const identifier = document.getElementById('signin-id').value.trim();
  const password = document.getElementById('signin-pass').value;
  const errorBox = document.getElementById('signin-error');
  errorBox.classList.remove('show');

  try{
    const res = await fetch('auth/login.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ identifier, password, role })
    });
    const result = await res.json();
    if(!res.ok) throw new Error(result.error || 'Login failed');

    document.getElementById('form-signin').reset();
    showToast('Signed in successfully. Redirecting…');
    setTimeout(()=>{ window.location.href = result.redirect; }, 800);
  }catch(err){
    errorBox.textContent = err.message;
    errorBox.classList.add('show');
  }
  return false;
}

async function handleRegister(e){
  e.preventDefault();
  const role = document.querySelector('input[name="reg-role"]:checked').value;
  const office_id = document.getElementById('reg-office-id').value.trim();
  const position_title = document.getElementById('reg-position').value.trim();
  const government_level = document.getElementById('reg-gov-level').value;
  const office_department = document.getElementById('reg-department').value.trim();

  const countyValue = document.getElementById('reg-county').value.trim();
  if (!KENYA_COUNTIES[countyValue]) {
    showToast('Please select a valid county from the list.', true);
    return false;
  }

  const constituencyValue = document.getElementById('reg-constituency').value.trim();
  if (!KENYA_COUNTIES[countyValue].includes(constituencyValue)) {
    showToast('Please select a valid constituency from the list.', true);
    return false;
  }

  if(role === 'official' && (!office_id || !position_title || !government_level || !office_department)){
    showToast('Please fill in your office ID, position, government level, and department.', true);
    return false;
  }

  const payload = {
    full_name: document.getElementById('reg-name').value.trim(),
    national_id_number: document.getElementById('reg-national-id').value.trim(),
    phone: document.getElementById('reg-phone').value.trim() || null,
    email: document.getElementById('reg-email').value.trim(),
    password: document.getElementById('reg-pass').value,
    role,
    county_name: countyValue,
    constituency_name: constituencyValue,
    office_id_number: office_id,
    position_title: position_title || null,
    government_level: government_level || null,
    office_department: office_department || null
  };

  try{
    const res = await fetch('auth/register.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const result = await res.json();
    if(!res.ok) throw new Error(result.error || 'Registration failed');

    document.getElementById('form-register').reset();
    showToast(result.message);
    setTimeout(()=> switchTab('signin'), 1500);
  }catch(err){
    showToast(err.message, true);
  }
  return false;
}
window.addEventListener('pageshow', function(event){
  if (event.persisted) {
    // Page was restored from browser cache (e.g. back button) — clear passwords only
    const signinPass = document.getElementById('signin-pass');
    const regPass = document.getElementById('reg-pass');

    signinPass.value = '';
    regPass.value = '';

    // also reset the SHOW/HIDE toggle back to hidden, in case it was left open
    signinPass.type = 'password';
    regPass.type = 'password';
    document.querySelectorAll('.toggle-visibility button').forEach(btn => btn.textContent = 'SHOW');
  }
});

// ── Voice picker for text-to-speech ──────────────────────
let selectedVoice = null;
let matchedVoices = [];

const voiceOptions = [
  { match: 'Zira',   label: 'Female',   lang: 'en' },
  { match: 'Mark',   label: 'Male',     lang: 'en' },
  { match: 'Rafiki', label: 'Mwanaume', lang: 'sw' },
  { match: 'Zuri',   label: 'Mwanamke', lang: 'sw' }
];

function loadVoices(){
  const voices = window.speechSynthesis.getVoices();
  const picker = document.getElementById('voice-picker');
  if (voices.length === 0) return; // voices sometimes load asynchronously, especially in Chrome

  const currentLang = kiswahili ? 'sw' : 'en';

  picker.innerHTML = '';
  picker.setAttribute('aria-label', 'Voices / Sauti');

  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Voices / Sauti';
  placeholder.disabled = true;
  placeholder.selected = true;
  picker.appendChild(placeholder);

  matchedVoices = [];
  voiceOptions
    .filter(w => w.lang === currentLang)
    .forEach(w => {
      const voice = voices.find(v => v.name.includes(w.match));
      if (voice) {
        matchedVoices.push({ voice, label: w.label });
        const opt = document.createElement('option');
        opt.value = matchedVoices.length - 1;
        opt.textContent = w.label;
        picker.appendChild(opt);
      }
    });

  picker.style.display = matchedVoices.length > 0 ? 'inline-block' : 'none';
  picker.onchange = () => {
    if (picker.value === '') { selectedVoice = null; return; }
    selectedVoice = matchedVoices[picker.value].voice;
  };
  selectedVoice = matchedVoices[0] ? matchedVoices[0].voice : null;
}

window.speechSynthesis.onvoiceschanged = loadVoices;
loadVoices();

function speakForm(){
  if (!('speechSynthesis' in window)) {
    showToast('Sorry, your browser doesn\'t support text-to-speech.', true);
    return;
  }

  window.speechSynthesis.cancel();

  const isRegisterTab = document.getElementById('panel-register').classList.contains('active');

  const textEn = isRegisterTab
    ? "Register page. Choose whether you are a citizen or a government official. Then enter your full name, national ID number, phone number, optional email, your county, your constituency, and create a password of at least 8 characters."
    : "Sign in page. Choose whether you are a citizen or a government official. Then enter your email or phone number, and your password.";

  const textSw = isRegisterTab
    ? "Ukurasa wa kujiandikisha. Chagua kama wewe ni raia au afisa wa serikali. Kisha jaza jina lako kamili, nambari ya kitambulisho cha taifa, nambari ya simu, barua pepe ikiwa unayo, kaunti yako, jimbo lako, na uweke nywila yenye herufi nane au zaidi."
    : "Ukurasa wa kuingia. Chagua kama wewe ni raia au afisa wa serikali. Kisha jaza barua pepe au nambari ya simu, na nywila yako.";

  const utterance = new SpeechSynthesisUtterance(kiswahili ? textSw : textEn);
  if (selectedVoice) {
    utterance.voice = selectedVoice;
  } else {
    utterance.lang = kiswahili ? 'sw-KE' : 'en-US';
  }
  utterance.rate = 0.95;

  window.speechSynthesis.speak(utterance);
}

let kiswahili = false;
function toggleLang(){
  kiswahili = !kiswahili;
  document.getElementById('btn-lang').classList.toggle('on', kiswahili);
  document.getElementById('btn-lang').textContent = kiswahili ? '🌐 English' : '🌐 Kiswahili';
  loadVoices();
}
function toggleTextSize(){
  document.body.classList.toggle('large-text');
  document.getElementById('btn-text').classList.toggle('on');
}
document.addEventListener('DOMContentLoaded', () => {
  initCountyDropdowns();
  document.getElementById('reg-county').addEventListener('input', onCountyInput);

  const params = new URLSearchParams(window.location.search);
  if (params.get('tab') === 'register') {
    switchTab('register');
  }
});