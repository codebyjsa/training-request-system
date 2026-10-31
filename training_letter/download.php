<?php
// This file is part of Moodle - https://moodle.org/
//
// Download training request letter as official PDF.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}

require_once($CFG->libdir . '/pdflib.php');

require_login();

$id = required_param('id', PARAM_INT);

$context = context_system::instance();

global $DB, $USER;

$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

// Access check: Students may download their own letter; coordinators may download any.
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

// Create the PDF instance.
$pdf = new pdf('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('GNDEC Training Request System');
$pdf->SetAuthor($request->studentname);
$pdf->SetTitle('Official Training Letter - ' . $request->studentname);

// Set margins: 16mm left/right, 14mm top, 14mm bottom.
$pdf->SetMargins(16, 14, 16);
$pdf->SetAutoPageBreak(false); // Single official page
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

// Outer black border around entire page (A4 is 210mm x 297mm).
$pdf->SetLineStyle(['width' => 0.5, 'color' => [0, 0, 0]]);
$pdf->Rect(10, 10, 190, 277);

// Watermark in center background.
$logopath = __DIR__ . '/pix/gndec_logo.png';
if (file_exists($logopath)) {
    $pdf->SetAlpha(0.08);
    $pdf->Image($logopath, 55, 95, 100, 0, 'PNG', '', '', false, 300, '', false, false, 0);
    $pdf->SetAlpha(1.0);
}

// Logo in header.
if (file_exists($logopath)) {
    $pdf->Image($logopath, 15, 14, 22, 24, 'PNG', '', '', false, 300, '', false, false, 0);
}

// Top Divider Line (dark maroon: RGB 128, 24, 24).
$pdf->SetLineStyle(['width' => 0.9, 'color' => [128, 24, 24]]);
$pdf->Line(14, 41, 196, 41);

// Bottom Divider Line (dark maroon).
$pdf->Line(14, 270, 196, 270);

$pdf->SetFont('times', '', 10);

$studentname = s($request->studentname);
$rollnumber = s($request->rollnumber);
$branch = s($request->branch);
$companyname = s($request->companyname);
$companyaddress = !empty($request->companyaddress) ? nl2br(s($request->companyaddress)) : '';

if ($trainingtype === 'software') {
    // Official GNDEC Software Training Content.
    $subject_line = 'REQUEST FOR SOFTWARE TRAINING OF B.TECH ' . s($sem_label) . ' STUDENTS';
    $years_inception = '55';
    $p2_text = 'Since software training has become a necessity in today’s scenario, the course curriculum is so designed that students get exposure to different kinds of software for designing and other purposes. We are in a process of enrolling the final year students of our institute to various Organisations for <b>SOFTWARE TRAINING (ATLEAST 6 WEEKS)</b> which is an essential component of their four year B.Tech programme.';
    $bullets_html = '
    <li>Getting familiar with various software technologies related to the respective disciplines.</li>
    <li>Preparation and submission of synopsis.</li>
    <li>Working and acquiring proficiency in some software technology/application(s).</li>
    <li>Submission of Mid Term and Final Report.</li>
    <li>Submission of Daily Diary at the end of training maintained &amp; checked by the company representative.</li>';
    $training_word = 'software';
    $signatory_html = '
    <b>(Faculty Coordinator)</b><br>
    Training &amp; Placement Cell';
    $footer_emails = 'tpo@gndec.ac.in and mannkulvinder@yahoo.com';
    $footer_cell = '+91-9915507920';
} else {
    // Official GNDEC Industrial Training Content.
    $subject_line = 'REQUEST FOR INDUSTRIAL TRAINING OF B.TECH ' . s($sem_label) . ' STUDENTS';
    $years_inception = '62';
    $p2_text = 'Since practical training is equal in importance to theoretical foundation, the course curriculum is so designed that the students get exposure to practical aspects of their respective engineering branch. We are in a process of enrolling the final year students of our institute to various Industrial Organisations for <b>INDUSTRIAL TRAINING (6 MONTHS)</b> which is an essential component of their four year B.Tech programme.';
    $bullets_html = '
    <li>To get familiar with the setup and working of the organisation.</li>
    <li>Preparation and submission of synopsis.</li>
    <li>Working on the given project- provided by the company.</li>
    <li>Submission of Mid Term and Final Report.</li>
    <li>Submission of Daily Diary at the end of training maintained &amp; checked by the company representative.</li>';
    $training_word = 'Industrial';
    $signatory_html = '
    <b>Prof. G.S. Sodhi</b><br>
    Training &amp; Placement Officer';
    $footer_emails = 'tpo@gndec.ac.in and gagan_sodhi76@yahoo.com';
    $footer_cell = '+91-98722-19178';
}

$html = '
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="15%"></td>
        <td width="85%" align="center">
            <span style="font-size: 13.5pt; font-weight: bold; font-family: times;">GURU NANAK DEV ENGINEERING COLLEGE, LUDHIANA</span><br>
            <span style="font-size: 10.5pt; font-weight: bold; font-family: times;">(TRAINING AND PLACEMENT CELL)</span><br>
            <span style="font-size: 7.5pt; font-weight: bold;">PUNJAB GOVERNMENT AIDED STATUS</span><br>
            <span style="font-size: 8.5pt;">An Autonomous college under UGC Act- 1956 [2(f) and 12(B)] - NBA Accredited</span><br>
            <span style="font-size: 8.5pt; color: #0033aa;">(<a href="http://www.gndec.ac.in" style="color: #0033aa; text-decoration: underline;">http://www.gndec.ac.in</a>)</span>
        </td>
    </tr>
</table>

<br><br>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="55%" align="left"><b>Ref.No. ' . s($refno) . '</b></td>
        <td width="45%" align="right"><b>Dated: ' . s($letterdate) . '</b></td>
    </tr>
</table>

<div style="font-size: 9.5pt; line-height: 1.25;">
    To<br>
    <b>' . $companyname . '</b><br>
    ' . ($companyaddress ? $companyaddress . '<br>' : '') . '
</div>

<p align="left" style="font-size: 10pt;">
    <b><u>SUBJECT: ' . $subject_line . '</u></b>
</p>

<div style="font-size: 9.5pt; line-height: 1.2;">
    Sir,<br><br>
    Greetings from GNDEC, Ludhiana.<br>
</div>

<p align="justify" style="font-size: 9.3pt; line-height: 1.25;">
Guru Nanak Dev Engineering College has emerged as one of the most prestigious engineering institute of North India over the ' . $years_inception . ' years of its inception and is conducting B.Tech. in seven disciplines as well as M.Tech., MBA and PhD. for meeting the research requirement of technical field.
</p>

<p align="justify" style="font-size: 9.3pt; line-height: 1.25;">
' . $p2_text . '
</p>

<div style="font-size: 9.3pt; line-height: 1.2;">
The programme will be as under:
</div>
<ul style="font-size: 9.2pt; line-height: 1.22;">
' . $bullets_html . '
</ul>

<p align="justify" style="font-size: 9.3pt; line-height: 1.3;">
We recommend our graduating student Mr./Ms. <u>&nbsp;<b>' . $studentname . '</b>&nbsp;</u> Roll no. <u>&nbsp;<b>' . $rollnumber . '</b>&nbsp;</u> of B.Tech (Branch) <u>&nbsp;<b>' . $branch . '</b>&nbsp;</u> ,Email Id <u>&nbsp;<b>' . s($studentemail) . '</b>&nbsp;</u> Phone no. <u>&nbsp;<b>' . s($studentphone) . '</b>&nbsp;</u> to undergo ' . $training_word . ' training in your esteemed organization starting from <u>&nbsp;<b>' . s($startdatestr) . '</b>&nbsp;</u>.
</p>

<div style="font-size: 8pt; color: #222;">
(* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)
</div>

<p align="justify" style="font-size: 9.3pt; line-height: 1.25;">
We would highly appreciate if the student can be accommodated for the training programme. Our students are sincere and hard working and we are sure that they will put in their best efforts during the training program. Looking for the confirmation from your side.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 9.5pt;">
    <tr>
        <td width="100%" align="left">
            Yours Sincerely<br><br><br>
            ' . $signatory_html . '
        </td>
    </tr>
</table>
';

$pdf->writeHTMLCell(180, 0, 15, 14, $html, 0, 1, false, true, 'J', true);

// Footer text at bottom inside border.
$footerhtml = '
<div align="center" style="font-size: 7.8pt; line-height: 1.2; font-family: times;">
    <b>Contact Us At:</b><br>
    <b>Address:</b> Training and Placement Cell, GNDEC, Gill Road, Ludhiana – 141006<br>
    <b>E-mail:</b> ' . $footer_emails . '<br>
    <b>Telefax:</b> +91-161-2501106 &nbsp; <b>Cell:</b> ' . $footer_cell . ', &nbsp; <b>Phone No.</b> 0161 – 5064510
</div>
';

$pdf->writeHTMLCell(180, 0, 15, 271, $footerhtml, 0, 1, false, true, 'C', true);

$cleanroll = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->rollnumber);
$filename = 'GNDEC_Training_Letter_' . $cleanroll . '_' . $request->id . '.pdf';

$pdf->Output($filename, 'D');