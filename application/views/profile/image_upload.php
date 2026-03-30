<?php $page_title = 'Profile Image'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📷 Profile Image</h2>
            <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body" style="text-align:center;">

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Current Image -->
            <?php if ($profile && !empty($profile->profile_image)): ?>
                <img src="<?php echo base_url($profile->profile_image); ?>" class="profile-img" alt="Current profile" style="margin-bottom:20px;">
                <p style="color:#888; font-size:13px; margin-bottom:20px;">Current profile image</p>
            <?php else: ?>
                <div class="profile-img-placeholder" style="margin:0 auto 20px;">👤</div>
                <p style="color:#888; font-size:13px; margin-bottom:20px;">No profile image uploaded yet</p>
            <?php endif; ?>

            <!-- Upload Form (must use multipart) -->
            <?php echo form_open_multipart('profile/image/upload'); ?>

                <div class="form-group" style="text-align:left;">
                    <label>Select Image</label>
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/gif" required>
                    <p class="form-hint">Allowed: JPG, JPEG, PNG, GIF. Max size: 2MB. Max dimensions: 2000x2000px</p>
                </div>

                <button type="submit" class="btn btn-success">Upload Image</button>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>