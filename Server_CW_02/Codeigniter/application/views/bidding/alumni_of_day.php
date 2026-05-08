<?php $page_title = 'Alumni of the Day'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .alumni-hero { background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff; border-radius:12px; padding:40px; text-align:center; margin-bottom:20px; }
    .alumni-hero img { width:150px; height:150px; border-radius:50%; object-fit:cover; border:4px solid #fff; margin-bottom:16px; }
    .alumni-hero h1 { font-size:32px; margin-bottom:8px; }
    .section-title { font-size:16px; font-weight:700; color:#333; margin:20px 0 12px; padding-bottom:8px; border-bottom:2px solid #eee; }
    .item-card { background:#f8f9fa; padding:12px 16px; border-radius:8px; margin-bottom:8px; }
    .item-card h4 { margin-bottom:4px; }
    .item-card p { color:#666; font-size:13px; margin:0; }
</style>

<div class="container">

    <div class="section-nav">
        <a href="<?php echo site_url('bidding'); ?>">← Bidding</a>
        <a href="<?php echo site_url('bidding/alumni-of-the-day'); ?>" class="active">Alumni of the Day</a>
    </div>

    <?php if ($alumni): ?>

        <!-- Hero Section -->
        <div class="alumni-hero">
            <?php if (!empty($alumni['user']->profile_image)): ?>
                <img src="<?php echo base_url($alumni['user']->profile_image); ?>" alt="Alumni">
            <?php else: ?>
                <div style="width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:64px;margin:0 auto 16px;">👤</div>
            <?php endif; ?>

            <h1><?php echo htmlspecialchars($alumni['user']->first_name . ' ' . $alumni['user']->last_name); ?></h1>
            <p style="opacity:0.9;">⭐ Alumni of the Day — <?php echo date('d F Y', strtotime($alumni['display_date'])); ?></p>

            <?php if (!empty($alumni['user']->city) || !empty($alumni['user']->country)): ?>
                <p style="opacity:0.8;">📍 <?php echo htmlspecialchars(trim($alumni['user']->city . ', ' . $alumni['user']->country, ', ')); ?></p>
            <?php endif; ?>

            <?php if (!empty($alumni['user']->linkedin_url)): ?>
                <a href="<?php echo htmlspecialchars($alumni['user']->linkedin_url); ?>" target="_blank"
                   style="display:inline-block; margin-top:12px; background:#fff; color:#667eea; padding:10px 24px; border-radius:8px; text-decoration:none; font-weight:600;">
                    🔗 Connect on LinkedIn
                </a>
            <?php endif; ?>
        </div>

        <!-- Biography -->
        <?php if (!empty($alumni['user']->biography)): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">📝 Biography</h3>
                    <p style="line-height:1.8;"><?php echo nl2br(htmlspecialchars($alumni['user']->biography)); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Degrees -->
        <?php if (!empty($alumni['degrees'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">🎓 Degrees</h3>
                    <?php foreach ($alumni['degrees'] as $d): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($d->degree_title); ?> — <?php echo htmlspecialchars($d->field_of_study); ?></h4>
                            <p><?php echo htmlspecialchars($d->university_name); ?> • <?php echo date('M Y', strtotime($d->completion_date)); ?></p>
                            <?php if (!empty($d->degree_url)): ?>
                                <a href="<?php echo htmlspecialchars($d->degree_url); ?>" target="_blank" class="url-link">View Degree Page ↗</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Certifications -->
        <?php if (!empty($alumni['certifications'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">📜 Certifications</h3>
                    <?php foreach ($alumni['certifications'] as $c): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($c->certification_name); ?></h4>
                            <p><?php echo htmlspecialchars($c->issuing_organization); ?> • <?php echo date('M Y', strtotime($c->completion_date)); ?></p>
                            <?php if (!empty($c->certification_url)): ?>
                                <a href="<?php echo htmlspecialchars($c->certification_url); ?>" target="_blank" class="url-link">View ↗</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Licences -->
        <?php if (!empty($alumni['licences'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">🪪 Licences</h3>
                    <?php foreach ($alumni['licences'] as $l): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($l->licence_name); ?></h4>
                            <p><?php echo htmlspecialchars($l->issuing_body); ?> • Issued: <?php echo date('M Y', strtotime($l->issue_date)); ?></p>
                            <?php if (!empty($l->licence_url)): ?>
                                <a href="<?php echo htmlspecialchars($l->licence_url); ?>" target="_blank" class="url-link">View ↗</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Courses -->
        <?php if (!empty($alumni['courses'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">📚 Professional Courses</h3>
                    <?php foreach ($alumni['courses'] as $c): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($c->course_name); ?></h4>
                            <p><?php echo htmlspecialchars($c->provider); ?> • <?php echo date('M Y', strtotime($c->completion_date)); ?></p>
                            <?php if (!empty($c->course_url)): ?>
                                <a href="<?php echo htmlspecialchars($c->course_url); ?>" target="_blank" class="url-link">View ↗</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Employment -->
        <?php if (!empty($alumni['employment'])): ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="section-title">💼 Employment History</h3>
                    <?php foreach ($alumni['employment'] as $e): ?>
                        <div class="item-card">
                            <h4><?php echo htmlspecialchars($e->job_title); ?></h4>
                            <p>
                                <?php echo htmlspecialchars($e->company_name); ?> •
                                <?php echo date('M Y', strtotime($e->start_date)); ?> —
                                <?php echo $e->is_current ? 'Present' : ($e->end_date ? date('M Y', strtotime($e->end_date)) : 'N/A'); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="card">
            <div class="card-body" style="text-align:center; padding:60px;">
                <p style="font-size:64px;">🏆</p>
                <h2 style="color:#666;">No Alumni of the Day Yet</h2>
                <p style="color:#888; margin-top:12px;">The winning alumni profile will appear here once a winner is selected.</p>
                <a href="<?php echo site_url('bidding'); ?>" class="btn btn-primary" style="margin-top:20px;">Place a Bid →</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php $this->load->view('profile/footer'); ?>