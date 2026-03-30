<?php $page_title = 'Certifications'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>📜 Professional Certifications</h2>
            <div>
                <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
                <a href="<?php echo site_url('profile/certification/add'); ?>" class="btn btn-primary btn-sm">+ Add Certification</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($certifications)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No certifications added yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Certification</th><th>Issuer</th><th>Completed</th><th>URL</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certifications as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c->certification_name); ?></td>
                            <td><?php echo htmlspecialchars($c->issuing_organization); ?></td>
                            <td><?php echo date('M Y', strtotime($c->completion_date)); ?></td>
                            <td>
                                <?php if (!empty($c->certification_url)): ?>
                                    <a href="<?php echo htmlspecialchars($c->certification_url); ?>" target="_blank" class="url-link">View ↗</a>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo site_url('profile/certification/edit/' . $c->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="<?php echo site_url('profile/certification/delete/' . $c->id); ?>" class="btn btn-danger" onclick="return confirm('Delete this certification?');">Delete</a>
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