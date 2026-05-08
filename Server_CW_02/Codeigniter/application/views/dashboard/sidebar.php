<?php
// Codeigniter/application/views/dashboard/sidebar.php
?>
<nav class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-university"></i>
        </div>
        <div class="brand-text">
            <span class="brand-title">Alumni</span>
            <span class="brand-subtitle">Analytics</span>
        </div>
    </div>

    <!-- Nav Links -->
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="<?php echo base_url('index.php/dashboard'); ?>"
               class="nav-link <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url('index.php/dashboard/graphs'); ?>"
               class="nav-link <?php echo ($active_page === 'graphs') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics & Graphs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url('index.php/dashboard/alumni'); ?>"
               class="nav-link <?php echo ($active_page === 'alumni') ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>View Alumni</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?php echo base_url('index.php/bidding'); ?>"
            class="nav-link <?php echo ($active_page === 'bidding') ? 'active' : ''; ?>">
                <i class="fas fa-gavel"></i>
                <span>Blind Bidding</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo base_url('index.php/security'); ?>"
            class="nav-link <?php echo ($active_page === 'security') ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>Security & API Keys</span>
            </a>
        </li>
    </ul>

    <!-- User info at bottom -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                <span class="user-role">Staff</span>
            </div>
        </div>
        <a href="<?php echo base_url('index.php/auth/logout'); ?>"
           class="logout-btn" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</nav>