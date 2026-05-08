<?php $page_title = 'Degrees'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>🎓 Degrees</h2>
            <div>
                <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
                <a href="<?php echo site_url('profile/degree/add'); ?>" class="btn btn-primary btn-sm">+ Add Degree</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($degrees)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No degrees added yet. Click "Add Degree" to get started.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Degree</th>
                            <th>Field</th>
                            <th>University</th>
                            <th>Completed</th>
                            <th>URL</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($degrees as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d->degree_title); ?></td>
                            <td><?php echo htmlspecialchars($d->field_of_study); ?></td>
                            <td><?php echo htmlspecialchars($d->university_name); ?></td>
                            <td><?php echo date('M Y', strtotime($d->completion_date)); ?></td>
                            <td>
                                <?php if (!empty($d->degree_url)): ?>
                                    <a href="<?php echo htmlspecialchars($d->degree_url); ?>" target="_blank" class="url-link">View ↗</a>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo site_url('profile/degree/edit/' . $d->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="<?php echo site_url('profile/degree/delete/' . $d->id); ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this degree?');">Delete</a>
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