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
      <div>
        <div class="hero-stat-num">847K</div>
        <div class="hero-stat-label">Citizens voted this month</div>
      </div>
      <div>
        <div class="hero-stat-num red">23</div>
        <div class="hero-stat-label">Active policy votes open</div>
      </div>
    </div>
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
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script src="script.js"></script>
</body>
</html>