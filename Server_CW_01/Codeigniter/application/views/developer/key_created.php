<?php $page_title = 'API Key Created'; ?>
<?php $this->load->view('profile/css_header'); ?>

<style>
    .secret-box { background: #fff3e0; border: 2px solid #FF9800; border-radius: 12px; padding: 24px; margin: 20px 0; }
    .copy-value { font-family: 'Courier New', monospace; background: #f5f5f5; padding: 12px; border-radius: 8px; 
                  font-size: 14px; word-break: break-all; display: block; margin: 8px 0; border: 1px solid #ddd; }
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>✅ API Key Generated Successfully!</h2>
        </div>
        <div class="card-body">

            <div class="secret-box">
                <h3 style="color: #e65100; margin-bottom: 12px;">⚠️ IMPORTANT: Save Your API Secret Now!</h3>
                <p style="color: #bf360c;">
                    The <strong>API Secret</strong> is shown <strong>only once</strong> and cannot be retrieved later.
                    Copy it now and store it securely.
                </p>
            </div>

            <div class="form-group">
                <label>API Key (Public Identifier)</label>
                <code class="copy-value"><?php echo htmlspecialchars($new_key['api_key']); ?></code>
                <p class="form-hint">This can be viewed anytime from your dashboard.</p>
            </div>

            <div class="form-group">
                <label style="color: #e65100;">🔐 API Secret (Save This Now!)</label>
                <code class="copy-value" style="background: #fff8e1; border-color: #FF9800; font-size: 16px;">
                    <?php echo htmlspecialchars($new_key['api_secret']); ?>
                </code>
                <p class="form-hint" style="color: #e65100;">
                    ⚠️ This secret will NOT be shown again. If you lose it, you'll need to generate a new API key.
                </p>
            </div>

            <hr style="margin: 24px 0;">

            <h3>How to Use</h3>
            <p style="margin: 12px 0;">1. First, authenticate to get a bearer token:</p>
            <pre style="background:#263238;color:#80CBC4;padding:16px;border-radius:8px;overflow-x:auto;font-size:13px;">
POST /api/auth/client-login
Content-Type: application/json

{
    "api_key": "<?php echo htmlspecialchars($new_key['api_key']); ?>",
    "api_secret": "YOUR_API_SECRET"
}

Response:
{
    "bearer_token": "abc123...",
    "expires_in": 86400
}</pre>

            <p style="margin: 12px 0;">2. Use the bearer token in all API requests:</p>
            <pre style="background:#263238;color:#80CBC4;padding:16px;border-radius:8px;overflow-x:auto;font-size:13px;">
GET /api/alumni-of-the-day
Authorization: Bearer abc123...

Response:
{
    "status": "success",
    "data": { ... alumni profile ... }
}</pre>

            <a href="<?php echo site_url('developer'); ?>" class="btn btn-primary" style="margin-top: 20px;">
                ← Back to Developer Dashboard
            </a>

        </div>
    </div>
</div>

<?php $this->load->view('profile/footer'); ?>