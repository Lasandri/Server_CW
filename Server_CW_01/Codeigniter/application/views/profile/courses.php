<?php $page_title = 'Professional Courses'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($this->session->flashdata('success')); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>📚 Professional Courses</h2>
            <div>
                <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
                <a href="<?php echo site_url('profile/course/add'); ?>" class="btn btn-primary btn-sm">+ Add Course</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($courses)): ?>
                <p style="color:#888; text-align:center; padding:20px;">No courses added yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr><th>Course</th><th>Provider</th><th>Completed</th><th>Duration</th><th>URL</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c->course_name); ?></td>
                            <td><?php echo htmlspecialchars($c->provider); ?></td>
                            <td><?php echo date('M Y', strtotime($c->completion_date)); ?></td>
                            <td><?php echo $c->duration ? htmlspecialchars($c->duration) : '—'; ?></td>
                            <td>
                                <?php if (!empty($c->course_url)): ?>
                                    <a href="<?php echo htmlspecialchars($c->course_url); ?>" target="_blank" class="url-link">View ↗</a>
                                <?php else: ?>
                                    <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo site_url('profile/course/edit/' . $c->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="<?php echo site_url('profile/course/delete/' . $c->id); ?>" class="btn btn-danger" onclick="return confirm('Delete this course?');">Delete</a>
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