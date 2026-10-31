<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request letter preview page.

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
$PAGE->set_url(new moodle_url('/local/training_letter/letter.php', ['id' => $id]));
$PAGE->set_title(get_string('lettertitle', 'local_training_letter'));
$PAGE->set_heading(get_string('lettertitle', 'local_training_letter'));

global $DB, $USER;

// Fetch the request record.
$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

// Access check: Students can only view their own request.
// Coordinators with reviewrequests capability can view any request.
if ((int)$request->userid !== (int)$USER->id) {
    require_capability('local/training_letter:reviewrequests', $context);
}

// Only approved requests can generate an official letter.
if ($request->status !== 'approved') {
    redirect(
        new moodle_url('/local/training_letter/myrequests.php'),
        get_string('letter_not_approved_warning', 'local_training_letter'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

// Fetch student user details for email/phone.
$studentuser = $DB->get_record('user', ['id' => $request->userid], 'id, email, phone1, phone2');
$studentemail = !empty($studentuser->email) ? $studentuser->email : '';
$studentphone = !empty($request->phone) ? $request->phone : (!empty($studentuser->phone1) ? $studentuser->phone1 : '');

$trainingtype = !empty($request->trainingtype) ? $request->trainingtype : 'software';
$duration_str = !empty($request->duration) ? trim($request->duration) : ($trainingtype === 'software' ? '6 Weeks' : '6 Months');
$duration_upper = core_text::strtoupper($duration_str);
$duration_lower = core_text::strtolower($duration_str);

$semester = (int)$request->semester;
if ($semester <= 2) {
    $sem_label = '1st/2nd SEMESTER';
} else if ($semester <= 4) {
    $sem_label = '3rd/4th SEMESTER';
} else if ($semester <= 6) {
    $sem_label = '5th/6th SEMESTER';
} else {
    $sem_label = '7th/8th SEMESTER';
}

$refno = 'PTP/' . date('Y', $request->timemodified ?: time()) . '/' . str_pad($request->id, 4, '0', STR_PAD_LEFT);
$letterdate = userdate($request->timemodified ?: time(), '%d/%m/%Y');
$startdatestr = userdate($request->startdate, '%d %B %Y');

$logourl = new moodle_url('/local/training_letter/pix/gndec_logo.png');
$downloadurl = new moodle_url('/local/training_letter/download.php', ['id' => $request->id]);
$portalurl = new moodle_url('/local/training_letter/myrequests.php');

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
    margin: 8px 0 14px 24px;
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
            <i class="fa fa-download"></i> <?php echo get_string('downloadpdf', 'local_training_letter'); ?>
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa fa-print"></i> <?php echo get_string('printletter', 'local_training_letter'); ?>
        </button>
        <a href="<?php echo $portalurl; ?>" class="btn btn-outline-secondary">
            <?php echo get_string('backtoportal', 'local_training_letter'); ?>
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

            <?php if ($trainingtype === 'software'): ?>
                <!-- Official Software Training Format -->
                <div class="gndec-subject">
                    SUBJECT: REQUEST FOR SOFTWARE TRAINING OF B.TECH <?php echo s($sem_label); ?> STUDENTS
                </div>

                <div style="margin-bottom: 8px;">Sir,</div>
                <div style="margin-bottom: 12px;">Greetings from GNDEC, Ludhiana.</div>

                <div class="gndec-paragraph">
                    Guru Nanak Dev Engineering College has emerged as one of the most prestigious engineering institute of North India over the 55 years of its inception and is conducting B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for meeting the research requirement of technical field.
                </div>

                <div class="gndec-paragraph">
                    Since software training has become a necessity in today’s scenario, the course curriculum is so designed that students get exposure to different kinds of software for designing and other purposes. We are in a process of enrolling the final year students of our institute to various Organisations for SOFTWARE TRAINING (ATLEAST 6 WEEKS) which is an essential component of their four year B.Tech programme.
                </div>

                <div>The programme will be as under:</div>
                <ul class="gndec-bullets">
                    <li>Getting familiar with various software technologies related to the respective disciplines.</li>
                    <li>Preparation and submission of synopsis.</li>
                    <li>Working and acquiring proficiency in some software technology/application(s).</li>
                    <li>Submission of Mid Term and Final Report.</li>
                    <li>Submission of Daily Diary at the end of training maintained &amp; checked by the company representative.</li>
                </ul>

                <div class="gndec-recommendation">
                    We recommend our graduating student Mr./Ms. <span class="gndec-field-val"><?php echo s($request->studentname); ?></span> Roll no. <span class="gndec-field-val"><?php echo s($request->rollnumber); ?></span> of B.Tech (Branch) <span class="gndec-field-val"><?php echo s($request->branch); ?></span> ,Email Id <span class="gndec-field-val"><?php echo s($studentemail); ?></span> Phone no. <span class="gndec-field-val"><?php echo s($studentphone); ?></span> to undergo software training in your esteemed organization starting from <span class="gndec-field-val"><?php echo s($startdatestr); ?></span>.
                </div>

                <div class="gndec-note">
                    (* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)
                </div>

                <div class="gndec-paragraph">
                    We would highly appreciate if the student can be accommodated for the training programme. Our students are sincere and hard working and we are sure that they will put in their best efforts during the training program. Looking for the confirmation from your side.
                </div>

                <div class="gndec-closing">
                    <div>Yours Sincerely</div>
                    <div class="gndec-signatory">
                        <div style="font-weight: bold;">(Faculty Coordinator)</div>
                        <div>Training &amp; Placement Cell</div>
                    </div>
                </div>

                <hr class="gndec-footer-divider" />
                <div class="gndec-footer-info">
                    <div><strong>Contact Us At:</strong></div>
                    <div><strong>Address:</strong> Training and Placement Cell, GNDEC, Gill Road, Ludhiana – 141006</div>
                    <div><strong>E-mail:</strong> <a href="mailto:tpo@gndec.ac.in">tpo@gndec.ac.in</a> and <a href="mailto:mannkulvinder@yahoo.com">mannkulvinder@yahoo.com</a></div>
                    <div><strong>Telefax:</strong> +91-161-2501106 <strong>Cell:</strong> +91-9915507920, <strong>Phone No.</strong> 0161 – 5064510</div>
                </div>

            <?php else: ?>
                <!-- Official Industrial Training Format -->
                <div class="gndec-subject">
                    SUBJECT: REQUEST FOR INDUSTRIAL TRAINING OF B.TECH <?php echo s($sem_label); ?> STUDENTS
                </div>

                <div style="margin-bottom: 8px;">Sir,</div>
                <div style="margin-bottom: 12px;">Greetings from GNDEC, Ludhiana.</div>

                <div class="gndec-paragraph">
                    Guru Nanak Dev Engineering College has emerged as one of the most prestigious engineering institute of North India over the 62 years of its inception and is conducting B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for meeting the research requirement of technical field.
                </div>

                <div class="gndec-paragraph">
                    Since practical training is equal in importance to theoretical foundation, the course curriculum is so designed that the students get exposure to practical aspects of their respective engineering branch. We are in a process of enrolling the final year students of our institute to various Industrial Organisations for INDUSTRIAL TRAINING (6 MONTHS) which is an essential component of their four year B.Tech programme.
                </div>

                <div>The programme will be as under:</div>
                <ul class="gndec-bullets">
                    <li>To get familiar with the setup and working of the organisation.</li>
                    <li>Preparation and submission of synopsis.</li>
                    <li>Working on the given project- provided by the company.</li>
                    <li>Submission of Mid Term and Final Report.</li>
                    <li>Submission of Daily Diary at the end of training maintained &amp; checked by the company representative.</li>
                </ul>

                <div class="gndec-recommendation">
                    We recommend our graduating student Mr./Ms. <span class="gndec-field-val"><?php echo s($request->studentname); ?></span> Roll no. <span class="gndec-field-val"><?php echo s($request->rollnumber); ?></span> of B.Tech (Branch) <span class="gndec-field-val"><?php echo s($request->branch); ?></span> ,Email Id <span class="gndec-field-val"><?php echo s($studentemail); ?></span> Phone no. <span class="gndec-field-val"><?php echo s($studentphone); ?></span> to undergo Industrial training in your esteemed organization starting from <span class="gndec-field-val"><?php echo s($startdatestr); ?></span>.
                </div>

                <div class="gndec-note">
                    (* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)
                </div>

                <div class="gndec-paragraph">
                    We would highly appreciate if the student can be accommodated for the training programme. Our students are sincere and hard working and we are sure that they will put in their best efforts during the training program. Looking for the confirmation from your side.
                </div>

                <div class="gndec-closing">
                    <div>Yours Sincerely</div>
                    <div class="gndec-signatory">
                        <div style="font-weight: bold;">Prof. G.S. Sodhi</div>
                        <div>Training &amp; Placement Officer</div>
                    </div>
                </div>

                <hr class="gndec-footer-divider" />
                <div class="gndec-footer-info">
                    <div><strong>Contact Us At:</strong></div>
                    <div><strong>Address:</strong> Training and Placement Cell, GNDEC, Gill Road, Ludhiana - 141006</div>
                    <div><strong>E-mail:</strong> <a href="mailto:tpo@gndec.ac.in">tpo@gndec.ac.in</a> and <a href="mailto:gagan_sodhi76@yahoo.com">gagan_sodhi76@yahoo.com</a></div>
                    <div><strong>Telefax:</strong> +91-161-2501106 <strong>Cell:</strong> +91-98722-19178, <strong>Phone No.</strong> 0161 – 5064510</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();