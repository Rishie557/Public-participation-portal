<?php
require __DIR__ . '/../../config/db_connect.php';

// These 13 slugs already have hand-written, richly-detailed cards below
// (vote counts, sources, Swahili narration). Any other active bill in the
// `bills` table — e.g. one an official proposed and an admin approved —
// gets a simpler auto-generated card in the "Recently Added" section
// instead, so it isn't silently missing from this page.
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
     ORDER BY group_label, id"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['slug'], $curatedSlugs, true)) {
            $newBills[] = $row;
        }
    }
}
$conn->close();

function billsStageClass($status) {
    $s = strtolower($status);
    if (strpos($s, 'sign') !== false || strpos($s, 'passed') !== false || strpos($s, 'law') !== false) {
        return ['card-passed', 'stage-assent'];
    }
    if (strpos($s, 'reject') !== false || strpos($s, 'fail') !== false) {
        return ['card-rejected', 'stage-rejected'];
    }
    return ['card-pending', 'stage-pending'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kenya Bills 2026 — Legislative Outcomes</title>
  <style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f5f6fa; padding: 24px; color: #1a1a2e; }
    h1 { font-size: 1.4rem; color: #2c3e50; margin-bottom: 4px; }
    .subtitle { color: #666; font-size: 0.9rem; margin-bottom: 20px; }

    .bills-controls { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .bills-play-btn { display: flex; align-items: center; gap: 8px; background: #27ae60; color: #fff; border: none; border-radius: 8px; padding: 12px 20px; font-size: 0.95rem; font-weight: 700; cursor: pointer; }
    .bills-play-btn.playing { background: #1e8449; }
    .bills-voice-group { display: flex; align-items: center; gap: 8px; }
    #bills-voice-picker { font-size: 0.85rem; padding: 8px 12px; border-radius: 20px; border: 1px solid #ccc; background: #fff; color: #333; }
    .bills-lang-btn { font-size: 0.85rem; padding: 8px 14px; border-radius: 20px; border: 1px solid #ccc; background: #fff; color: #333; cursor: pointer; }
    .bills-lang-btn.on { background: #d4efdf; border-color: #27ae60; color: #1e8449; font-weight: 700; }

    .bills-tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 24px; }
    .bills-tab { flex-shrink: 0; font-size: 0.85rem; font-weight: 700; padding: 8px 16px; border-radius: 20px; border: 1px solid #ccc; background: #fff; color: #444; cursor: pointer; white-space: nowrap; }
    .bills-tab:hover { border-color: #27ae60; color: #1e8449; }

    .section-label {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
      color: #999; margin: 28px 0 10px; padding-left: 4px; border-bottom: 1px solid #e0e0e0; padding-bottom: 6px;
      scroll-margin-top: 16px;
    }

    .pw-vote-card {
      background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      padding: 20px 22px; margin-bottom: 18px; border-left: 4px solid #ccc;
    }
    .pw-vote-card.card-passed   { border-left-color: #27ae60; }
    .pw-vote-card.card-rejected { border-left-color: #e74c3c; }
    .pw-vote-card.card-pending  { border-left-color: #f39c12; }

    .pw-vote-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; }
    .pw-vote-title  { font-size: 1rem; font-weight: 700; color: #1a1a2e; }
    .pw-vote-meta   { font-size: 0.78rem; color: #666; margin-top: 3px; }

    .pw-bill-stage { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; white-space: nowrap; }
    .stage-assent   { background: #d4efdf; color: #1e8449; }
    .stage-rejected { background: #fadbd8; color: #c0392b; }
    .stage-pending  { background: #fef9e7; color: #b7950b; }

    .pw-vote-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 12px; gap: 10px; flex-wrap: wrap; }
    .pw-vote-footnote { font-size: 0.73rem; color: #777; flex: 1; }
    .pw-source-link { font-size: 0.72rem; color: #2980b9; text-decoration: none; white-space: nowrap; }
    .pw-source-link:hover { text-decoration: underline; }

    .card-listen-btn {
      background: #d4efdf; border: 1px solid #27ae60; color: #1e8449;
      font-size: 0.8rem; font-weight: 700; padding: 7px 14px; border-radius: 16px; cursor: pointer; margin-bottom: 10px;
    }
    .card-listen-btn.speaking { background: #27ae60; color: #fff; }

    .pw-empty-note { font-size: 0.85rem; color: #999; padding: 8px 4px 20px; }

    @media (max-width: 600px) { .pw-vote-header { flex-direction: column; } }
  </style>
</head>
<body>

<h1>Kenya Parliament — Bills 2026 Legislative Outcomes</h1>
<p class="subtitle">National Assembly &amp; Senate · FY 2026/27 Budget Session</p>

<div class="bills-controls">
  <button class="bills-play-btn" id="bills-play-btn" onclick="toggleBillsPlayAll()">▶ Play All Bills</button>
  <div class="bills-voice-group">
    <button class="bills-lang-btn" id="bills-lang-btn" onclick="toggleBillsLang()">🌐 Kiswahili</button>
    <select id="bills-voice-picker" aria-label="Choose a voice"></select>
  </div>
</div>

<div class="bills-tabs">
  <button class="bills-tab" onclick="scrollToBillsGroup('group-budget')">Budget</button>
  <button class="bills-tab" onclick="scrollToBillsGroup('group-infra')">Infrastructure</button>
  <button class="bills-tab" onclick="scrollToBillsGroup('group-agri')">Agriculture</button>
  <button class="bills-tab" onclick="scrollToBillsGroup('group-gov')">Governance</button>
  <button class="bills-tab" onclick="scrollToBillsGroup('group-health')">Health</button>
  <button class="bills-tab" onclick="scrollToBillsGroup('group-new')">New</button>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-budget">Budget &amp; Appropriations</div>
<!-- ═══════════════════════════════════════ -->

<div class="pw-vote-card card-passed" data-sw="Mswada wa Fedha, 2026. Umepitishwa kuwa sheria. Umeongeza kodi ya kupangisha nyumba kutoka asilimia 7.5 hadi asilimia 10. Umeongeza kiwango cha ununuzi wa bidhaa bila ushuru safarini kutoka shilingi 39,000 hadi shilingi 260,000. Ushuru wa sukari umeongezwa hadi shilingi 40 kwa kilo.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Finance Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · Passed 18 Jun 2026 · Signed into law 23 Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-assent">Passed</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">122 ayes, 40 nays, 0 abstentions · Only 162 of 349 MPs present · 186 absent · Amends Income Tax Act, VAT Act, Excise Duty Act, Tax Procedures Act &amp; Stamp Duty Act · Raises rental income tax from 7.5% to 10% · Increases duty-free traveller allowance from KSh 39K to KSh 260K · Sugar import duty raised from KSh 7.50 to KSh 40/kg · 6-month tax amnesty on penalties &amp; interest · Targets KSh 4.8T budget for FY 2026/27</span>
    <a href="https://www.kenyans.co.ke/news/124557-ruto-signs-2026-finance-bill-law" target="_blank" class="pw-source-link">Source: Kenyans.co.ke ↗</a>
  </div>
</div>

<div class="pw-vote-card card-passed" data-sw="Mswada wa Ugavi, 2026. Umepitishwa kuwa sheria. Unaruhusu matumizi ya fedha za serikali kuu kwa mwaka wa fedha 2026/27, ikiwemo shilingi bilioni 175.5 kwa afya.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Appropriation Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 36 of 2026 · Signed into law 23 Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-assent">Passed</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Authorises national government expenditure for FY 2026/27 · Signed by President Ruto alongside Finance Bill 2026 at State House · Provides legal framework for implementation of the KSh 4.8T budget · Allocates KSh 175.5B for health, with KSh 19.1B for the Primary Healthcare Fund</span>
    <a href="https://citizen.digital/article/ruto-increases-duty-free-allowance-from-ksh39k-to-ksh260k-as-he-assents-to-finance-bill-2026-n385093" target="_blank" class="pw-source-link">Source: Citizen Digital ↗</a>
  </div>
</div>

<div class="pw-vote-card card-passed" data-sw="Mswada wa Nyongeza ya Ugavi, 2026. Umepitishwa kuwa sheria. Umeongeza bajeti kwa asilimia 9.1, ikiwemo shilingi bilioni 60 za ziada kwa usalama na shilingi bilioni 25 kwa nyumba nafuu.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Supplementary Appropriation Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · Published Mar 2026 · Signed into law Apr 2026</div>
    </div>
    <span class="pw-bill-stage stage-assent">Passed</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Increased budget by 9.1% from original KSh 4.301T · Additional KSh 363.8B to National Government; KSh 29.2B to Consolidated Fund Services · Security allocated KSh 60B extra; KSh 25B for Affordable Housing Programme; KSh 17.6B to KRA for enhanced tax collection; KSh 10B fertiliser subsidy · Also signed at the 23 Jun ceremony alongside Finance &amp; Appropriation Bills</span>
    <a href="https://parliament.go.ke/index.php/node/25556" target="_blank" class="pw-source-link">Source: Parliament ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Mgawanyo wa Mapato, 2026. Bado uko kwenye mchakato. Unaamua jinsi mapato yatakavyogawanywa kati ya serikali kuu na serikali za kaunti kwa mwaka wa fedha 2026/27.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Division of Revenue Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly &amp; Senate · NA Bill No. 2 of 2026 · In mediation as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Determines equitable sharing of revenue between National and County Governments for FY 2026/27 · Passed NA Committee of the Whole House · Mediation committee report under consideration by Senate as of late May 2026 · Senate scheduled to vote on mediated version</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-22-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Mgao wa Ziada kwa Serikali za Kaunti, 2026. Bado uko kwenye mchakato. Unatoa mfumo wa mgao wa ziada kwa serikali 47 za kaunti.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">County Governments Additional Allocations Bill, 2026</div>
      <div class="pw-vote-meta">Senate · Senate Bill No. 8 of 2026 · Before Senate as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Provides framework for conditional and unconditional additional allocations to county governments for FY 2026/27 · Before the Senate as of mid-June 2026 · Outcome pending</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-22-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-infra">Infrastructure &amp; Investment</div>
<!-- ═══════════════════════════════════════ -->

<div class="pw-vote-card card-passed" data-sw="Mswada wa Mfuko wa Miundombinu ya Taifa, 2026. Umepitishwa kuwa sheria. Unaanzisha mfuko wa kukusanya fedha kwa ajili ya barabara, reli, viwanja vya ndege na umeme.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">National Infrastructure Fund Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · Published Jan 2026 · Passed Mar 5, 2026 · Signed into law Mar 9, 2026</div>
    </div>
    <span class="pw-bill-stage stage-assent">Passed</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Sponsored by Majority Leader Kimani Ichung'wah · Establishes National Infrastructure Fund to mobilise capital from pension funds, collective investment schemes and sovereign wealth funds · Finances highways, railways, airports, seaports, electricity infrastructure · Two-tier governance: Governing Council + Board of Directors · Proceeds from privatisation among revenue streams · Signed by Ruto at State House 9 Mar 2026</span>
    <a href="https://www.parliament.go.ke/node/25363" target="_blank" class="pw-source-link">Source: Parliament ↗</a>
  </div>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-agri">Agriculture, Food &amp; Environment</div>
<!-- ═══════════════════════════════════════ -->

<div class="pw-vote-card card-passed" data-sw="Mswada wa Uratibu wa Usalama wa Chakula na Malisho. Umepitishwa kuwa sheria. Unatoa mfumo wa kisheria wa kuratibu usalama wa chakula na malisho ya wanyama.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Food and Feed Safety Control Coordination Bill</div>
      <div class="pw-vote-meta">National Assembly &amp; Senate · NA Bill No. 21 of 2023 · Mediated version signed into law 23 Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-assent">Passed</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Long-running bill introduced 2023 · Passed NA 23 Aug 2023 · Senate amendments required mediation · Mediated version approved by Senate 28 Apr 2026 · Signed into law 23 Jun 2026 alongside Finance and Appropriation Bills · Provides a coordinated legal framework for food and animal feed safety regulation</span>
    <a href="https://eastleighvoice.co.ke/news/371322/ruto-signs-finance-bill-2026-into-law-clearing-path-for-sh48-trillion-budget-implementation" target="_blank" class="pw-source-link">Source: Eastleigh Voice ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Ulinzi wa Mimea, 2026. Bado uko kwenye mchakato. Unatoa mfumo mpya wa kisheria wa afya na ulinzi wa mimea.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Plant Protection Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 28 of 2025 · Before Senate as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Passed National Assembly · Transmitted to Senate for consideration · Provides updated legal framework for plant health and protection · Outcome pending as of Jun 2026</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-22-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Marekebisho ya Uhifadhi wa Misitu. Bado uko kwenye mchakato. Unarekebisha sheria ya uhifadhi na usimamizi wa misitu.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Forest Conservation and Management (Amendment) Bill</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 38 of 2025 · Committee of Whole House stage Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Amends the Forest Conservation and Management Act · Passed 2nd Reading · At Committee of the Whole House stage in June 2026 · Outcome pending</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-18-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-gov">Governance &amp; Public Administration</div>
<!-- ═══════════════════════════════════════ -->

<div class="pw-vote-card card-pending" data-sw="Mswada wa Marekebisho ya Ushindani, 2026. Bado uko kwenye mchakato. Unaboresha mfumo wa udhibiti wa soko nchini Kenya.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Competition (Amendment) Bill, 2026</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 4 of 2026 · Before Senate as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Passed National Assembly · Transmitted to Senate · Proposes amendments to the Competition Act to update Kenya's market regulatory framework · Outcome pending as of Jun 2026</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-22-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Marekebisho ya Ununuzi wa Umma na Uondoshaji wa Mali. Bado uko kwenye mchakato. Unaimarisha uwazi katika ununuzi wa serikali.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Public Procurement and Asset Disposal (Amendment) Bill</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 48 of 2024 · Committee of Whole House stage Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">At Committee of the Whole House stage · Amends the Public Procurement and Asset Disposal Act to strengthen procurement governance and transparency · Outcome pending as of Jun 2026</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-18-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<div class="pw-vote-card card-pending" data-sw="Mswada wa Utamaduni, 2024. Bado uko kwenye mchakato. Unatoa mfumo wa kisheria wa kukuza na kulinda utamaduni wa Kenya.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Culture Bill, 2024</div>
      <div class="pw-vote-meta">National Assembly · NA Bill No. 12 of 2024 · Before Senate as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Passed National Assembly · Transmitted to Senate for consideration · Provides a legislative framework for the promotion and protection of Kenya's cultural heritage · Outcome pending as of Jun 2026</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-18-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-health">Health</div>
<!-- ═══════════════════════════════════════ -->

<div class="pw-vote-card card-pending" data-sw="Mswada wa Marekebisho ya Afya. Bado uko kwenye mchakato. Unaoanisha Sheria ya Afya na mfumo wa Bima ya Afya kwa Wote.">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title">Health (Amendment) Bill</div>
      <div class="pw-vote-meta">Senate · Senate Bill No. 12 of 2025 · Before National Assembly as of Jun 2026</div>
    </div>
    <span class="pw-bill-stage stage-pending">In Progress</span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Senate bill transmitted to National Assembly · Proposes amendments to the Health Act to align with UHC rollout and Social Health Authority reforms · Scheduled before National Assembly in June 2026 · Outcome pending</span>
    <a href="https://vellum.co.ke/parliamentary-round-up-issue-no-18-of-2026/" target="_blank" class="pw-source-link">Source: Vellum Kenya ↗</a>
  </div>
</div>

<!-- ═══════════════════════════════════════ -->
<div class="section-label" id="group-new">Recently Added</div>
<!-- ═══════════════════════════════════════ -->
<?php if (empty($newBills)): ?>
  <p class="pw-empty-note">No newly proposed bills yet.</p>
<?php else: foreach ($newBills as $b):
    [$cardClass, $stageClass] = billsStageClass($b['bill_status']);
    $safeTitle  = htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8');
    $safeStatus = htmlspecialchars($b['bill_status'], ENT_QUOTES, 'UTF-8');
    $safeGroup  = htmlspecialchars($b['group_label'] ?: '—', ENT_QUOTES, 'UTF-8');
    $docPath    = !empty($b['document_path']) ? htmlspecialchars($b['document_path'], ENT_QUOTES, 'UTF-8') : null;
?>
<div class="pw-vote-card <?= $cardClass ?>">
  <div class="pw-vote-header">
    <div>
      <div class="pw-vote-title"><?= $safeTitle ?></div>
      <div class="pw-vote-meta"><?= $safeGroup ?> · Added via official docket review</div>
    </div>
    <span class="pw-bill-stage <?= $stageClass ?>"><?= $safeStatus ?></span>
  </div>
  <button class="card-listen-btn" onclick="readBillCard(this)">🔊 Listen</button>
  <div class="pw-vote-footer">
    <span class="pw-vote-footnote">Status: <?= $safeStatus ?></span>
    <?php if ($docPath): ?>
      <a href="/sauti/<?= $docPath ?>" target="_blank" class="pw-source-link">📄 View bill document ↗</a>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; endif; ?>
<script>
let billsKiswahili = false;
let billsSelectedVoice = null;
let billsMatchedVoices = [];
let billsCurrentUtterance = null;
let billsPlayingAll = false;
let billsPlayQueue = [];

const billsVoiceOptions = [
  { match: 'Zira',   label: 'Female',   lang: 'en' },
  { match: 'Mark',   label: 'Male',     lang: 'en' },
  { match: 'Rafiki', label: 'Mwanaume', lang: 'sw' },
  { match: 'Zuri',   label: 'Mwanamke', lang: 'sw' }
];

function billsLoadVoices(){
  const voices = window.speechSynthesis.getVoices();
  const picker = document.getElementById('bills-voice-picker');
  if (!picker || voices.length === 0) return;

  const currentLang = billsKiswahili ? 'sw' : 'en';
  picker.innerHTML = '';

  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Voice / Sauti';
  placeholder.disabled = true;
  placeholder.selected = true;
  picker.appendChild(placeholder);

  billsMatchedVoices = [];
  billsVoiceOptions
    .filter(w => w.lang === currentLang)
    .forEach(w => {
      const voice = voices.find(v => v.name.includes(w.match));
      if (voice) {
        billsMatchedVoices.push({ voice, label: w.label });
        const opt = document.createElement('option');
        opt.value = billsMatchedVoices.length - 1;
        opt.textContent = w.label;
        picker.appendChild(opt);
      }
    });

  picker.style.display = billsMatchedVoices.length > 0 ? 'inline-block' : 'none';
  picker.onchange = () => {
    if (picker.value === '') { billsSelectedVoice = null; return; }
    billsSelectedVoice = billsMatchedVoices[picker.value].voice;
  };
  billsSelectedVoice = billsMatchedVoices[0] ? billsMatchedVoices[0].voice : null;
}

window.speechSynthesis.onvoiceschanged = billsLoadVoices;
billsLoadVoices();

function toggleBillsLang(){
  billsKiswahili = !billsKiswahili;
  const btn = document.getElementById('bills-lang-btn');
  btn.classList.toggle('on', billsKiswahili);
  btn.textContent = billsKiswahili ? '🌐 English' : '🌐 Kiswahili';
  billsLoadVoices();
}

function billsCardText(card){
  if (billsKiswahili) {
    return card.getAttribute('data-sw') || card.querySelector('.pw-vote-title').textContent.trim();
  }
  const title = card.querySelector('.pw-vote-title')?.textContent.trim() || '';
  const meta = card.querySelector('.pw-vote-meta')?.textContent.trim() || '';
  const footnote = card.querySelector('.pw-vote-footnote')?.textContent.trim() || '';
  return [title, meta, footnote].filter(Boolean).join('. ');
}

function speakBillsText(text, onEnd){
  const utterance = new SpeechSynthesisUtterance(text);
  if (billsSelectedVoice) utterance.voice = billsSelectedVoice;
  else utterance.lang = billsKiswahili ? 'sw-KE' : 'en-US';
  utterance.onend = onEnd;
  utterance.onerror = onEnd;
  billsCurrentUtterance = utterance;
  speechSynthesis.speak(utterance);
}

function readBillCard(btn){
  const card = btn.closest('.pw-vote-card');
  const wasSpeaking = btn.classList.contains('speaking');
  stopBillsPlayAll();
  speechSynthesis.cancel();
  document.querySelectorAll('.card-listen-btn.speaking').forEach(b => b.classList.remove('speaking'));
  if (wasSpeaking) return;

  setTimeout(() => {
    btn.classList.add('speaking');
    speakBillsText(billsCardText(card), () => btn.classList.remove('speaking'));
  }, 50);
}

function toggleBillsPlayAll(){
  if (billsPlayingAll) { stopBillsPlayAll(); return; }
  speechSynthesis.cancel();
  document.querySelectorAll('.card-listen-btn.speaking').forEach(b => b.classList.remove('speaking'));

  billsPlayingAll = true;
  billsPlayQueue = Array.from(document.querySelectorAll('.pw-vote-card'));
  const playBtn = document.getElementById('bills-play-btn');
  playBtn.textContent = '⏹ Stop';
  playBtn.classList.add('playing');

  setTimeout(playNextBillsCard, 50);
}

function playNextBillsCard(){
  if (!billsPlayingAll || billsPlayQueue.length === 0) { stopBillsPlayAll(); return; }
  const card = billsPlayQueue.shift();
  const listenBtn = card.querySelector('.card-listen-btn');
  listenBtn.classList.add('speaking');
  card.scrollIntoView({ behavior: 'smooth', block: 'center' });

  speakBillsText(billsCardText(card), () => {
    listenBtn.classList.remove('speaking');
    if (billsPlayingAll) setTimeout(playNextBillsCard, 300);
  });
}

function stopBillsPlayAll(){
  billsPlayingAll = false;
  billsPlayQueue = [];
  const playBtn = document.getElementById('bills-play-btn');
  playBtn.textContent = '▶ Play All Bills';
  playBtn.classList.remove('playing');
  document.querySelectorAll('.card-listen-btn.speaking').forEach(b => b.classList.remove('speaking'));
}

function scrollToBillsGroup(id){
  document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

</body>
</html>