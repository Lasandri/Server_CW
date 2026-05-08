<?php $page_title = ($action === 'edit') ? 'Edit Licence' : 'Add Licence'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🪪 <?php echo ($action === 'edit') ? 'Edit Licence' : 'Add Licence'; ?></h2>
            <a href="<?php echo site_url('profile/licences'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">
            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/licence/save'); ?>

                <?php if ($action === 'edit' && $licence): ?>
                    <input type="hidden" name="id" value="<?php echo $licence->id; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Licence Name *</label>
                    <input type="text" name="licence_name" required
                           value="<?php echo set_value('licence_name', isset($licence->licence_name) ? $licence->licence_name : ''); ?>"
                           placeholder="e.g., Chartered Engineer, Medical Licence">
                </div>

                <div class="form-group">
                    <label>Issuing Body *</label>
                    <input type="text" name="issuing_body" required
                           value="<?php echo set_value('issuing_body', isset($licence->issuing_body) ? $licence->issuing_body : ''); ?>"
                           placeholder="e.g., Engineering Council, GMC">
                </div>

                <div class="form-group">
                    <label>Licence Awarding Body URL</label>
                    <input type="url" name="licence_url"
                           value="<?php echo set_value('licence_url', isset($licence->licence_url) ? $licence->licence_url : ''); ?>"
                           placeholder="https://www.engc.org.uk">
                    <p class="form-hint">Link to the licence awarding body website</p>
                </div>

                <div class="form-group">
                    <label>Licence Number</label>
                    <input type="text" name="licence_number"
                           value="<?php echo set_value('licence_number', isset($licence->licence_number) ? $licence->licence_number : ''); ?>"
                           placeholder="e.g., LIC-2024-001234">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Issue Date *</label>
                        <input type="date" name="issue_date" required
                               value="<?php echo set_value('issue_date', isset($licence->issue_date) ? $licence->issue_date : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date"
                               value="<?php echo set_value('expiry_date', isset($licence->expiry_date) ? $licence->expiry_date : ''); ?>">
                        <p class="form-hint">Leave empty if no expiry</p>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <?php echo ($action === 'edit') ? 'Update Licence' : 'Add Licence'; ?>
                </button>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>