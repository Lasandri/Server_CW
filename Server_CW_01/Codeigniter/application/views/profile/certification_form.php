<?php $page_title = ($action === 'edit') ? 'Edit Certification' : 'Add Certification'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📜 <?php echo ($action === 'edit') ? 'Edit Certification' : 'Add Certification'; ?></h2>
            <a href="<?php echo site_url('profile/certifications'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/certification/save'); ?>

                <?php if ($action === 'edit' && $cert): ?>
                    <input type="hidden" name="id" value="<?php echo $cert->id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Certification Name *</label>
                    <input type="text" name="certification_name" required
                           value="<?php echo set_value('certification_name', isset($cert->certification_name) ? $cert->certification_name : ''); ?>"
                           placeholder="e.g., AWS Solutions Architect, PMP">
                </div>

                <div class="form-group">
                    <label>Issuing Organization *</label>
                    <input type="text" name="issuing_organization" required
                           value="<?php echo set_value('issuing_organization', isset($cert->issuing_organization) ? $cert->issuing_organization : ''); ?>"
                           placeholder="e.g., Amazon Web Services, PMI">
                </div>

                <div class="form-group">
                    <label>Certification URL</label>
                    <input type="url" name="certification_url"
                           value="<?php echo set_value('certification_url', isset($cert->certification_url) ? $cert->certification_url : ''); ?>"
                           placeholder="https://www.example.com/certification-page">
                    <p class="form-hint">Link to the certification course page</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Completion Date *</label>
                        <input type="date" name="completion_date" required
                               value="<?php echo set_value('completion_date', isset($cert->completion_date) ? $cert->completion_date : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date"
                               value="<?php echo set_value('expiry_date', isset($cert->expiry_date) ? $cert->expiry_date : ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Credential ID</label>
                    <input type="text" name="credential_id"
                           value="<?php echo set_value('credential_id', isset($cert->credential_id) ? $cert->credential_id : ''); ?>"
                           placeholder="e.g., ABC-123-XYZ">
                </div>

                <button type="submit" class="btn btn-success">
                    <?php echo ($action === 'edit') ? 'Update Certification' : 'Add Certification'; ?>
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>