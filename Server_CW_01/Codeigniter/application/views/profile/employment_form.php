<?php $page_title = ($action === 'edit') ? 'Edit Employment' : 'Add Employment'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>💼 <?php echo ($action === 'edit') ? 'Edit Employment' : 'Add Employment'; ?></h2>
            <a href="<?php echo site_url('profile/employment'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/employment/save'); ?>

                <?php if ($action === 'edit' && $job): ?>
                    <input type="hidden" name="id" value="<?php echo $job->id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Job Title *</label>
                    <input type="text" name="job_title" required
                           value="<?php echo set_value('job_title', isset($job->job_title) ? $job->job_title : ''); ?>"
                           placeholder="e.g., Software Engineer, Marketing Manager">
                </div>

                <div class="form-group">
                    <label>Company Name *</label>
                    <input type="text" name="company_name" required
                           value="<?php echo set_value('company_name', isset($job->company_name) ? $job->company_name : ''); ?>"
                           placeholder="e.g., Google, Phantasmagoria Ltd">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"
                              placeholder="Describe your role and responsibilities..."
                    ><?php echo set_value('description', isset($job->description) ? $job->description : ''); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date *</label>
                        <input type="date" name="start_date" required
                               value="<?php echo set_value('start_date', isset($job->start_date) ? $job->start_date : ''); ?>">
                    </div>
                    <div class="form-group" id="end-date-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" id="end_date"
                               value="<?php echo set_value('end_date', isset($job->end_date) ? $job->end_date : ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_current" id="is_current" value="1"
                               <?php echo (isset($job->is_current) && $job->is_current) ? 'checked' : ''; ?>
                               onchange="toggleEndDate()">
                        <label for="is_current" style="margin-bottom:0; font-weight:normal;">I currently work here</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <?php echo ($action === 'edit') ? 'Update Employment' : 'Add Employment'; ?>
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
// Hide end date field when "currently working here" is checked
function toggleEndDate() {
    var checkbox = document.getElementById('is_current');
    var endDateGroup = document.getElementById('end-date-group');
    var endDateInput = document.getElementById('end_date');
    
    if (checkbox.checked) {
        endDateGroup.style.opacity = '0.5';
        endDateInput.disabled = true;
        endDateInput.value = '';
    } else {
        endDateGroup.style.opacity = '1';
        endDateInput.disabled = false;
    }
}

// Run on page load
toggleEndDate();
</script>

<?php $this->load->view('profile/footer'); ?>