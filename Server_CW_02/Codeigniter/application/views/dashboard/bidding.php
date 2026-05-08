<?php
// Codeigniter/application/views/dashboard/bidding.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blind Bidding - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#0f3460; --secondary:#e94560; --sidebar-w:260px; --bg:#f0f2f5; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar { width:var(--sidebar-w); background:linear-gradient(180deg,#0f3460,#16213e); display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:100; }
        .sidebar-brand { display:flex; align-items:center; padding:25px 20px; border-bottom:1px solid rgba(255,255,255,0.1); gap:12px; }
        .brand-icon { width:42px; height:42px; background:#e94560; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon i { color:white; font-size:18px; }
        .brand-title { color:white; font-size:18px; font-weight:700; display:block; }
        .brand-subtitle { color:rgba(255,255,255,0.6); font-size:12px; display:block; }
        .sidebar-nav { list-style:none; padding:15px 0; flex:1; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:13px 20px; color:rgba(255,255,255,0.7); text-decoration:none; transition:all 0.2s; border-left:3px solid transparent; font-size:14px; }
        .nav-link:hover { color:white; background:rgba(255,255,255,0.08); border-left-color:rgba(255,255,255,0.3); }
        .nav-link.active { color:white; background:rgba(233,69,96,0.2); border-left-color:#e94560; }
        .nav-link i { width:20px; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px; }
        .user-avatar { width:36px; height:36px; background:#e94560; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0; }
        .user-details { flex:1; min-width:0; }
        .user-name { color:white; font-size:13px; font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-role { color:rgba(255,255,255,0.5); font-size:11px; }
        .logout-btn { color:rgba(255,255,255,0.6); text-decoration:none; padding:6px; border-radius:6px; }
        .logout-btn:hover { color:#e94560; }

        /* Main */
        .main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
        .topbar { background:white; padding:0 30px; height:65px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.08); position:sticky; top:0; z-index:99; }
        .topbar-title { font-size:20px; font-weight:700; color:var(--primary); }
        .page-body { padding:30px; flex:1; }

        /* Blind Bidding specific styles */
        .blind-banner {
            background: linear-gradient(135deg,#0f3460,#e94560);
            border-radius: 15px;
            padding: 20px 25px;
            color: white;
            margin-bottom: 25px;
        }

        .blind-banner h4 { margin:0; font-weight:700; }
        .blind-banner p  { margin:8px 0 0; opacity:0.85; font-size:14px; }

        /* Limit bar */
        .limit-card {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 25px;
        }

        .limit-bar-container {
            background: #e9ecef;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            margin: 10px 0;
        }

        .limit-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .limit-0 { background: #198754; }  /* green - none used */
        .limit-1 { background: #ffc107; }  /* yellow */
        .limit-2 { background: #fd7e14; }  /* orange */
        .limit-3 { background: #dc3545; }  /* red - all used */

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .feature-card.open      { border-left-color: #198754; }
        .feature-card.closed    { border-left-color: #6c757d; }
        .feature-card.bid-placed { border-left-color: #0f3460; }

        .feature-title   { font-size:16px; font-weight:700; color:var(--primary); }
        .feature-desc    { font-size:13px; color:#6c757d; margin:5px 0 15px; }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-open    { background:#d1e7dd; color:#198754; }
        .badge-closed  { background:#e2e3e5; color:#6c757d; }
        .badge-won     { background:#fff3cd; color:#856404; }
        .badge-lost    { background:#f8d7da; color:#842029; }
        .badge-pending { background:#cfe2ff; color:#084298; }

        /* Bid Form */
        .bid-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
        }

        .bid-input-group { position: relative; }

        .bid-input-group .currency {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 600;
            z-index: 2;
        }

        .bid-input-group input {
            padding-left: 30px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .bid-input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(15,52,96,0.15);
        }

        .btn-bid {
            background: linear-gradient(135deg,var(--primary),var(--secondary));
            border: none; color: white; border-radius: 8px;
            padding: 10px 25px; font-weight: 600; font-size: 14px;
            transition: opacity 0.2s;
        }

        .btn-bid:hover    { opacity: 0.9; color: white; }
        .btn-bid:disabled { opacity: 0.6; }

        .btn-update {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-update:hover { background: var(--primary); color: white; }

        /* Blind notice */
        .blind-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 13px;
            color: #856404;
            margin-bottom: 15px;
        }

        /* My current bid display */
        .my-bid-display {
            background: rgba(15,52,96,0.08);
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 12px;
        }

        .my-bid-amount {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
        }

        /* Result Cards */
        .result-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            text-align: center;
        }

        .result-won  { border-top: 4px solid #198754; }
        .result-lost { border-top: 4px solid #dc3545; }

        .result-icon { font-size: 40px; margin-bottom: 10px; }

        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php $this->load->view('dashboard/sidebar'); ?>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-gavel me-2 text-danger"></i>Blind Bidding System
        </div>
        <a href="<?php echo base_url('index.php/bidding/featured'); ?>"
           class="btn"
           style="background:linear-gradient(135deg,#198754,#0f3460);color:white;border-radius:8px;font-size:13px;font-weight:600;">
            <i class="fas fa-star me-1"></i>View Featured Winners
        </a>
    </div>

    <div class="page-body">

        <!-- Blind Bidding Banner -->
        <div class="blind-banner">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <h4><i class="fas fa-eye-slash me-2"></i>Blind Bidding System</h4>
                    <p>
                        Place bids to feature alumni profiles. 
                        <strong>You cannot see other bids</strong> - only your own. 
                        The highest bidder wins and their profile gets featured. 
                        Winners are selected automatically at <strong>midnight</strong>.
                    </p>
                </div>
                <i class="fas fa-gavel" style="font-size:40px;opacity:0.3;margin-left:20px;"></i>
            </div>
        </div>

        <div class="row g-4">

            <!-- Left Column: Features to Bid On -->
            <div class="col-lg-8">

                <!-- Select Alumni -->
                <div class="limit-card">
                    <h6 style="font-weight:700;color:var(--primary);margin-bottom:12px;">
                        <i class="fas fa-user-graduate me-2"></i>Select Alumni to Bid For
                    </h6>
                    <select class="form-select" id="selectAlumni"
                            onchange="loadAlumniData(this.value)">
                        <option value="">-- Select an Alumni --</option>
                        <?php foreach ($alumni as $a): ?>
                            <option value="<?php echo $a['id']; ?>">
                                <?php echo htmlspecialchars($a['full_name']); ?> -
                                <?php echo htmlspecialchars($a['programme']); ?>
                                (<?php echo $a['graduation_year']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Monthly Limit Card -->
                <div class="limit-card" id="limitCard" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 style="font-weight:700;color:var(--primary);margin:0;">
                            <i class="fas fa-calendar me-2"></i>Monthly Bid Limit
                        </h6>
                        <span id="limitText" style="font-size:13px;color:#6c757d;"></span>
                    </div>
                    <div class="limit-bar-container">
                        <div class="limit-bar" id="limitBar" style="width:0%"></div>
                    </div>
                    <small id="limitMessage" style="font-size:12px;"></small>
                </div>

                <!-- Available Features -->
                <h5 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                    Available Features This Month
                </h5>

                <div id="featuresContainer">
                    <?php if (empty($features)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>No features available for bidding this month.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($features as $feature): ?>
                            <div class="feature-card open" id="featureCard_<?php echo $feature['id']; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="feature-title">
                                            <i class="fas fa-star me-2 text-warning"></i>
                                            <?php echo htmlspecialchars($feature['feature_name']); ?>
                                        </p>
                                        <p class="feature-desc">
                                            <?php echo htmlspecialchars($feature['description']); ?>
                                        </p>
                                    </div>
                                    <span class="status-badge badge-open" 
                                          id="featureStatus_<?php echo $feature['id']; ?>">
                                        Open
                                    </span>
                                </div>

                                <!-- Blind Notice -->
                                <div class="blind-notice">
                                    <i class="fas fa-eye-slash me-2"></i>
                                    <strong>Blind Bidding:</strong> 
                                    You cannot see other participants' bids. 
                                    Only your own bid amount is shown.
                                </div>

                                <!-- Current Bid Display (shows after alumni selected) -->
                                <div id="currentBid_<?php echo $feature['id']; ?>" 
                                     style="display:none;">
                                </div>

                                <!-- Bid Form -->
                                <div class="bid-form" id="bidForm_<?php echo $feature['id']; ?>"
                                     style="display:none;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold" style="font-size:13px;">
                                                Your Bid Amount (£)
                                            </label>
                                            <div class="bid-input-group">
                                                <span class="currency">£</span>
                                                <input type="number"
                                                       class="form-control"
                                                       id="bidAmount_<?php echo $feature['id']; ?>"
                                                       placeholder="0.00"
                                                       min="1"
                                                       step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn-bid w-100 py-2"
                                                    id="bidBtn_<?php echo $feature['id']; ?>"
                                                    onclick="placeBid(<?php echo $feature['id']; ?>)">
                                                <i class="fas fa-gavel me-2"></i>Place Blind Bid
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Bid Form (shows if already bid) -->
                                <div class="bid-form" id="updateForm_<?php echo $feature['id']; ?>"
                                     style="display:none;">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold" style="font-size:13px;">
                                                New Bid Amount (increase only) (£)
                                            </label>
                                            <div class="bid-input-group">
                                                <span class="currency">£</span>
                                                <input type="number"
                                                       class="form-control"
                                                       id="updateAmount_<?php echo $feature['id']; ?>"
                                                       placeholder="Must be higher than current"
                                                       min="1"
                                                       step="0.01">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn-update w-100 py-2"
                                                    id="updateBtn_<?php echo $feature['id']; ?>"
                                                    onclick="updateBid(<?php echo $feature['id']; ?>)">
                                                <i class="fas fa-arrow-up me-2"></i>Increase Bid
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Right Column: Results & History -->
            <div class="col-lg-4">

                <!-- Results Panel -->
                <div class="limit-card" style="margin-bottom:20px;">
                    <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                        <i class="fas fa-trophy me-2 text-warning"></i>Bid Results
                    </h6>
                    <div id="resultsContainer">
                        <p style="color:#aaa;font-size:13px;text-align:center;padding:20px 0;">
                            Select an alumni to see their bid results
                        </p>
                    </div>
                </div>

                <!-- How it Works -->
                <div class="limit-card">
                    <h6 style="font-weight:700;color:var(--primary);margin-bottom:15px;">
                        <i class="fas fa-info-circle me-2"></i>How Blind Bidding Works
                    </h6>
                    <ol style="font-size:13px;color:#555;padding-left:20px;line-height:2;">
                        <li>Select an alumni to bid for</li>
                        <li>Choose a feature slot to bid on</li>
                        <li>Enter your bid amount (hidden from others)</li>
                        <li>You can only <strong>increase</strong> your bid</li>
                        <li>Maximum <strong>3 bids</strong> per month</li>
                        <li>Winners selected at <strong>midnight</strong></li>
                        <li>Winner's profile gets <strong>featured</strong></li>
                    </ol>

                    <div class="mt-3 p-3"
                         style="background:#f8f9fa;border-radius:8px;font-size:12px;color:#6c757d;">
                        <i class="fas fa-clock me-1"></i>
                        Next winner selection: <strong>Tonight at Midnight</strong>
                    </div>
                </div>

                <!-- Admin: Manual Winner Selection -->
                <div class="limit-card mt-3">
                    <h6 style="font-weight:700;color:var(--primary);margin-bottom:10px;">
                        <i class="fas fa-cog me-2"></i>Admin Controls
                    </h6>
                    <button class="btn-bid w-100 py-2"
                            onclick="triggerWinnerSelection()">
                        <i class="fas fa-play me-2"></i>Run Winner Selection Now
                    </button>
                    <small class="text-muted d-block mt-2" style="font-size:11px;">
                        Normally runs automatically at midnight. 
                        Use this for testing only.
                    </small>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_URL = '<?php echo $api_url; ?>';
const API_KEY = '<?php echo $api_key; ?>';

const HEADERS = {
    'Authorization': 'Bearer ' + API_KEY,
    'Content-Type': 'application/json'
};

let selectedAlumniId = null;
let alumniMyBids     = {};  // store bids keyed by feature_id

// ── Load alumni data when selected ───────────────────────────────────────
async function loadAlumniData(alumniId) {
    if (!alumniId) {
        selectedAlumniId = null;
        document.getElementById('limitCard').style.display = 'none';
        resetBidForms();
        return;
    }

    selectedAlumniId = alumniId;
    document.getElementById('limitCard').style.display = 'block';

    // Load limit and bids in parallel
    await Promise.all([
        loadMonthlyLimit(alumniId),
        loadMyBids(alumniId),
        loadBidResults(alumniId)
    ]);
}

// ── Load monthly limit ────────────────────────────────────────────────────
async function loadMonthlyLimit(alumniId) {
    try {
        const res  = await fetch(`${API_URL}/bidding/limit/${alumniId}`, { headers: HEADERS });
        const json = await res.json();

        if (!json.success) return;

        const limit    = json.data;
        const pct      = (limit.bids_used / limit.max_bids) * 100;

        document.getElementById('limitText').textContent =
            `${limit.bids_used} / ${limit.max_bids} bids used this month`;

        const bar = document.getElementById('limitBar');
        bar.style.width = pct + '%';
        bar.className   = 'limit-bar limit-' + limit.bids_used;

        const msg = document.getElementById('limitMessage');
        if (limit.can_bid) {
            msg.style.color    = '#198754';
            msg.textContent    = `✅ ${limit.bids_remaining} bid(s) remaining this month`;
        } else {
            msg.style.color    = '#dc3545';
            msg.textContent    = '❌ Monthly limit reached (3/3). Cannot place new bids.';
        }

    } catch (e) {
        console.error('Load limit error:', e);
    }
}

// ── Load my bids for all features ────────────────────────────────────────
async function loadMyBids(alumniId) {
    try {
        const res  = await fetch(`${API_URL}/bidding/my-bids/${alumniId}`, { headers: HEADERS });
        const json = await res.json();

        if (!json.success) return;

        // Store bids indexed by feature_id (need to get feature_id from feature card)
        alumniMyBids = {};
        json.data.forEach(bid => {
            // We need to match by feature name since we don't have feature_id directly
            // Instead, refresh each feature card
        });

        // Show bid status on each feature card
        json.data.forEach(bid => {
            updateFeatureCardWithBid(bid);
        });

        // Show new bid form for features not yet bid on
        const bidFeatureNames = json.data.map(b => b.feature_name);

        // Get all feature cards and show/hide forms
        document.querySelectorAll('[id^="featureCard_"]').forEach(card => {
            const featureId = card.id.split('_')[1];
            const featureName = card.querySelector('.feature-title').textContent.trim();
            const cleanName   = featureName.replace(/^\S+\s/, ''); // remove icon text

            const hasBid = json.data.some(b => b.feature_name === cleanName);
            if (!hasBid) {
                showNewBidForm(featureId);
            }
        });

    } catch (e) {
        console.error('Load my bids error:', e);
    }
}

// ── Update feature card when bid exists ───────────────────────────────────
function updateFeatureCardWithBid(bid) {
    // Find the card by matching feature name
    const cards = document.querySelectorAll('.feature-title');
    cards.forEach(titleEl => {
        const cardTitle = titleEl.textContent.replace(/^\S+\s/, '').trim();
        if (cardTitle === bid.feature_name) {
            const card      = titleEl.closest('[id^="featureCard_"]');
            const featureId = card.id.split('_')[1];

            // Store bid id on card
            card.dataset.bidId = bid.id;

            // Show current bid amount
            const currentBidDiv = document.getElementById('currentBid_' + featureId);
            currentBidDiv.style.display = 'block';
            currentBidDiv.innerHTML = `
                <div class="my-bid-display mb-2">
                    <small style="color:#6c757d;font-weight:600;font-size:12px;">YOUR CURRENT BID</small>
                    <div class="my-bid-amount">£${parseFloat(bid.bid_amount).toFixed(2)}</div>
                    <small style="color:#aaa;font-size:11px;">
                        <i class="fas fa-eye-slash me-1"></i>Other bids are hidden (blind bidding)
                    </small>
                </div>
            `;

            // Hide new bid form, show update form
            const bidForm    = document.getElementById('bidForm_'    + featureId);
            const updateForm = document.getElementById('updateForm_' + featureId);

            if (bidForm)    bidForm.style.display    = 'none';
            if (updateForm) updateForm.style.display = 'block';

            // Set minimum for update input
            const updateInput = document.getElementById('updateAmount_' + featureId);
            if (updateInput) {
                updateInput.min         = (parseFloat(bid.bid_amount) + 0.01).toString();
                updateInput.placeholder = `Must be > £${parseFloat(bid.bid_amount).toFixed(2)}`;
            }

            // Update status badge
            const statusBadge = document.getElementById('featureStatus_' + featureId);
            if (statusBadge) {
                statusBadge.textContent  = 'Bid Placed';
                statusBadge.className    = 'status-badge badge-pending';
            }

            // Update card border
            card.classList.remove('open');
            card.classList.add('bid-placed');
        }
    });
}

// ── Show new bid form ─────────────────────────────────────────────────────
function showNewBidForm(featureId) {
    const bidForm    = document.getElementById('bidForm_'    + featureId);
    const updateForm = document.getElementById('updateForm_' + featureId);
    if (bidForm)    bidForm.style.display    = 'block';
    if (updateForm) updateForm.style.display = 'none';
}

// ── Reset all bid forms ───────────────────────────────────────────────────
function resetBidForms() {
    document.querySelectorAll('[id^="bidForm_"]').forEach(f => f.style.display = 'none');
    document.querySelectorAll('[id^="updateForm_"]').forEach(f => f.style.display = 'none');
    document.querySelectorAll('[id^="currentBid_"]').forEach(f => { f.style.display = 'none'; f.innerHTML = ''; });
}

// ── Place new bid ─────────────────────────────────────────────────────────
async function placeBid(featureId) {
    if (!selectedAlumniId) {
        showToast('Please select an alumni first', 'warning');
        return;
    }

    const amount = parseFloat(document.getElementById('bidAmount_' + featureId).value);

    if (!amount || amount <= 0) {
        showToast('Please enter a valid bid amount', 'warning');
        return;
    }

    const btn = document.getElementById('bidBtn_' + featureId);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Placing Bid...';

    try {
        const res = await fetch(`${API_URL}/bidding/place`, {
            method:  'POST',
            headers: HEADERS,
            body:    JSON.stringify({
                alumni_id:  parseInt(selectedAlumniId),
                feature_id: parseInt(featureId),
                bid_amount: amount
            })
        });

        const json = await res.json();

        if (json.success) {
            showToast(`✅ ${json.message}`, 'success');
            // Reload alumni data to update UI
            await loadAlumniData(selectedAlumniId);
        } else {
            showToast(`❌ ${json.message}`, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-gavel me-2"></i>Place Blind Bid';
        }

    } catch (e) {
        showToast('Connection error. Check API server.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-gavel me-2"></i>Place Blind Bid';
    }
}

// ── Update existing bid (increase only) ──────────────────────────────────
async function updateBid(featureId) {
    if (!selectedAlumniId) {
        showToast('Please select an alumni first', 'warning');
        return;
    }

    const card   = document.getElementById('featureCard_' + featureId);
    const bidId  = card.dataset.bidId;
    const amount = parseFloat(document.getElementById('updateAmount_' + featureId).value);

    if (!amount || amount <= 0) {
        showToast('Please enter a valid bid amount', 'warning');
        return;
    }

    const btn = document.getElementById('updateBtn_' + featureId);
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

    try {
        const res = await fetch(`${API_URL}/bidding/update/${bidId}`, {
            method:  'PUT',
            headers: HEADERS,
            body:    JSON.stringify({
                alumni_id:  parseInt(selectedAlumniId),
                new_amount: amount
            })
        });

        const json = await res.json();

        if (json.success) {
            showToast(`✅ Bid increased from £${json.data.old_amount} to £${json.data.new_amount}`, 'success');
            await loadAlumniData(selectedAlumniId);
        } else {
            showToast(`❌ ${json.message}`, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-arrow-up me-2"></i>Increase Bid';
        }

    } catch (e) {
        showToast('Connection error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-arrow-up me-2"></i>Increase Bid';
    }
}

// ── Load bid results (win/lose) ───────────────────────────────────────────
async function loadBidResults(alumniId) {
    try {
        const res  = await fetch(`${API_URL}/bidding/results/${alumniId}`, { headers: HEADERS });
        const json = await res.json();
        const container = document.getElementById('resultsContainer');

        if (!json.success || json.data.length === 0) {
            container.innerHTML = `
                <p style="color:#aaa;font-size:13px;text-align:center;padding:20px 0;">
                    No completed bid results yet.<br>
                    <small>Results appear after winner selection at midnight.</small>
                </p>
            `;
            return;
        }

        container.innerHTML = json.data.map(r => `
            <div class="result-card ${r.is_winner ? 'result-won' : 'result-lost'} mb-3">
                <div class="result-icon">
                    ${r.is_winner ? '🏆' : '😔'}
                </div>
                <strong style="font-size:14px;">${escapeHtml(r.feature_name)}</strong>
                <br>
                <span class="status-badge ${r.is_winner ? 'badge-won' : 'badge-lost'} mt-2 d-inline-block">
                    ${r.is_winner ? '🥇 WON' : '❌ LOST'}
                </span>
                <br>
                <small style="color:#aaa;font-size:11px;margin-top:5px;display:block;">
                    ${r.bid_month}/${r.bid_year}
                </small>
            </div>
        `).join('');

    } catch (e) {
        console.error('Load results error:', e);
    }
}

// ── Trigger manual winner selection (admin) ───────────────────────────────
async function triggerWinnerSelection() {
    if (!confirm('Run winner selection now? This will close all open bids and select winners.')) {
        return;
    }

    showToast('Running winner selection...', 'info');

    try {
        const res  = await fetch(`${API_URL}/bidding/select-winners`, {
            method:  'POST',
            headers: HEADERS
        });
        const json = await res.json();

        if (json.success) {
            showToast(`✅ ${json.message}`, 'success');

            // Reload page to show updated statuses
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showToast(`❌ ${json.message}`, 'danger');
        }

    } catch (e) {
        showToast('Connection error.', 'danger');
    }
}

// ── Toast notification ────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const colors = {
        success: '#198754',
        danger:  '#dc3545',
        warning: '#ffc107',
        info:    '#0dcaf0'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        background:${colors[type]};
        color:${type === 'warning' ? '#000' : '#fff'};
        padding:12px 20px;
        border-radius:10px;
        margin-bottom:10px;
        font-size:14px;
        font-weight:600;
        box-shadow:0 4px 15px rgba(0,0,0,0.2);
        animation:slideIn 0.3s ease;
        max-width:350px;
    `;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity    = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}
</script>
</body>
</html>