<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request letter view.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}

require_login();

$id = required_param('id', PARAM_INT);

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/letter.php', [
    'id' => $id,
]));
$PAGE->set_title('Training Request Letter');
$PAGE->set_heading('Training Request Letter');

global $DB, $USER;

// Fetch the request.
$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

// Students can view their own request.
// Coordinators can view any request.
if ((int)$request->userid !== (int)$USER->id) {
    require_capability(
        'local/training_letter:reviewrequests',
        $context
    );
}

// Only approved requests can generate an official letter.
if ($request->status !== 'approved') {
    redirect(
        new moodle_url('/local/training_letter/index.php'),
        'Your training request must be approved before the official letter is available.',
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

// Fetch student user details for email/phone.
$studentuser = $DB->get_record('user', ['id' => $request->userid], 'id, email, phone1, phone2');
$studentemail = !empty($studentuser->email) ? $studentuser->email : '';
$studentphone = !empty($request->phone) ? $request->phone : (!empty($studentuser->phone1) ? $studentuser->phone1 : '');

// Dynamic duration and semester to year mapping.
$duration_str = !empty($request->duration) ? trim($request->duration) : '6 Weeks';
$duration_upper = core_text::strtoupper($duration_str);
$duration_lower = core_text::strtolower($duration_str);

$semester = (int)$request->semester;
if ($semester <= 2) {
    $year_ordinal = '1st YEAR';
    $year_words = 'first';
} else if ($semester <= 4) {
    $year_ordinal = '2nd YEAR';
    $year_words = 'second';
} else if ($semester <= 6) {
    $year_ordinal = '3rd YEAR';
    $year_words = 'third';
} else {
    $year_ordinal = '4th YEAR';
    $year_words = 'final';
}

$refno = 'PTP/' . date('Y', $request->timemodified ?: time()) . '/' . str_pad($request->id, 4, '0', STR_PAD_LEFT);
$letterdate = userdate($request->timemodified ?: time(), '%d/%m/%Y');
$startdatestr = userdate($request->startdate, '%d %B %Y');

$logourl = new moodle_url('/local/training_letter/pix/gndec_logo.png');
$downloadurl = new moodle_url('/local/training_letter/download.php', ['id' => $request->id]);
$portalurl = new moodle_url('/local/training_letter/index.php');

echo $OUTPUT->header();
?>

<style>
.gndec-letter-wrapper {
    max-width: 860px;
    margin: 20px auto 40px;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
}
.gndec-letter-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}
.gndec-letter-sheet {
    background: #ffffff;
    border: 2px solid #000000;
    padding: 35px 42px;
    position: relative;
    box-shadow: 0 4px 18px rgba(0,0,0,0.12);
    line-height: 1.45;
    font-size: 14.5px;
    box-sizing: border-box;
}
.gndec-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 440px;
    opacity: 0.08;
    pointer-events: none;
    z-index: 1;
}
.gndec-letter-content {
    position: relative;
    z-index: 2;
}
.gndec-letter-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    text-align: center;
}
.gndec-header-logo {
    width: 92px;
    height: auto;
    flex-shrink: 0;
}
.gndec-header-text {
    flex-grow: 1;
}
.gndec-header-title {
    font-size: 19px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin: 0;
    color: #000;
}
.gndec-header-sub {
    font-size: 14.5px;
    font-weight: 700;
    margin: 3px 0;
}
.gndec-header-status {
    font-size: 11.5px;
    font-weight: 600;
    margin: 2px 0;
}
.gndec-header-autonomous {
    font-size: 12px;
    margin: 2px 0;
}
.gndec-header-web {
    font-size: 12px;
    margin: 2px 0;
}
.gndec-header-web a {
    color: #0044cc;
    text-decoration: underline;
}
.gndec-divider {
    border: none;
    border-top: 3.5px solid #801818;
    margin: 10px 0 16px 0;
    opacity: 1;
}
.gndec-meta-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 14px;
}
.gndec-to-block {
    margin-bottom: 14px;
    font-size: 14px;
    line-height: 1.35;
}
.gndec-subject {
    font-size: 14.5px;
    font-weight: 800;
    text-decoration: underline;
    margin: 14px 0 12px 0;
}
.gndec-paragraph {
    text-align: justify;
    margin-bottom: 12px;
    text-justify: inter-word;
}
.gndec-bullets {
    margin: 8px 0 14px 22px;
    padding: 0;
}
.gndec-bullets li {
    margin-bottom: 4px;
}
.gndec-recommendation {
    text-align: justify;
    margin: 14px 0 10px 0;
    line-height: 1.6;
}
.gndec-field-val {
    font-weight: bold;
    text-decoration: underline;
}
.gndec-note {
    font-size: 11.5px;
    margin-bottom: 14px;
    color: #222;
}
.gndec-closing {
    margin-top: 14px;
    margin-bottom: 20px;
}
.gndec-signatory {
    margin-top: 35px;
    line-height: 1.3;
}
.gndec-footer-divider {
    border: none;
    border-top: 3px solid #801818;
    margin: 24px 0 8px 0;
    opacity: 1;
}
.gndec-footer-info {
    text-align: center;
    font-size: 11.5px;
    line-height: 1.35;
}
.gndec-footer-info strong {
    font-size: 12px;
}

