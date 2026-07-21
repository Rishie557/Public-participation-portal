<?php require __DIR__ . '/../auth/auth_gate.php'; ?>
<?php
require __DIR__ . '/../auth/auth_gate.php';
require __DIR__ . '/../config/db_connect.php';

// These 13 slugs already have hand-written cards below. Any other active
// bill in the `bills` table (e.g. one an official proposed and an admin
// approved) gets an auto-generated card in "Recently Added" instead.
$curatedSlugs = [
    'finance-bill-2026', 'appropriation-bill-2026', 'supp-approp-2026',
    'division-revenue-2026', 'county-alloc-2026', 'infra-fund-2026',
    'food-feed-safety', 'plant-protection', 'forest-conservation',
    'competition-amendment', 'procurement-amendment', 'culture-bill',
    'health-amendment',
];

$newBills = [];
$result = $conn->query(
    "SELECT slug, title, bill_status, group_label, document_path
     FROM bills
     WHERE status = 'active'
     ORDER BY id"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['slug'], $curatedSlugs, true)) {
            $newBills[] = $row;
        }
    }
}
$conn->close();

function voteBadgeClass($status) {
    $s = strtolower($status);
    if (strpos($s, 'sign') !== false || strpos($s, 'passed') !== false || strpos($s, 'law') !== false) {
        return ['badge-closed', '✅'];
    }
    return ['badge-open', '🕒'];
}
?>
<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vote 2026 — Sauti ya Wananchi</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css" />
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">SW</div>
    <span class="nav-brand">Sauti ya <span>Wananchi</span></span>
  </div>
    <div class="nav-account">
      <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
      <a href="../auth/logout.php" class="nav-logout">Log out</a>
    </div>
  </div>
</nav>

<div class="accessibility-bar" role="region" aria-label="Accessibility options">
  <button class="a11y-btn" id="btn-readaloud" onclick="toggleReadAloudMode()">🔊 Read Aloud Mode</button>
  <select id="voice-picker" onchange="setVoice(this.value)" aria-label="Choose a voice"></select>
  <button class="a11y-btn" id="btn-bigtext" onclick="toggleBigText()">🔎 Larger Text</button>
</div>

