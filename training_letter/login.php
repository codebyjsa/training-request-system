<?php
// This file is part of Moodle - https://moodle.org/
//
// Custom Institutional Login Page for GNDEC Training Request System.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}

require_once($CFG->libdir . '/authlib.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/login.php'));
$PAGE->set_title('Login | GNDEC Training & Placement Cell');
$PAGE->set_pagelayout('login');

// Ensure alternateloginurl is registered so all Moodle logouts and login redirects route to this page.
if (empty($CFG->alternateloginurl) || $CFG->alternateloginurl !== $CFG->wwwroot . '/local/training_letter/login.php') {
    set_config('alternateloginurl', $CFG->wwwroot . '/local/training_letter/login.php');
}

// If already logged in, redirect based on user capabilities.
if (isloggedin() && !isguestuser()) {
    if (has_capability('local/training_letter:reviewrequests', $context)) {
        redirect(new moodle_url('/local/training_letter/manage.php'));
    } else {
        redirect(new moodle_url('/local/training_letter/myrequests.php'));
    }
}

$error_msg = '';
$username_val = '';
$wantsurl = optional_param('wantsurl', '', PARAM_URL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = optional_param('username', '', PARAM_RAW);
    $password = optional_param('password', '', PARAM_RAW);
    $username_val = $username;

    if (empty($username) || empty($password)) {
        $error_msg = 'Please enter both username and password.';
    } else {
        // Authenticate using Moodle Core Authentication API.
        $user = authenticate_user_login($username, $password);

        if ($user) {
            complete_user_login($user);

            // Determine redirection target.
            if (!empty($wantsurl) && strpos($wantsurl, $CFG->wwwroot) === 0) {
                redirect($wantsurl);
            } else if (has_capability('local/training_letter:reviewrequests', $context)) {
                redirect(new moodle_url('/local/training_letter/manage.php'), 'Welcome to Training Management Dashboard', null, \core\output\notification::NOTIFY_SUCCESS);
            } else {
                redirect(new moodle_url('/local/training_letter/myrequests.php'), 'Welcome to GNDEC Training Request Portal', null, \core\output\notification::NOTIFY_SUCCESS);
            }
        } else {
            $error_msg = 'Invalid username or password. Please try again.';
        }
    }
}

$logourl = new moodle_url('/local/training_letter/pix/gndec_logo.png');
$moodleloginurl = new moodle_url('/login/index.php');

echo $OUTPUT->header();
?>

<style>
/* Remove Moodle Default 'Welcome to Moodle' side section */
.login-layout-left {
    display: none !important;
}
.login-layout-right {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
    background: transparent !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 30px 15px !important;
}
.login-layout-right-content,
#region-main-box,
.login-container {
    width: 100% !important;
    max-width: 1020px !important;
    margin: 0 auto !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}

