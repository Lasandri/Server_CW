<?php $page_title = 'Generate API Key'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🔑 Generate New API Key</h2>
            <a href="<?php echo site_url('developer'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">

            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('developer/create_save'); ?>

                <div class="form-group">
                    <label>Client Application Name *</label>
                    <input type="text" name="client_name" required
                           value="<?php echo set_value('client_name'); ?>"
                           placeholder="e.g., My AR App, Mobile Client, Dashboard Widget">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="client_description" rows="3"
                              placeholder="Describe what this client will do..."
                    ><?php echo set_value('client_description'); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Client Type *</label>
                    <select name="client_type" required>
                        <option value="web">Web Application</option>
                        <option value="mobile">Mobile App</option>
                        <option value="desktop">Desktop App</option>
                        <option value="ar">AR Client</option>
                        <option value="iot">IoT Device</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Permissions (Scopes)</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                        <label style="font-weight:normal;display:flex;align-items:center;gap:4px;">
                            <input type="checkbox" name="scopes[]" value="read" checked> Read (view alumni data)
                        </label>
                        <label style="font-weight:normal;display:flex;align-items:center;gap:4px;">
                            <input type="checkbox" name="scopes[]" value="alumni_of_day"> Alumni of the Day
                        </label>
                        <label style="font-weight:normal;display:flex;align-items:center;gap:4px;">
                            <input type="checkbox" name="scopes[]" value="profiles"> View Profiles
                        </label>
                        <label style="font-weight:normal;display:flex;align-items:center;gap:4px;">
                            <input type="checkbox" name="scopes[]" value="bidding"> Bidding Data
                        </label>
                    </div>
                    <p class="form-hint">Select what data this client can access</p>
                </div>

                <button type="submit" class="btn btn-success">🔑 Generate API Key</button>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>