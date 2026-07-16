<?php require 'auth/auth_gate_official.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Official Dashboard — Sauti ya Wananchi</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">SW</div>
    <span class="nav-brand">Sauti ya <span>Wananchi</span></span>
  </div>
  <div class="nav-links">
    <a href="Votes.php">Vote 2026</a>
    <a href="official_dashboard.php" class="nav-cta">Official Dashboard</a>
    <div class="nav-account">
      <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</span>
      <a href="auth/logout.php" class="nav-logout">Log out</a>
    </div>
  </div>
</nav>

<section id="vote2026" style="margin-top:60px;">
  <div class="section-wrapper">
    <div class="section-eyebrow">Official Access</div>
    <h2 class="section-title">Bill Results &amp; Docket</h2>
    <p class="section-sub">You can view results for every bill. Bills in your docket also let you post an official response and moderate citizen comments.</p>

    <button class="btn-vote btn-vote-yes" id="propose-new-bill-toggle" style="margin-bottom:1rem;">➕ Propose New Bill</button>
    <div class="comments-section" id="propose-new-bill-panel" style="display:none;"></div>

    <div class="vote-grid" id="official-bills-grid"></div>
  </div>
</section>

<div class="toast" id="toast"></div>
<script src="official_dashboard.js"></script>
</body>
</html>