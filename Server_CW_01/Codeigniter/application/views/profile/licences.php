<?php $page_title = 'Licences'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>🪪 Professional Licences</h2>
            <div>
                <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
                <a href="<?php echo site_url('profile/licence/add'); ?>" class="btn btn-primary btn-sm">+ Add Licence</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($licences)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No licences added yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Licence</th><th>Issuing Body</th><th>Issued</th><th>Expires</th><th>URL</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($licences as $l): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($l->licence_name); ?></td>
                            <td><?php echo htmlspecialchars($l->issuing_body); ?></td>
                            <td><?php echo date('M Y', strtotime($l->issue_date)); ?></td>
                            <td><?php echo $l->expiry_date ? date('M Y', strtotime($l->expiry_date)) : 'No expiry'; ?></td>
                            <td>
                                <?php if (!empty($l->licence_url)): ?>
                                    <a href="<?php echo htmlspecialchars($l->licence_url); ?>" target="_blank" class="url-link">View ↗</a>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo site_url('profile/licence/edit/' . $l->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="<?php echo site_url('profile/licence/delete/' . $l->id); ?>" class="btn btn-danger" onclick="return confirm('Delete this licence?');">Delete</a>
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