/* Custom GNDEC Institutional Login Styling */
body {
    background: linear-gradient(135deg, #1b2838 0%, #0d1b2a 50%, #162436 100%) !important;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
#page-wrapper {
    background: transparent !important;
}
#page {
    background: transparent !important;
    margin-top: 0 !important;
    padding-top: 10px;
}
#page-header {
    display: none !important;
}
.gndec-login-container {
    width: 100%;
    max-width: 980px;
    margin: 20px auto;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
}
.gndec-login-hero {
    flex: 1 1 420px;
    background: linear-gradient(160deg, #7a1515 0%, #4a0c0c 100%);
    color: #ffffff;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}
.gndec-hero-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 24px;
}
.gndec-hero-logo {
    width: 88px;
    height: auto;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
}
.gndec-hero-title {
    font-size: 24px;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 12px;
    letter-spacing: -0.3px;
}
.gndec-hero-subtitle {
    font-size: 15px;
    opacity: 0.9;
    line-height: 1.5;
    margin-bottom: 24px;
}
.gndec-hero-features {
    list-style: none;
    padding: 0;
    margin: 0 0 24px 0;
}
.gndec-hero-features li {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 13.5px;
    opacity: 0.95;
}
.gndec-hero-features li i {
    width: 20px;
    color: #ffc107;
}
.gndec-hero-footer {
    font-size: 12px;
    opacity: 0.75;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 16px;
}

.gndec-login-form-pane {
    flex: 1 1 450px;
    padding: 48px 45px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #ffffff;
}
.gndec-form-header {
    margin-bottom: 28px;
}
.gndec-form-header h2 {
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 6px;
}
.gndec-form-header p {
    color: #666;
    font-size: 14px;
    margin: 0;
}
.gndec-input-group {
    margin-bottom: 20px;
}
.gndec-input-group label {
    display: block;
    font-weight: 600;
    font-size: 13.5px;
    color: #333;
    margin-bottom: 6px;
}
.gndec-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.gndec-input-icon {
    position: absolute;
    left: 14px;
    color: #888;
    font-size: 15px;
}
.gndec-input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 1.5px solid #dcdcdc;
    border-radius: 8px;
    font-size: 14.5px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.gndec-input:focus {
    border-color: #7a1515;
    box-shadow: 0 0 0 3px rgba(122, 21, 21, 0.15);
    outline: none;
}
.gndec-password-toggle {
    position: absolute;
    right: 14px;
    color: #888;
    cursor: pointer;
    font-size: 14px;
}
.gndec-btn-submit {
    width: 100%;
    padding: 13px;
    background: #7a1515;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.1s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.gndec-btn-submit:hover {
    background: #5f1010;
}
.gndec-btn-submit:active {
    transform: scale(0.99);
}
.gndec-alert-error {
    background: #fde8e8;
    border-left: 4px solid #e02424;
    color: #9b1c1c;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 13.5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.gndec-credentials-box {
    margin-top: 24px;
    padding: 12px 16px;
    background: #f8f9fa;
    border: 1px dashed #ced4da;
    border-radius: 8px;
    font-size: 12.5px;
    color: #495057;
}
.gndec-credentials-box strong {
    color: #212529;
}
.gndec-fallback-link {
    display: block;
    text-align: center;
    margin-top: 18px;
    font-size: 13px;
    color: #6c757d;
    text-decoration: none;
}
.gndec-fallback-link:hover {
    color: #7a1515;
    text-decoration: underline;
}
</style>

<div class="gndec-login-container">
    <!-- Left Hero Section -->
    <div class="gndec-login-hero">
        <div>
            <img src="<?php echo $logourl; ?>" alt="GNDEC Crest" class="gndec-hero-logo" />
            <div class="gndec-hero-title">GNDEC Training Request System</div>
            <div class="gndec-hero-subtitle">
                Training &amp; Placement Cell, Guru Nanak Dev Engineering College, Ludhiana
            </div>

            <ul class="gndec-hero-features">
                <li><i class="fa fa-file-text-o"></i> Software &amp; Industrial Training Letters</li>
                <li><i class="fa fa-check-circle-o"></i> Fast-Track Coordinator Endorsement</li>
                <li><i class="fa fa-download"></i> Verified Institutional PDF Generation</li>
                <li><i class="fa fa-id-card-o"></i> Pre-filled University Roll Number &amp; Profile</li>
            </ul>
        </div>

        <div class="gndec-hero-footer">
            An Autonomous College under UGC Act • Affiliated to IKGPTU • NBA Accredited
        </div>
    </div>

    <!-- Right Form Section -->
    <div class="gndec-login-form-pane">
        <div class="gndec-form-header">
            <h2>Sign In</h2>
            <p>Access the Training Request Letter Generator</p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div class="gndec-alert-error">
                <i class="fa fa-exclamation-triangle"></i>
                <span><?php echo s($error_msg); ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo new moodle_url('/local/training_letter/login.php'); ?>" autocomplete="on">
            <input type="hidden" name="wantsurl" value="<?php echo s($wantsurl); ?>" />

            <div class="gndec-input-group">
                <label for="username">Username or University Roll No.</label>
                <div class="gndec-input-wrapper">
                    <i class="fa fa-user gndec-input-icon"></i>
                    <input type="text" id="username" name="username" class="gndec-input"
                           value="<?php echo s($username_val); ?>"
                           placeholder="Enter your username or roll number" required autofocus />
                </div>
            </div>

            <div class="gndec-input-group">
                <label for="password">Password</label>
                <div class="gndec-input-wrapper">
                    <i class="fa fa-lock gndec-input-icon"></i>
                    <input type="password" id="password" name="password" class="gndec-input"
                           placeholder="Enter your password" required />
                    <i class="fa fa-eye gndec-password-toggle" id="togglePassword" title="Show/Hide Password"></i>
                </div>
            </div>

            <button type="submit" class="gndec-btn-submit">
                <span>Sign In to Portal</span>
                <i class="fa fa-arrow-right"></i>
            </button>
        </form>

        <div class="gndec-credentials-box">
            <i class="fa fa-info-circle text-primary"></i> <strong>Portal Logins:</strong><br>
            • Student: <code>jsaishere</code> &bull; Coordinator: <code>admin</code>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    if (toggle && passwordInput) {
        toggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        });
    }
});
</script>

<?php
echo $OUTPUT->footer();
