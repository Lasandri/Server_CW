<?php
$user   = (object)($alumni_data['user'] ?? array());
$degs   = $alumni_data['degrees']        ?? array();
$certs  = $alumni_data['certifications'] ?? array();
$lics   = $alumni_data['licences']       ?? array();
$crses  = $alumni_data['courses']        ?? array();
$emps   = $alumni_data['employment']     ?? array();
?>

<!-- Back button -->
<div style="margin-bottom:16px;">
    <a href="<?php echo site_url('alumni'); ?>"
       class="btn btn-secondary btn-sm">← Back to Alumni</a>
</div>

<!-- Profile Header -->
<div class="card">
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:24px;">
            <?php if (!empty($user->profile_image)): ?>
                <img src="<?php echo base_url($user->profile_image); ?>"
                     class="profile-img" alt="Profile">
            <?php else: ?>
                <div class="profile-placeholder">👤</div>
            <?php endif; ?>

            <div style="flex:1;">
                <h2 style="font-size:24px;color:#333;">
                    <?php echo htmlspecialchars(
                        $user->first_name . ' ' . $user->last_name
                    ); ?>
                </h2>
                <p style="color:#888;margin-top:4px;">
                    <?php echo htmlspecialchars($user->email); ?>
                </p>

                <?php if (!empty($user->city) || !empty($user->country)): ?>
                    <p style="color:#666;margin-top:8px;font-size:14px;">
                        📍 <?php echo htmlspecialchars(
                            implode(', ', array_filter(array(
                                $user->city ?? '',
                                $user->country ?? '',
                            )))
                        ); ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($user->linkedin_url)): ?>
                    <a href="<?php echo htmlspecialchars($user->linkedin_url); ?>"
                       target="_blank"
                       style="display:inline-block;margin-top:8px;
                              color:#1565C0;font-size:14px;">
                        🔗 LinkedIn Profile ↗
                    </a>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div style="display:flex;gap:16px;">
                <div style="text-align:center;padding:12px 16px;
                            background:#e3f2fd;border-radius:8px;">
                    <div style="font-size:24px;font-weight:700;color:#1565C0;">
                        <?php echo count($degs); ?>
                    </div>
                    <div style="font-size:11px;color:#888;">Degrees</div>
                </div>
                <div style="text-align:center;padding:12px 16px;
                            background:#e8f5e9;border-radius:8px;">
                    <div style="font-size:24px;font-weight:700;color:#2e7d32;">
                        <?php echo count($certs); ?>
                    </div>
                    <div style="font-size:11px;color:#888;">Certs</div>
                </div>
                <div style="text-align:center;padding:12px 16px;
                            background:#fff3e0;border-radius:8px;">
                    <div style="font-size:24px;font-weight:700;color:#e65100;">
                        <?php echo count($emps); ?>
                    </div>
                    <div style="font-size:11px;color:#888;">Jobs</div>
                </div>
            </div>
        </div>

        <?php if (!empty($user->biography)): ?>
            <div style="margin-top:20px;padding:16px;background:#f8f9fa;
                        border-radius:8px;border-left:4px solid #1565C0;">
                <p style="color:#555;font-size:14px;line-height:1.6;">
                    <?php echo htmlspecialchars($user->biography); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Degrees -->
<?php if (!empty($degs)): ?>
<div class="card">
    <div class="card-header">
        <h3>🎓 Degrees</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Degree</th><th>Field</th>
                    <th>University</th><th>Completed</th>
                    <th>Grade</th><th>URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($degs as $d):
                    $d = (object)$d;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($d->degree_title); ?></td>
                    <td>
                        <span class="badge badge-blue">
                            <?php echo htmlspecialchars($d->field_of_study); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($d->university_name); ?></td>
                    <td><?php echo date('M Y',
                        strtotime($d->completion_date)); ?></td>
                    <td><?php echo $d->grade
                        ? htmlspecialchars($d->grade) : '—'; ?></td>
                    <td>
                        <?php if (!empty($d->degree_url)): ?>
                            <a href="<?php echo htmlspecialchars($d->degree_url); ?>"
                               target="_blank"
                               style="color:#1565C0;font-size:12px;">
                                View ↗
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Certifications -->
<?php if (!empty($certs)): ?>
<div class="card">
    <div class="card-header"><h3>📜 Certifications</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Certification</th><th>Issuer</th>
                    <th>Completed</th><th>Expires</th><th>URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certs as $c):
                    $c = (object)$c;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars(
                        $c->certification_name); ?></td>
                    <td><?php echo htmlspecialchars(
                        $c->issuing_organization); ?></td>
                    <td><?php echo date('M Y',
                        strtotime($c->completion_date)); ?></td>
                    <td><?php echo !empty($c->expiry_date)
                        ? date('M Y', strtotime($c->expiry_date))
                        : 'No expiry'; ?></td>
                    <td>
                        <?php if (!empty($c->certification_url)): ?>
                            <a href="<?php echo htmlspecialchars(
                                $c->certification_url); ?>"
                               target="_blank"
                               style="color:#1565C0;font-size:12px;">
                                View ↗
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Employment -->
<?php if (!empty($emps)): ?>
<div class="card">
    <div class="card-header"><h3>💼 Employment History</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Job Title</th><th>Company</th>
                    <th>Start</th><th>End</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emps as $e):
                    $e = (object)$e;
                ?>
                <tr>
                    <td style="font-weight:600;">
                        <?php echo htmlspecialchars($e->job_title); ?>
                    </td>
                    <td><?php echo htmlspecialchars($e->company_name); ?></td>
                    <td><?php echo date('M Y',
                        strtotime($e->start_date)); ?></td>
                    <td>
                        <?php if ($e->is_current): ?>
                            <span class="badge badge-green">Current</span>
                        <?php elseif (!empty($e->end_date)): ?>
                            <?php echo date('M Y',
                                strtotime($e->end_date)); ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Licences -->
<?php if (!empty($lics)): ?>
<div class="card">
    <div class="card-header"><h3>🪪 Licences</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Licence</th><th>Issuing Body</th>
                    <th>Issued</th><th>Expires</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lics as $l):
                    $l = (object)$l;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($l->licence_name); ?></td>
                    <td><?php echo htmlspecialchars($l->issuing_body); ?></td>
                    <td><?php echo date('M Y',
                        strtotime($l->issue_date)); ?></td>
                    <td><?php echo !empty($l->expiry_date)
                        ? date('M Y', strtotime($l->expiry_date))
                        : 'No expiry'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Courses -->
<?php if (!empty($crses)): ?>
<div class="card">
    <div class="card-header"><h3>📚 Professional Courses</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Course</th><th>Provider</th>
                    <th>Completed</th><th>Duration</th><th>URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($crses as $c):
                    $c = (object)$c;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($c->course_name); ?></td>
                    <td><?php echo htmlspecialchars($c->provider); ?></td>
                    <td><?php echo date('M Y',
                        strtotime($c->completion_date)); ?></td>
                    <td><?php echo !empty($c->duration)
                        ? htmlspecialchars($c->duration) : '—'; ?></td>
                    <td>
                        <?php if (!empty($c->course_url)): ?>
                            <a href="<?php echo htmlspecialchars(
                                $c->course_url); ?>"
                               target="_blank"
                               style="color:#1565C0;font-size:12px;">
                                View ↗
                            </a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>