<section id="vote2026">
  <div class="section-wrapper">
    <div class="section-eyebrow">Citizen Voting — 2026</div>
    <h2 class="section-title">Active Policy Votes</h2>
    <p class="section-sub">Cast your vote on Kenya's 2026 legislation. Bills already signed into law are marked — your vote still forms part of the public record. Tap 🔊 <strong>Listen</strong> on any bill to have it read to you. Share your thoughts below, or see everyone's comments on the <a href="comments.php" style="color:var(--green);font-weight:600;">Comments page</a>.</p>

    <!-- ── BUDGET & APPROPRIATIONS ── -->
    <div class="vote-section-group">
      <div class="vote-group-label">Budget &amp; Appropriations</div>
      <div class="vote-grid">

        <!-- FINANCE BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-closed">✅ Signed into Law</div>
              <div class="vote-card-title">Finance Bill, 2026</div>
              <div class="vote-card-desc">National Assembly · Passed 18 Jun 2026 · Signed 23 Jun 2026 · Raises rental income tax to 10%, increases duty-free traveller allowance to KSh 260K, sugar import duty raised to KSh 40/kg, 6-month tax amnesty on penalties. Targets KSh 4.8T budget.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/akn/ke/act/2026/19/eng@2026-06-26" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','finance-bill-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','finance-bill-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="finance-bill-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'finance-bill-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- APPROPRIATION BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-closed">✅ Signed into Law</div>
              <div class="vote-card-title">Appropriation Bill, 2026</div>
              <div class="vote-card-desc">National Assembly · NA Bill No. 36 of 2026 · Signed 23 Jun 2026 · Authorises national government expenditure for FY 2026/27 · Allocates KSh 175.5B for health including KSh 19.1B for Primary Healthcare Fund.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Appropriation+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','appropriation-bill-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','appropriation-bill-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="appropriation-bill-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'appropriation-bill-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- SUPPLEMENTARY APPROPRIATION BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-closed">✅ Signed into Law</div>
              <div class="vote-card-title">Supplementary Appropriation Bill, 2026</div>
              <div class="vote-card-desc">National Assembly · Published Mar 2026 · Signed Apr 2026 · Increased budget by 9.1% · Additional KSh 363.8B to National Government · KSh 60B extra for security, KSh 25B for Affordable Housing, KSh 10B fertiliser subsidy.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Supplementary+Appropriation+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','supp-approp-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','supp-approp-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="supp-approp-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'supp-approp-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- DIVISION OF REVENUE BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Division of Revenue Bill, 2026</div>
              <div class="vote-card-desc">National Assembly &amp; Senate · NA Bill No. 2 of 2026 · Mediation committee report under Senate consideration as of Jun 2026 · Determines revenue sharing between national and county governments for FY 2026/27.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Division+of+Revenue+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','division-revenue-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','division-revenue-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="division-revenue-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'division-revenue-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- COUNTY GOVERNMENTS ADDITIONAL ALLOCATIONS BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">County Governments Additional Allocations Bill, 2026</div>
              <div class="vote-card-desc">Senate · Senate Bill No. 8 of 2026 · Before Senate as of Jun 2026 · Provides framework for conditional and unconditional additional allocations to the 47 county governments for FY 2026/27.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=County+Governments+Additional+Allocations+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','county-alloc-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','county-alloc-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="county-alloc-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'county-alloc-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- ── INFRASTRUCTURE & INVESTMENT ── -->
    <div class="vote-section-group">
      <div class="vote-group-label">Infrastructure &amp; Investment</div>
      <div class="vote-grid">

        <!-- NATIONAL INFRASTRUCTURE FUND BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-closed">✅ Signed into Law</div>
              <div class="vote-card-title">National Infrastructure Fund Bill, 2026</div>
              <div class="vote-card-desc">National Assembly · Passed 5 Mar 2026 · Signed 9 Mar 2026 · Establishes fund to mobilise capital from pension funds &amp; sovereign wealth funds · Finances highways, railways, airports, seaports &amp; electricity infrastructure.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/akn/ke/act/2026/4/eng@2026-03-11" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','infra-fund-2026')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','infra-fund-2026')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="infra-fund-2026">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'infra-fund-2026')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- ── AGRICULTURE, FOOD & ENVIRONMENT ── -->
    <div class="vote-section-group">
      <div class="vote-group-label">Agriculture, Food &amp; Environment</div>
      <div class="vote-grid">

        <!-- FOOD AND FEED SAFETY CONTROL COORDINATION BILL -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-closed">✅ Signed into Law</div>
              <div class="vote-card-title">Food and Feed Safety Control Coordination Bill</div>
              <div class="vote-card-desc">National Assembly &amp; Senate · NA Bill No. 21 of 2023 · Mediated version signed 23 Jun 2026 · Provides coordinated legal framework for food and animal feed safety regulation.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Food+and+Feed+Safety+Control+Coordination+Bill" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','food-feed-safety')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','food-feed-safety')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="food-feed-safety">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'food-feed-safety')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- PLANT PROTECTION BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Plant Protection Bill, 2026</div>
              <div class="vote-card-desc">National Assembly &amp; Senate · NA Bill No. 28 of 2025 · Passed NA · Before Senate as of Jun 2026 · Provides updated legal framework for plant health and protection.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Plant+Protection+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','plant-protection')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','plant-protection')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="plant-protection">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'plant-protection')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- FOREST CONSERVATION AND MANAGEMENT (AMENDMENT) BILL -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Forest Conservation and Management (Amendment) Bill</div>
              <div class="vote-card-desc">National Assembly · NA Bill No. 38 of 2025 · Committee of Whole House stage Jun 2026 · Amends Forest Conservation and Management Act. Outcome pending.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Forest+Conservation+and+Management+Amendment+Bill" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','forest-conservation')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','forest-conservation')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="forest-conservation">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'forest-conservation')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- ── GOVERNANCE & PUBLIC ADMINISTRATION ── -->
    <div class="vote-section-group">
      <div class="vote-group-label">Governance &amp; Public Administration</div>
      <div class="vote-grid">

        <!-- COMPETITION (AMENDMENT) BILL 2026 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Competition (Amendment) Bill, 2026</div>
              <div class="vote-card-desc">National Assembly &amp; Senate · NA Bill No. 4 of 2026 · Passed NA · Before Senate as of Jun 2026 · Updates Kenya's market regulatory framework under the Competition Act.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Competition+Amendment+Bill+2026" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','competition-amendment')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','competition-amendment')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="competition-amendment">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'competition-amendment')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- PUBLIC PROCUREMENT AND ASSET DISPOSAL (AMENDMENT) BILL -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Public Procurement and Asset Disposal (Amendment) Bill</div>
              <div class="vote-card-desc">National Assembly · NA Bill No. 48 of 2024 · Committee of Whole House stage Jun 2026 · Strengthens procurement governance and transparency. Outcome pending.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Public+Procurement+and+Asset+Disposal+Amendment+Bill" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','procurement-amendment')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','procurement-amendment')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="procurement-amendment">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'procurement-amendment')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

        <!-- CULTURE BILL 2024 -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Culture Bill, 2024</div>
              <div class="vote-card-desc">National Assembly &amp; Senate · NA Bill No. 12 of 2024 · Passed NA · Before Senate as of Jun 2026 · Legislative framework for promotion and protection of Kenya's cultural heritage.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Culture+Bill+2024" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','culture-bill')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','culture-bill')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="culture-bill">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'culture-bill')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- ── HEALTH ── -->
    <div class="vote-section-group">
      <div class="vote-group-label">Health</div>
      <div class="vote-grid">

        <!-- HEALTH (AMENDMENT) BILL -->
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge badge-open">🕒 In Progress</div>
              <div class="vote-card-title">Health (Amendment) Bill</div>
              <div class="vote-card-desc">Senate &amp; National Assembly · Senate Bill No. 12 of 2025 · Passed Senate · Before National Assembly Jun 2026 · Aligns Health Act with UHC rollout and Social Health Authority reforms.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <a class="card-read-link" href="https://new.kenyalaw.org/search/?q=Health+Amendment+Bill" target="_blank" rel="noopener">📄 Read the Bill</a>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','health-amendment')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','health-amendment')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="health-amendment">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'health-amendment')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>

      </div>
    </div>
