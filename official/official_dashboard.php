<?php require __DIR__ . '/../auth/auth_gate_official.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Official Dashboard — Sauti ya Wananchi</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css" />
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">SW</div>
    <span class="nav-brand">Sauti ya <span>Wananchi</span></span>
  </div>
  <div class="nav-links">
    <a href="../Votes.php">Vote 2026</a>
    <a href="official_dashboard.php" class="nav-cta">Official Dashboard</a>
    <div class="nav-account">
      <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
      <a href="../auth/logout.php" class="nav-logout">Log out</a>
    </div>
  </div>
</nav>

<section id="vote2026" style="margin-top:60px;">
  <div class="section-wrapper">
    <div class="section-eyebrow">Official Access</div>
    <h2 class="section-title">Bill Results &amp; Docket</h2>
    <p class="section-sub">You can view results for every bill. Bills in your docket also let you post an official response and moderate citizen comments.</p>

    <div class="official-tabs">
      <button class="official-tab active" data-tab="bills" onclick="switchOfficialTab('bills')">📋 Bills &amp; Docket</button>
      <button class="official-tab" data-tab="propose" onclick="switchOfficialTab('propose')">➕ Propose New Bill</button>
      <button class="official-tab" data-tab="reviews" onclick="switchOfficialTab('reviews')">🕒 Pending Reviews</button>
    </div>

    <div class="official-tab-panel active" id="tab-panel-bills">
      <div class="vote-grid" id="official-bills-grid"></div>
    </div>

    <div class="official-tab-panel" id="tab-panel-propose"></div>

    <div class="official-tab-panel" id="tab-panel-reviews"></div>

  </div>
</section>

<div class="toast" id="toast"></div>
<script src="official_dashboard.js"></script>
</body>
</html>