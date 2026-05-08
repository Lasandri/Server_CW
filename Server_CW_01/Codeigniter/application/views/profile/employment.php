<?php $page_title = 'Employment History'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>💼 Employment History</h2>
            <div>
                <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
                <a href="<?php echo site_url('profile/employment/add'); ?>" class="btn btn-primary btn-sm">+ Add Job</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($employment)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No employment history added yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Job Title</th><th>Company</th><th>Start</th><th>End</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employment as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e->job_title); ?></td>
                            <td><?php echo htmlspecialchars($e->company_name); ?></td>
                            <td><?php echo date('M Y', strtotime($e->start_date)); ?></td>
                            <td>
                                <?php if ($e->is_current): ?>
                                    <span style="background:#e8f5e9; color:#2e7d32; padding:2px 8px; border-radius:4px; font-size:12px;">Current</span>
                                <?php elseif ($e->end_date): ?>
                                    <?php echo date('M Y', strtotime($e->end_date)); ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo site_url('profile/employment/edit/' . $e->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="<?php echo site_url('profile/employment/delete/' . $e->id); ?>" class="btn btn-danger" onclick="return confirm('Delete this employment record?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>