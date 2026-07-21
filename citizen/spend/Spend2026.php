<?php
require __DIR__ . '/../../config/db_connect.php';

$officialUpdates = [];
$result = $conn->query(
    "SELECT bts.bill_slug, bts.year, bts.amount, bts.notes, b.title AS bill_title
     FROM bill_tax_spend bts
     LEFT JOIN bills b ON b.slug = bts.bill_slug
     ORDER BY bts.year DESC, bts.bill_slug ASC"
);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $officialUpdates[] = $row;
    }
}
$conn->close();

function formatKsh($amount) {
    $amount = (float)$amount;
    if ($amount >= 1e12) return 'KSh ' . round($amount / 1e12, 2) . 'T';
    if ($amount >= 1e9)  return 'KSh ' . round($amount / 1e9, 1) . 'B';
    if ($amount >= 1e6)  return 'KSh ' . round($amount / 1e6, 1) . 'M';
    return 'KSh ' . number_format($amount, 2);
}
?>
<link rel="stylesheet" href="../../style.css">
<meta charset="UTF-8" />
<!-- ══════════════════════════════════════════
     DASHBOARD SECTION — FY 2026/27
══════════════════════════════════════════ -->
<section id="dashboard">
  <div class="section-wrapper">

    <div class="section-eyebrow">Tax Transparency Dashboard</div>
    <h2 class="section-title">Track Public Spending</h2>
    <p class="section-sub">Budget allocations, constituency development funds, and government expenditure — FY 2026/27.</p>

    <!-- ── BUDGET SUMMARY METRICS ── -->
    <div class="pw-metrics" style="margin-bottom:2.5rem;">
      <div class="pw-metric">
        <div class="pw-metric-label">Total Budget</div>
        <div class="pw-metric-val" style="font-size:22px;">KSh 4.82T</div>
      </div>
      <div class="pw-metric">
        <div class="pw-metric-label">Recurrent Expenditure</div>
        <div class="pw-metric-val" style="font-size:22px;">KSh 3.46T</div>
      </div>
      <div class="pw-metric">
        <div class="pw-metric-label">Development Expenditure</div>
        <div class="pw-metric-val green" style="font-size:22px;">KSh 749.5B</div>
      </div>
      <div class="pw-metric">
        <div class="pw-metric-label">County Transfers</div>
        <div class="pw-metric-val gold" style="font-size:22px;">KSh 502B</div>
      </div>
      <div class="pw-metric">
        <div class="pw-metric-label">Fiscal Deficit</div>
        <div class="pw-metric-val red" style="font-size:22px;">KSh 1.1T</div>
      </div>
    </div>

    <!-- ── NG-CDF SPENDING ── -->
    <div id="cdf-panel">

      <h3 style="margin-bottom:0.5rem;">NG-CDF Spending — FY 2026/27</h3>
      <p style="font-size:13px;color:var(--gray);margin-bottom:1.25rem;">
        The National Assembly's Finance and National Planning Committee has proposed raising NG-CDF from the initially proposed KSh 58.7B to at least KSh 61.8B, citing the statutory 2.5%-of-revenue minimum under the NG-CDF Act, 2015 — up from KSh 55.9B in FY 2025/26.
        A finalized per-constituency breakdown for FY 2026/27 has not yet been published; the table below still reflects the last confirmed FY 2025/26 allocations pending release at
        <a href="https://ngcdf.go.ke/allocations/" target="_blank" style="color:var(--green);font-weight:600;">ngcdf.go.ke</a>.
      </p>

      <div class="pw-notice" style="margin-bottom:1.25rem;">
        ⚠️ Constituency-level FY 2026/27 NG-CDF figures were not yet available as of this writing — only the aggregate proposal (KSh 58.7B–61.8B) has been published. The table below is the last confirmed dataset (FY 2025/26) and will be updated once Treasury/NG-CDF Board release the new breakdown.
      </div>

      <div class="pw-cdf-table">
        <div class="pw-cdf-header">
          <span>Constituency</span>
          <span>Allocation (FY 2025/26)</span>
          <span>Tier</span>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Kinangop</div>
            <div class="pw-cdf-county">Nyandarua County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Kieni</div>
            <div class="pw-cdf-county">Nyeri County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Mwea</div>
            <div class="pw-cdf-county">Kirinyaga County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Ruiru</div>
            <div class="pw-cdf-county">Kiambu County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Naivasha</div>
            <div class="pw-cdf-county">Nakuru County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Bobasi</div>
            <div class="pw-cdf-county">Kisii County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Kanduyi</div>
            <div class="pw-cdf-county">Bungoma County</div>
          </div>
          <div class="pw-cdf-amount">KSh 221.5M</div>
          <div class="pw-cdf-util util-high">Top tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Mt. Elgon</div>
            <div class="pw-cdf-county">Bungoma County</div>
          </div>
          <div class="pw-cdf-amount">KSh 188.4M</div>
          <div class="pw-cdf-util util-mid">2nd tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Malava</div>
            <div class="pw-cdf-county">Kakamega County</div>
          </div>
          <div class="pw-cdf-amount">KSh 188.4M</div>
          <div class="pw-cdf-util util-mid">2nd tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Karachuonyo</div>
            <div class="pw-cdf-county">Homa Bay County</div>
          </div>
          <div class="pw-cdf-amount">KSh 188.4M</div>
          <div class="pw-cdf-util util-mid">2nd tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Molo</div>
            <div class="pw-cdf-county">Nakuru County</div>
          </div>
          <div class="pw-cdf-amount">KSh 188.4M</div>
          <div class="pw-cdf-util util-mid">2nd tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Garissa Township</div>
            <div class="pw-cdf-county">Garissa County</div>
          </div>
          <div class="pw-cdf-amount">KSh 188.4M</div>
          <div class="pw-cdf-util util-mid">2nd tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Mumias East</div>
            <div class="pw-cdf-county">Kakamega County</div>
          </div>
          <div class="pw-cdf-amount">KSh 173.2M</div>
          <div class="pw-cdf-util util-low">Low tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Subukia</div>
            <div class="pw-cdf-county">Nakuru County</div>
          </div>
          <div class="pw-cdf-amount">KSh 173.2M</div>
          <div class="pw-cdf-util util-low">Low tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Tetu</div>
            <div class="pw-cdf-county">Nyeri County</div>
          </div>
          <div class="pw-cdf-amount">KSh 173.2M</div>
          <div class="pw-cdf-util util-low">Low tier</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Lamu West</div>
            <div class="pw-cdf-county">Lamu County</div>
          </div>
          <div class="pw-cdf-amount">KSh 192.6M</div>
          <div class="pw-cdf-util util-low">Low tier</div>
        </div>

      </div><!-- /pw-cdf-table -->

      <div class="pw-notice">
        ℹ️ NG-CDF committee has proposed raising the fund from KSh 58.7B to KSh 61.8B for FY 2026/27 to meet the statutory 2.5%-of-revenue minimum, up from KSh 55.9B in FY 2025/26.
        Note: A 2024 High Court ruling declared NG-CDF unconstitutional; MPs have until June 2026 to complete pending projects under the current framework.
        Full data at <a href="https://ngcdf.go.ke/allocations/" target="_blank">ngcdf.go.ke</a>.
      </div>

    </div><!-- /cdf-panel -->

    <!-- ── DONUT CHARTS ── -->
    <div class="dashboard-grid" style="margin-top:2.5rem;">

      <!-- LEFT: Budget Allocation Donut -->
      <div class="dash-card">
        <div class="dash-card-title">Budget Allocation by Category — FY 2026/27</div>
        <div class="budget-donut-row">
          <div class="donut-wrap">
            <svg viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
              <circle cx="70" cy="70" r="52" fill="none" stroke="#f4f4f0" stroke-width="25"/>
              <!-- Recurrent 71.8% (3.46T of 4.82T) → 0.718 × 326.7 = 234.6 -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#006600" stroke-width="25"
                stroke-dasharray="234.6 92.1" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
              <!-- Development 15.5% (749.5B of 4.82T) → 0.155 × 326.7 = 50.6 -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#bb0000" stroke-width="25"
                stroke-dasharray="50.6 276.1" stroke-dashoffset="-234.6" transform="rotate(-90 70 70)"/>
              <!-- Counties 10.4% (502B of 4.82T) → 0.104 × 326.7 = 34 -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#d4af37" stroke-width="25"
                stroke-dasharray="34 292.7" stroke-dashoffset="-285.2" transform="rotate(-90 70 70)"/>
            </svg>
            <div class="donut-center">
              <span class="donut-total">KSh 4.82T</span>
              <span class="donut-sub">FY 2026/27</span>
            </div>
          </div>
        </div>
        <div style="margin-top:1rem;display:flex;flex-direction:column;gap:6px;font-size:13px;">
          <p>🟢 Recurrent expenditure — 71.8% (KSh 3.46T)</p>
          <p>🔴 Development expenditure — 15.5% (KSh 749.5B)</p>
          <p>🟡 County transfers — 10.4% (KSh 502B)</p>
        </div>
      </div>

      <!-- RIGHT: Sector Spending Donut -->
      <div class="dash-card">
        <div class="dash-card-title">Key Sector Allocations — FY 2026/27</div>
        <div class="budget-donut-row">
          <div class="donut-wrap">
            <svg viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
              <circle cx="70" cy="70" r="52" fill="none" stroke="#f4f4f0" stroke-width="25"/>
              <!-- Education 668.3B of (668.3+566.9+230.3+177.2+61.8)=1704.5 → 39.2% -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#4a90d9" stroke-width="25"
                stroke-dasharray="128 199" stroke-dashoffset="0" transform="rotate(-90 70 70)"/>
              <!-- Security 566.9B → 33.3% -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#bb0000" stroke-width="25"
                stroke-dasharray="109 218" stroke-dashoffset="-128" transform="rotate(-90 70 70)"/>
              <!-- Roads 230.3B → 13.5% -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#006600" stroke-width="25"
                stroke-dasharray="44 283" stroke-dashoffset="-237" transform="rotate(-90 70 70)"/>
              <!-- Health 177.2B → 10.4% -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#d4af37" stroke-width="25"
                stroke-dasharray="34 293" stroke-dashoffset="-281" transform="rotate(-90 70 70)"/>
              <!-- NG-CDF (proposed) 61.8B → 3.6% -->
              <circle cx="70" cy="70" r="52" fill="none" stroke="#888" stroke-width="25"
                stroke-dasharray="12 315" stroke-dashoffset="-315" transform="rotate(-90 70 70)"/>
            </svg>
            <div class="donut-center">
              <span class="donut-total">Sectors</span>
              <span class="donut-sub">KSh billions</span>
            </div>
          </div>
        </div>
        <div style="margin-top:1rem;display:flex;flex-direction:column;gap:6px;font-size:13px;">
          <p style="color:#4a90d9;">🔵 Education — KSh 668.3B (28.5% of budget, largest)</p>
          <p>🔴 National Security — KSh 566.9B</p>
          <p>🟢 Roads — KSh 230.3B</p>
          <p>🟡 Health — KSh 177.2B (incl. cancer centre, family planning/reproductive health KSh 500M)</p>
          <p style="color:#888;">⚫ NG-CDF (proposed) — KSh 61.8B</p>
        </div>
      </div>

    </div><!-- /dashboard-grid -->

    <!-- ── SECTOR DETAIL BREAKDOWN ── -->
    <div style="margin-top:2rem;">
      <h3 style="margin-bottom:1rem;font-family:'Playfair Display',serif;">Sector Detail — FY 2026/27</h3>

      <div class="pw-cdf-table">
        <div class="pw-cdf-header">
          <span>Sector / Programme</span>
          <span>Allocation</span>
          <span>Change</span>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Education</div>
            <div class="pw-cdf-county">TSC salaries (KSh 420.9B), capitation, TVET, bursaries, infrastructure</div>
          </div>
          <div class="pw-cdf-amount">KSh 668.3B</div>
          <div class="pw-cdf-util util-high">↑ Largest — 28.5%</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">National Security</div>
            <div class="pw-cdf-county">Defence (+KSh 21.7B), Police (+KSh 11.4B), NIS KSh 58.6B, IEBC (+KSh 12B for 2027 polls prep)</div>
          </div>
          <div class="pw-cdf-amount">KSh 566.9B</div>
          <div class="pw-cdf-util util-high">↑ Top gainer</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Roads</div>
            <div class="pw-cdf-county">National road network expansion and maintenance</div>
          </div>
          <div class="pw-cdf-amount">KSh 230.3B</div>
          <div class="pw-cdf-util util-mid">↓ Cut vs FY25/26</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Health</div>
            <div class="pw-cdf-county">Cancer centre (Kisii), family planning/reproductive health KSh 500M, UHC, referral hospitals</div>
          </div>
          <div class="pw-cdf-amount">KSh 177.2B</div>
          <div class="pw-cdf-util util-mid">↓ Cut vs FY25/26</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">NG-CDF</div>
            <div class="pw-cdf-county">290 constituencies · bursaries, schools, police posts, digital hubs</div>
          </div>
          <div class="pw-cdf-amount">KSh 58.7–61.8B</div>
          <div class="pw-cdf-util util-mid">↑ Proposed increase</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">Debt Servicing (CFS)</div>
            <div class="pw-cdf-county">Domestic interest KSh 986.7B · Foreign interest · Pensions</div>
          </div>
          <div class="pw-cdf-amount">KSh 1.5T</div>
          <div class="pw-cdf-util util-low">↑ 31.2% of budget</div>
        </div>

        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const">County Transfers</div>
            <div class="pw-cdf-county">Equitable share to 47 county governments</div>
          </div>
          <div class="pw-cdf-amount">KSh 502B</div>
          <div class="pw-cdf-util util-mid">↑ Modest rise</div>
        </div>

      </div><!-- /sector table -->
    </div>

    <!-- ── DEFICIT NOTICE ── -->
    <div class="pw-notice" style="margin-top:1.5rem;">
      ℹ️ FY 2026/27 fiscal deficit: KSh 1.1 trillion (5.3% of GDP, up from 4.8% in FY 2025/26). Total revenue projected at KSh 3.6T (KSh 2.98T ordinary revenue + KSh 644.8B Ministerial AiA).
      Financed via KSh 116.2B external borrowing and a record KSh 995.7B domestic borrowing.
      Debt-to-GDP (present value) stands at 65.7%, still above the 55% statutory target. Global oil price shocks from the Middle East conflict (USD 63→100/barrel, Feb–Apr 2026) forced a downward GDP growth revision from 5.3% to 5.0%.
      Full budget documents at
      <a href="https://www.treasury.go.ke" target="_blank">treasury.go.ke</a> and
      <a href="https://www.parliament.go.ke" target="_blank">parliament.go.ke</a>.
    </div>
