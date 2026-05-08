<?php $page_title = 'Dashboard'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($this->session->flashdata('error')); ?></div>
    <?php endif; ?>

    <!-- Welcome & Completion -->
    <div class="card">
        <div class="card-body" style="display:flex; align-items:center; gap:24px;">
            
            <!-- Profile Image -->
            <?php if ($profile && !empty($profile->profile_image)): ?>
                <img src="<?php echo base_url($profile->profile_image); ?>" class="profile-img" alt="Profile">
            <?php else: ?>
                <div class="profile-img-placeholder">👤</div>
            <?php endif; ?>
            
            <div style="flex:1;">
                <h2>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h2>
                <p style="color:#666; margin:8px 0;">Profile Completion: <?php echo $completion['percentage']; ?>%</p>
                
                <!-- Progress Bar -->
                <div class="progress-bar">
                    <?php
                    $pct = $completion['percentage'];
                    $class = ($pct < 40) ? 'low' : (($pct < 75) ? 'mid' : 'high');
                    ?>
                    <div class="progress-fill <?php echo $class; ?>" style="width:<?php echo $pct; ?>%"></div>
                </div>
                <p style="font-size:13px; color:#888;"><?php echo $completion['completed']; ?> of <?php echo $completion['total']; ?> sections completed</p>
            </div>
        </div>
    </div>

    <!-- Section Navigation -->
    <div class="section-nav">
        <a href="<?php echo site_url('profile/personal'); ?>" class="<?php echo $completion['sections']['personal_info'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['personal_info'] ? '✅' : '⬜'; ?> Personal Info
        </a>
        <a href="<?php echo site_url('profile/image'); ?>" class="<?php echo $completion['sections']['profile_image'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['profile_image'] ? '✅' : '⬜'; ?> Profile Image
        </a>
        <a href="<?php echo site_url('profile/degrees'); ?>" class="<?php echo $completion['sections']['degrees'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['degrees'] ? '✅' : '⬜'; ?> Degrees
        </a>
        <a href="<?php echo site_url('profile/certifications'); ?>" class="<?php echo $completion['sections']['certifications'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['certifications'] ? '✅' : '⬜'; ?> Certifications
        </a>
        <a href="<?php echo site_url('profile/licences'); ?>" class="<?php echo $completion['sections']['licences_courses'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['licences_courses'] ? '✅' : '⬜'; ?> Licences
        </a>
        <a href="<?php echo site_url('profile/courses'); ?>">
            Courses
        </a>
        <a href="<?php echo site_url('profile/employment'); ?>" class="<?php echo $completion['sections']['employment'] ? 'active' : ''; ?>">
            <?php echo $completion['sections']['employment'] ? '✅' : '⬜'; ?> Employment
        </a>
    </div>

    <!-- Quick Summary Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px;">
        
        <div class="card">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:32px;">🎓</div>
                <h3><?php echo count($degrees); ?></h3>
                <p style="color:#888; font-size:13px;">Degrees</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:32px;">📜</div>
                <h3><?php echo count($certifications); ?></h3>
                <p style="color:#888; font-size:13px;">Certifications</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:32px;">🪪</div>
                <h3><?php echo count($licences); ?></h3>
                <p style="color:#888; font-size:13px;">Licences</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body" style="text-align:center;">
                <div style="font-size:32px;">💼</div>
                <h3><?php echo count($employment); ?></h3>
                <p style="color:#888; font-size:13px;">Jobs</p>
            </div>
        </div>
    </div>

</div>

<?php $this->load->view('profile/footer'); ?>