<!-- ── RECENTLY ADDED (auto-generated from official proposals) ── -->
    <?php if (!empty($newBills)): ?>
    <div class="vote-section-group">
      <div class="vote-group-label">Recently Added</div>
      <div class="vote-grid">
        <?php foreach ($newBills as $b):
            [$badgeClass, $badgeIcon] = voteBadgeClass($b['bill_status']);
            $safeSlug   = htmlspecialchars($b['slug'], ENT_QUOTES, 'UTF-8');
            $safeTitle  = htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8');
            $safeStatus = htmlspecialchars($b['bill_status'], ENT_QUOTES, 'UTF-8');
            $safeGroup  = htmlspecialchars($b['group_label'] ?: 'General', ENT_QUOTES, 'UTF-8');
            $docPath    = !empty($b['document_path']) ? htmlspecialchars($b['document_path'], ENT_QUOTES, 'UTF-8') : null;
        ?>
        <div class="vote-card">
          <div class="vote-card-header">
            <div class="vote-card-meta">
              <div class="vote-badge <?= $badgeClass ?>"><?= $badgeIcon ?> <?= $safeStatus ?></div>
              <div class="vote-card-title"><?= $safeTitle ?></div>
              <div class="vote-card-desc"><?= $safeGroup ?> · Added via official docket review.</div>
            </div>
          </div>
          <div class="vote-card-actions">
            <button class="card-listen-btn" onclick="readCard(this)">🔊 Listen</button>
            <?php if ($docPath): ?>
              <a class="card-read-link" href="/sauti/<?= $docPath ?>" target="_blank" rel="noopener">📄 Read the Bill</a>
            <?php endif; ?>
          </div>
          <div class="vote-card-footer">
            <span class="vote-count"><strong>0</strong> votes cast</span>
            <div class="vote-btns">
              <button class="btn-vote btn-vote-yes" onclick="castVote(this,'yes','<?= $safeSlug ?>')">✅ Approve</button>
              <button class="btn-vote btn-vote-no"  onclick="castVote(this,'no','<?= $safeSlug ?>')">❌ Reject</button>
            </div>
          </div>
          <div class="comments-section" data-bill="<?= $safeSlug ?>">
            <h4 class="comments-title">💬 Share Your Thoughts</h4>
            <div class="comment-form">
              <textarea class="comment-input" placeholder="Share your thoughts on this bill..."></textarea>
              <button class="comment-submit-btn" onclick="submitComment(this,'<?= $safeSlug ?>')">Post Comment</button>
            </div>
            <p class="comment-redirect-note">See what others are saying on the <a href="comments.php">Comments page</a>.</p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
  </section>

<footer>
  <div class="footer-inner">
    <div>
      <div class="footer-logo">Sauti ya <span>Wananchi</span></div>
      <div class="footer-tagline">A citizen-driven platform for democratic tax policy, transparency, and accountability.</div>
    </div>
  </div>
</footer>

<div class="toast" id="toast"></div>
<script src="../script.js"></script>
</body>
</html>