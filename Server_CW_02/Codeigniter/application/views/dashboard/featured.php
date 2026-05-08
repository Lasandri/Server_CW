<?php
// Codeigniter/application/views/dashboard/featured.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Alumni - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#0f3460; --secondary:#e94560; --sidebar-w:260px; --bg:#f0f2f5; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); display:flex; min-height:100vh; }
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
        .main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
        .topbar { background:white; padding:0 30px; height:65px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.08); position:sticky; top:0; z-index:99; }
        .topbar-title { font-size:20px; font-weight:700; color:var(--primary); }
        .page-body { padding:30px; flex:1; }

        /* Featured cards */
        .featured-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }

        .featured-card:hover { transform: translateY(-5px); }

        .featured-header {
            background: linear-gradient(135deg,#0f3460,#e94560);
            padding: 30px 25px;
            text-align: center;
            color: white;
        }

        .featured-avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 30px; font-weight: 700;
            margin: 0 auto 12px;
            border: 3px solid rgba(255,255,255,0.4);
        }

        .featured-name  { font-size:20px; font-weight:700; margin:0; }
        .featured-title { font-size:13px; opacity:0.85; margin:4px 0 0; }

        .featured-body  { padding:25px; }

        .featured-badge-wrap { position:absolute; top:15px; right:15px; }
        .star-badge {
            background: #ffc107;
            color: #856404;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #555;
        }

        .info-row:last-child { border-bottom: none; }
        .info-row i { width: 16px; color: #aaa; }

        .skill-chip {
            background: rgba(15,52,96,0.08);
            color: #0f3460;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }

        .feature-slot-badge {
            background: rgba(233,69,96,0.1);
            color: #e94560;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .no-featured {
            text-align: center;
            padding: 60px 20px;
            color: #aaa;
        }
    </style>
</head>
<body>

<?php $this->load->view('dashboard/sidebar'); ?>

<div class="main-content">

    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-star me-2 text-warning"></i>Featured Alumni - This Month's Winners
        </div>
        <a href="<?php echo base_url('index.php/bidding'); ?>"
           style="background:linear-gradient(135deg,#0f3460,#e94560);color:white;border-radius:8px;
                  padding:8px 18px;text-decoration:none;font-size:13px;font-weight:600;">
            <i class="fas fa-gavel me-1"></i>Back to Bidding
        </a>
    </div>

    <div class="page-body">

        <!-- Header Banner -->
        <div style="background:linear-gradient(135deg,#ffc107,#fd7e14);border-radius:15px;
                    padding:25px;margin-bottom:30px;color:white;">
            <h4 style="margin:0;font-weight:700;">
                🏆 This Month's Featured Alumni
            </h4>
            <p style="margin:8px 0 0;opacity:0.9;font-size:14px;">
                These alumni profiles won the blind bidding competition and are featured this month.
            </p>
        </div>

        <?php if (empty($featured)): ?>
            <div class="no-featured">
                <i class="fas fa-trophy fa-3x mb-3" style="color:#ffc107;"></i>
                <h5>No Featured Alumni Yet</h5>
                <p>Winner selection happens at midnight. Check back later!</p>
                <a href="<?php echo base_url('index.php/bidding'); ?>"
                   class="btn mt-3"
                   style="background:#0f3460;color:white;border-radius:8px;">
                    Place a Bid
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featured as $alumni): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="featured-card">

                            <!-- Star Badge -->
                            <div class="featured-badge-wrap">
                                <span class="star-badge">⭐ Featured</span>
                            </div>

                            <!-- Header -->
                            <div class="featured-header">
                                <div class="featured-avatar">
                                    <?php
                                    $parts = explode(' ', $alumni['full_name']);
                                    echo strtoupper(substr($parts[0], 0, 1) .
                                         (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                    ?>
                                </div>
                                <p class="featured-name">
                                    <?php echo htmlspecialchars($alumni['full_name']); ?>
                                </p>
                                <p class="featured-title">
                                    <?php echo htmlspecialchars($alumni['job_title'] ?? ''); ?>
                                </p>

                                <!-- Feature slot -->
                                <div style="margin-top:10px;">
                                    <span class="feature-slot-badge" style="background:rgba(255,255,255,0.2);color:white;">
                                        <?php echo htmlspecialchars($alumni['feature_name']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="featured-body">
                                <div class="info-row">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span><?php echo htmlspecialchars($alumni['programme']); ?>
                                          (<?php echo $alumni['graduation_year']; ?>)</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-briefcase"></i>
                                    <span><?php echo htmlspecialchars($alumni['industry_sector'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-building"></i>
                                    <span><?php echo htmlspecialchars($alumni['employer'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>
                                        <?php echo htmlspecialchars($alumni['location_city'] ?? ''); ?>,
                                        <?php echo htmlspecialchars($alumni['location_country'] ?? ''); ?>
                                    </span>
                                </div>

                                <?php if (!empty($alumni['skills'])): ?>
                                    <div style="margin-top:12px;">
                                        <small style="color:#aaa;font-weight:600;font-size:11px;">SKILLS</small>
                                        <div style="margin-top:5px;">
                                            <?php
                                            $skills = is_array($alumni['skills'])
                                                ? $alumni['skills']
                                                : json_decode($alumni['skills'], true);
                                            foreach (array_slice($skills ?? [], 0, 4) as $skill): ?>
                                                <span class="skill-chip">
                                                    <?php echo htmlspecialchars($skill); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div style="margin-top:15px;font-size:11px;color:#aaa;text-align:center;">
                                    <i class="fas fa-clock me-1"></i>
                                    Selected: <?php echo isset($alumni['winner_selected_at'])
                                        ? date('d M Y H:i', strtotime($alumni['winner_selected_at']))
                                        : 'N/A'; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>