<?php $page_title = ($action === 'edit') ? 'Edit Course' : 'Add Course'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📚 <?php echo ($action === 'edit') ? 'Edit Course' : 'Add Course'; ?></h2>
            <a href="<?php echo site_url('profile/courses'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/course/save'); ?>

                <?php if ($action === 'edit' && $course): ?>
                    <input type="hidden" name="id" value="<?php echo $course->id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Course Name *</label>
                    <input type="text" name="course_name" required
                           value="<?php echo set_value('course_name', isset($course->course_name) ? $course->course_name : ''); ?>"
                           placeholder="e.g., Advanced React Development, Agile Scrum Master">
                </div>

                <div class="form-group">
                    <label>Provider *</label>
                    <input type="text" name="provider" required
                           value="<?php echo set_value('provider', isset($course->provider) ? $course->provider : ''); ?>"
                           placeholder="e.g., Coursera, Udemy, LinkedIn Learning">
                </div>

                <div class="form-group">
                    <label>Course Page URL</label>
                    <input type="url" name="course_url"
                           value="<?php echo set_value('course_url', isset($course->course_url) ? $course->course_url : ''); ?>"
                           placeholder="https://www.coursera.org/learn/course-name">
                    <p class="form-hint">Link to the course page</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Completion Date *</label>
                        <input type="date" name="completion_date" required
                               value="<?php echo set_value('completion_date', isset($course->completion_date) ? $course->completion_date : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration"
                               value="<?php echo set_value('duration', isset($course->duration) ? $course->duration : ''); ?>"
                               placeholder="e.g., 40 hours, 6 weeks">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <?php echo ($action === 'edit') ? 'Update Course' : 'Add Course'; ?>
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>