@media print {
    body * {
        visibility: hidden;
    }
    .gndec-letter-wrapper, .gndec-letter-wrapper * {
        visibility: visible;
    }
    .gndec-letter-wrapper {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .gndec-letter-actions {
        display: none !important;
    }
    .gndec-letter-sheet {
        border: 2px solid #000 !important;
        box-shadow: none !important;
        padding: 25px 30px !important;
    }
}
</style>

<div class="gndec-letter-wrapper">
    <div class="gndec-letter-actions">
        <a href="<?php echo $downloadurl; ?>" class="btn btn-primary">
            <i class="fa fa-download"></i> Download PDF
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa fa-print"></i> Print Letter
        </button>
        <a href="<?php echo $portalurl; ?>" class="btn btn-outline-secondary">
            Back to Portal
        </a>
    </div>

    <div class="gndec-letter-sheet">
        <img src="<?php echo $logourl; ?>" alt="GNDEC Watermark" class="gndec-watermark" />

        <div class="gndec-letter-content">
            <!-- Header Section -->
            <div class="gndec-letter-header">
                <img src="<?php echo $logourl; ?>" alt="GNDEC Logo" class="gndec-header-logo" />
                <div class="gndec-header-text">
                    <div class="gndec-header-title">GURU NANAK DEV ENGINEERING COLLEGE, LUDHIANA</div>
                    <div class="gndec-header-sub">(TRAINING AND PLACEMENT CELL)</div>
                    <div class="gndec-header-status">PUNJAB GOVERNMENT AIDED STATUS</div>
                    <div class="gndec-header-autonomous">An Autonomous college under UGC Act- 1956 [2(f) and 12(B)] - NBA Accredited</div>
                    <div class="gndec-header-web">(<a href="http://www.gndec.ac.in" target="_blank">http://www.gndec.ac.in</a>)</div>
                </div>
            </div>

            <hr class="gndec-divider" />

            <!-- Ref No & Date -->
            <div class="gndec-meta-row">
                <div>Ref.No. <?php echo s($refno); ?></div>
                <div>Dated: <?php echo s($letterdate); ?></div>
            </div>

            <!-- Recipient Block -->
            <div class="gndec-to-block">
                <div>To</div>
                <div style="font-weight: 600; margin-top: 3px;"><?php echo s($request->companyname); ?></div>
                <?php if (!empty($request->companyaddress)): ?>
                    <div style="margin-top: 2px; white-space: pre-line;"><?php echo s($request->companyaddress); ?></div>
                <?php endif; ?>
            </div>

            <!-- Subject Line -->
            <div class="gndec-subject">
                SUBJECT: <?php echo s($duration_upper); ?> INDUSTRIAL TRAINING FOR B.TECH. <?php echo s($year_ordinal); ?> STUDENTS.
            </div>

            <!-- Salutation & Greeting -->
            <div style="margin-bottom: 8px;">Sir,</div>
            <div style="margin-bottom: 12px;">Greetings from GNDEC, Ludhiana.</div>

            <!-- Paragraph 1 -->
            <div class="gndec-paragraph">
                Guru Nanak Dev Engineering College has emerged as one of the most prestigious engineering institute of North India over 62 years of its inception and is conducting B.TECH courses in seven disciplines as well as M.TECH, M.B.A. and Ph.D for meeting the research requirements of technical field.
            </div>

            <!-- Paragraph 2 -->
            <div class="gndec-paragraph">
                Since industrial training is equal in importance to theoretical foundation, the course curriculum is so designed that the students get exposure to practical aspects of their respective engineering branch. We are in a process of enrolling the <?php echo s($year_words); ?> year students of our institute to various Industrial Organisations for <?php echo s($duration_upper); ?> INDUSTRIAL TRAINING which is an essential component of their four year B.Tech programme.
            </div>

            <!-- Programme Bullet Points -->
            <div>The programme will be as under:</div>
            <ul class="gndec-bullets">
                <li>To get familiar with the setup and working of the organisation.</li>
                <li>Working on the given project, 2 week onwards</li>
                <li>Submission of Final Report.</li>
                <li>Submission of Daily Diary at the end of training .</li>
            </ul>

            <!-- Student Recommendation Sentence -->
            <div class="gndec-recommendation">
                We recommend our graduating student Mr./Ms. <span class="gndec-field-val"><?php echo s($request->studentname); ?></span> Roll No. <span class="gndec-field-val"><?php echo s($request->rollnumber); ?></span> Of B.Tech. (Branch) <span class="gndec-field-val"><?php echo s($request->branch); ?></span>, e-mail ID <span class="gndec-field-val"><?php echo s($studentemail); ?></span> Phone No. <span class="gndec-field-val"><?php echo s($studentphone); ?></span> to undergo <?php echo s($duration_lower); ?> training in your esteemed organization starting from <span class="gndec-field-val"><?php echo s($startdatestr); ?></span>
            </div>

            <!-- Note in parentheses -->
            <div class="gndec-note">
                (* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)
            </div>

            <!-- Concluding Paragraph -->
            <div class="gndec-paragraph">
                We would very much appreciate if the student can be accommodated for the training programme in your esteemed organisation. Our students are sincere and hard working and we are sure that they will put in their best efforts during the training program. Looking forward to the confirmation from your side.
            </div>

            <!-- Closing & Signatory -->
            <div class="gndec-closing">
                <div>Yours Sincerely</div>
                <div class="gndec-signatory">
                    <div style="font-weight: bold;">Prof. G.S. Sodhi</div>
                    <div>Training &amp; Placement Officer</div>
                </div>
            </div>

            <!-- Footer Divider & Contact Information -->
            <hr class="gndec-footer-divider" />
            <div class="gndec-footer-info">
                <div><strong>Contact Us At:</strong></div>
                <div><strong>Address:</strong> Training and Placement Cell, GNDEC, Gill Road, Ludhiana - 141006</div>
                <div><strong>E-mail:</strong> <a href="mailto:tpo@gndec.ac.in">tpo@gndec.ac.in</a> and <a href="mailto:gagan_sodhi76@yahoo.com">gagan_sodhi76@yahoo.com</a></div>
                <div><strong>Telefax:</strong> +91-161-2501106 <strong>Cell:</strong> +91-98722-19178, <strong>Phone No.</strong> 0161-5064510</div>
            </div>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();