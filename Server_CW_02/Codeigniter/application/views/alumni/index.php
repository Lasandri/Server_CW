<!-- Alumni Directory with Filters -->

<!-- Filter Form -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-header">
        <h3>🔍 Filter Alumni</h3>
        <div>
            <a href="<?php echo site_url('alumni/export?' .
                http_build_query($filters)); ?>"
               class="btn btn-success btn-sm">⬇ Export CSV</a>
        </div>
    </div>
    <div class="card-body">
        <?php echo form_open('alumni', array('method'=>'get')); ?>
            <div class="form-row">

                <!-- Programme Filter -->
                <div class="form-group">
                    <label>Programme</label>
                    <select name="programme">
                        <option value="">All Programmes</option>
                        <?php
                        $programmes = isset($filter_opts['programmes'])
                            ? $filter_opts['programmes'] : array();
                        foreach ($programmes as $p):
                            $val = is_object($p) ? $p->programme : $p;
                        ?>
                        <option value="<?php echo htmlspecialchars($val); ?>"
                            <?php echo (isset($filters['programme']) &&
                                $filters['programme'] == $val)
                                ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($val); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Graduation Year Filter -->
                <div class="form-group">
                    <label>Graduation Year</label>
                    <select name="graduation_year">
                        <option value="">All Years</option>
                        <?php
                        $years = isset($filter_opts['graduation_years'])
                            ? $filter_opts['graduation_years'] : array();
                        foreach ($years as $y):
                            $yr = is_object($y) ? $y->year : $y;
                        ?>
                        <option value="<?php echo $yr; ?>"
                            <?php echo (isset($filters['graduation_year']) &&
                                $filters['graduation_year'] == $yr)
                                ? 'selected' : ''; ?>>
                            <?php echo $yr; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Industry Filter -->
                <div class="form-group">
                    <label>Industry / Company</label>
                    <input type="text" name="industry"
                           value="<?php echo htmlspecialchars(
                               $filters['industry'] ?? ''
                           ); ?>"
                           placeholder="e.g., Google, NHS...">
                </div>

                <!-- Country Filter -->
                <div class="form-group">
                    <label>Country</label>
                    <select name="country">
                        <option value="">All Countries</option>
                        <?php
                        $countries = isset($filter_opts['countries'])
                            ? $filter_opts['countries'] : array();
                        foreach ($countries as $c):
                            $cn = is_object($c) ? $c->country : $c;
                        ?>
                        <option value="<?php echo htmlspecialchars($cn); ?>"
                            <?php echo (isset($filters['country']) &&
                                $filters['country'] == $cn)
                                ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cn); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="align-self:flex-end;">
                    <button type="submit" class="btn btn-primary">
                        🔍 Search
                    </button>
                    <a href="<?php echo site_url('alumni'); ?>"
                       class="btn btn-secondary">Clear</a>
                </div>

            </div>
        <?php echo form_close(); ?>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-header">
        <h3>
            👥 Alumni
            <?php if (!empty($filters)): ?>
                <span style="font-size:13px;color:#888;font-weight:normal;">
                    — filtered results
                </span>
            <?php endif; ?>
        </h3>
        <span style="color:#888;font-size:13px;">
            <?php echo number_format($pagination['total']); ?> alumni found
        </span>
    </div>
    <div class="card-body" style="padding:0;">

        <?php if (empty($alumni)): ?>
            <div style="text-align:center;padding:40px;color:#888;">
                <p style="font-size:40px;">👥</p>
                <p>No alumni found with the selected filters.</p>
                <a href="<?php echo site_url('alumni'); ?>"
                   class="btn btn-primary" style="margin-top:12px;">
                    Clear Filters
                </a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Alumni</th>
                        <th>Programme</th>
                        <th>Grad Year</th>
                        <th>Current Role</th>
                        <th>Location</th>
                        <th>Links</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumni as $a):
                        $a = (object) $a;
                    ?>
                    <tr>
                        <td>
                            <div style="display:flex;
                                        align-items:center;gap:10px;">
                                <?php if (!empty($a->profile_image)): ?>
                                    <img src="<?php
                                        echo base_url($a->profile_image);
                                    ?>"
                                         style="width:36px;height:36px;
                                                border-radius:50%;
                                                object-fit:cover;"
                                         alt="">
                                <?php else: ?>
                                    <div style="width:36px;height:36px;
                                                border-radius:50%;
                                                background:#e3f2fd;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                font-size:16px;">👤</div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:600;">
                                        <?php echo htmlspecialchars(
                                            $a->first_name . ' ' . $a->last_name
                                        ); ?>
                                    </div>
                                    <div style="font-size:11px;color:#888;">
                                        <?php echo htmlspecialchars($a->email); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($a->programme)): ?>
                                <span class="badge badge-blue">
                                    <?php echo htmlspecialchars($a->programme); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($a->graduation_year)
                                ? $a->graduation_year : '—'; ?>
                        </td>
                        <td>
                            <?php if (!empty($a->current_job_title)): ?>
                                <div style="font-size:13px;">
                                    <?php echo htmlspecialchars(
                                        $a->current_job_title
                                    ); ?>
                                </div>
                                <?php if (!empty($a->current_company)): ?>
                                    <div style="font-size:11px;color:#888;">
                                        @ <?php echo htmlspecialchars(
                                            $a->current_company
                                        ); ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;">
                            <?php
                            $loc = array_filter(array(
                                isset($a->city) ? $a->city : '',
                                isset($a->country) ? $a->country : '',
                            ));
                            echo $loc
                                ? htmlspecialchars(implode(', ', $loc))
                                : '—';
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($a->linkedin_url)): ?>
                                <a href="<?php echo htmlspecialchars(
                                    $a->linkedin_url
                                ); ?>"
                                   target="_blank"
                                   style="color:#1565C0;font-size:12px;">
                                    LinkedIn ↗
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo site_url(
                                'alumni/view/' . $a->id
                            ); ?>"
                               class="btn btn-primary btn-sm">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div style="padding:16px 24px;display:flex;
                            justify-content:center;gap:8px;">
                    <?php for ($i = 1;
                               $i <= $pagination['total_pages'];
                               $i++): ?>
                        <?php
                        $fp = array_merge($filters, array('page' => $i));
                        ?>
                        <a href="<?php echo site_url(
                            'alumni?' . http_build_query($fp)
                        ); ?>"
                           class="btn btn-sm <?php echo
                               ($i == $pagination['page'])
                               ? 'btn-primary' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>