<!-- ── OFFICIAL TAX/SPEND UPDATES (submitted via docket review) ── -->
    <?php if (!empty($officialUpdates)): ?>
    <div style="margin-top:2rem;">
      <h3 style="margin-bottom:1rem;font-family:'Playfair Display',serif;">Official Tax/Spend Updates</h3>
      <p style="font-size:13px;color:var(--gray);margin-bottom:1.25rem;">
        Figures below were submitted by verified government officials through their docket and approved by an administrator.
      </p>

      <div class="pw-cdf-table">
        <div class="pw-cdf-header">
          <span>Bill</span>
          <span>Amount</span>
          <span>Year</span>
        </div>

        <?php foreach ($officialUpdates as $u):
            $safeTitle = htmlspecialchars($u['bill_title'] ?: $u['bill_slug'], ENT_QUOTES, 'UTF-8');
            $safeNotes = htmlspecialchars($u['notes'] ?: '', ENT_QUOTES, 'UTF-8');
            $safeYear  = htmlspecialchars((string)$u['year'], ENT_QUOTES, 'UTF-8');
            $safeAmt   = formatKsh($u['amount']);
        ?>
        <div class="pw-cdf-row">
          <div>
            <div class="pw-cdf-const"><?= $safeTitle ?></div>
            <?php if ($safeNotes): ?><div class="pw-cdf-county"><?= $safeNotes ?></div><?php endif; ?>
          </div>
          <div class="pw-cdf-amount"><?= $safeAmt ?></div>
          <div class="pw-cdf-util util-mid"><?= $safeYear ?></div>
        </div>
        <?php endforeach; ?>

      </div><!-- /pw-cdf-table -->
    </div>
    <?php endif; ?>

  </div><!-- /section-wrapper -->
</section><!-- /dashboard -->
