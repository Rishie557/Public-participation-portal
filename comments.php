<?php require 'auth/auth_gate.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Citizen Comments — Sauti ya Wananchi</title>
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
    <a href="comments.php" class="nav-cta">Comments</a>
    <div class="nav-account">
      <span class="nav-account-name">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
      <a href="auth/logout.php" class="nav-logout">Log out</a>
    </div>
  </div>
</nav>

<section id="vote2026" style="margin-top:60px;">
  <div class="section-wrapper">
    <div class="section-eyebrow">Public Discussion</div>
    <h2 class="section-title">Citizen Comments on 2026 Bills</h2>
    <p class="section-sub">Share your opinion on any bill. Comments are grouped by category so you can find the discussion you care about.</p>

    <div id="comments-page-root"></div>
  </div>
</section>

<div class="toast" id="toast"></div>
<script src="comments_page.js"></script>
</body>
</html>