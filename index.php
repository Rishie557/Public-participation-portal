<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sauti ya Wananchi — Kenya Civic Democracy Platform</title>
  <meta name="description" content="A citizen-driven platform to vote on tax policies before they become law, track public spending, and report corruption." />
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
      <a href="login.html" class="nav-cta">Sign In</a>
          <a href="login.html?tab=register" class="nav-cta nav-cta-outline">Register</a> 
    
  </div>
</nav>


<section id="hero">
  <div class="alert-bar">
    <span class="alert-bar-badge">Active</span>
    <span class="alert-bar-text">Bills 2026 — public participation is open.</span>
  </div>
  <div class="hero-content">
    <div class="hero-tag">Kenya Civic Democracy Platform</div>
    <h1 class="hero-title">
      Your <span class="accent">Voice.</span><br>
      Your <span class="red-accent">Kenya.</span>
    </h1>
    <p class="hero-sub">A citizen-driven platform for public participation, transparency, and accountability.</p>
   
  <div class="hero-stats">
  <div class="hero-stat-group">
    <div>
      <div class="hero-stat-num hero-stat-primary" id="stat-votes">—</div>
      <div class="hero-stat-label">Total votes cast</div>
    </div>
  </div>

  <div class="hero-stat-divider"></div>

  <div class="hero-stat-group">
    <div>
      <div class="hero-stat-num red" id="stat-bills">—</div>
      <div class="hero-stat-label">Active policy bills</div>
    </div>
    <div>
      <div class="hero-stat-num" style="color:var(--green-light);" id="stat-passed">—</div>
      <div class="hero-stat-label">Bills signed into law</div>
    </div>
  </div>
</div>
  </div>
</section>

<footer>
  <div class="footer-inner">
    <div>
      <div class="footer-logo">Sauti ya <span>Wananchi</span></div>
      <div class="footer-tagline">Built on three pillars: citizen voice, official accountability, and independent oversight.</div>
    </div>
    <div class="footer-links">
      <div class="footer-links-title">Get Started</div>
      <a href="login.html">Sign In</a>
      <a href="login.html?tab=register">Register</a>
      <a href="admin/admin.html">Admin Sign In</a>
    </div>
  </div>
</footer>

<div class="toast" id="toast"></div>
<script src="script.js"></script>
<script>
  fetch('get_homepage_stats.php')
    .then(res => res.json())
    .then(data => {
      document.getElementById('stat-votes').textContent = data.total_votes.toLocaleString();
      document.getElementById('stat-bills').textContent = data.active_bills.toLocaleString();
      document.getElementById('stat-passed').textContent = data.passed_bills.toLocaleString();
    })
    .catch(() => {
      document.getElementById('stat-votes').textContent = '0';
      document.getElementById('stat-bills').textContent = '0';
      document.getElementById('stat-passed').textContent = '0';
    });
</script>
</body>
</html>