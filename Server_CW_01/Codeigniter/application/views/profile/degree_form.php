<?php $page_title = ($action === 'edit') ? 'Edit Degree' : 'Add Degree'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🎓 <?php echo ($action === 'edit') ? 'Edit Degree' : 'Add New Degree'; ?></h2>
            <a href="<?php echo site_url('profile/degrees'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">

            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/degree/save'); ?>

                <?php if ($action === 'edit' && $degree): ?>
                    <input type="hidden" name="id" value="<?php echo $degree->id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Degree Title *</label>
                    <input type="text" name="degree_title" required
                           value="<?php echo set_value('degree_title', isset($degree->degree_title) ? $degree->degree_title : ''); ?>"
                           placeholder="e.g., Bachelor of Science, Master of Arts">
                </div>

                <div class="form-group">
                    <label>Field of Study *</label>
                    <input type="text" name="field_of_study" required
                           value="<?php echo set_value('field_of_study', isset($degree->field_of_study) ? $degree->field_of_study : ''); ?>"
                           placeholder="e.g., Computer Science, Business Administration">
                </div>

                <div class="form-group">
                    <label>University Name *</label>
                    <input type="text" name="university_name" required
                           value="<?php echo set_value('university_name', isset($degree->university_name) ? $degree->university_name : ''); ?>"
                           placeholder="e.g., University of Eastminster">
                </div>

                <div class="form-group">
                    <label>Official Degree Page URL</label>
                    <input type="url" name="degree_url"
                           value="<?php echo set_value('degree_url', isset($degree->degree_url) ? $degree->degree_url : ''); ?>"
                           placeholder="https://www.eastminster.ac.uk/courses/bsc-computer-science">
                    <p class="form-hint">Link to the official university degree page</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Completion Date *</label>
                        <input type="date" name="completion_date" required
                               value="<?php echo set_value('completion_date', isset($degree->completion_date) ? $degree->completion_date : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Grade / Classification</label>
                        <input type="text" name="grade"
                               value="<?php echo set_value('grade', isset($degree->grade) ? $degree->grade : ''); ?>"
                               placeholder="e.g., First Class Honours, 2:1, Distinction">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <?php echo ($action === 'edit') ? 'Update Degree' : 'Add Degree'; ?>
                </button>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>