<?php $page_title = 'Personal Information'; ?>
<?php $this->load->view('profile/css_header'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📋 Personal Information & Biography</h2>
            <a href="<?php echo site_url('profile'); ?>" class="btn btn-secondary btn-sm">← Back</a>
        </div>
        <div class="card-body">

            <?php if (validation_errors()): ?>
                <div class="alert alert-error"><?php echo validation_errors(); ?></div>
            <?php endif; ?>

            <?php echo form_open('profile/personal/save'); ?>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" 
                               value="<?php echo set_value('phone', isset($profile->phone) ? $profile->phone : ''); ?>"
                               placeholder="+44 7911 123456">
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" 
                               value="<?php echo set_value('date_of_birth', isset($profile->date_of_birth) ? $profile->date_of_birth : ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Biography</label>
                    <textarea name="biography" rows="5" 
                              placeholder="Tell us about yourself, your career journey, and your achievements..."
                    ><?php echo set_value('biography', isset($profile->biography) ? $profile->biography : ''); ?></textarea>
                    <p class="form-hint">Maximum 2000 characters</p>
                </div>

                <div class="form-group">
                    <label>LinkedIn Profile URL</label>
                    <input type="url" name="linkedin_url" 
                           value="<?php echo set_value('linkedin_url', isset($profile->linkedin_url) ? $profile->linkedin_url : ''); ?>"
                           placeholder="https://www.linkedin.com/in/yourprofile">
                    <p class="form-hint">Must start with https://</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" 
                               value="<?php echo set_value('city', isset($profile->city) ? $profile->city : ''); ?>"
                               placeholder="London">
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" 
                               value="<?php echo set_value('country', isset($profile->country) ? $profile->country : ''); ?>"
                               placeholder="United Kingdom">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Save Personal Information</button>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>