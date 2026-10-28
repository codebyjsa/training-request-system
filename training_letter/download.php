<?php
// This file is part of Moodle - https://moodle.org/
//
// Download training request letter as PDF.

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

// Students may download their own letter.
// Coordinators may download any request's letter.
if ((int)$request->userid !== (int)$USER->id) {
    require_capability(
        'local/training_letter:reviewrequests',
        $context
    );
}

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

// Create the PDF.
$pdf = new pdf('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('GNDEC Training Request System');
$pdf->SetAuthor($request->studentname);
$pdf->SetTitle('Official Training Letter - ' . $request->studentname);

// Set margins: 16mm left/right, 14mm top, 14mm bottom.
$pdf->SetMargins(16, 14, 16);
$pdf->SetAutoPageBreak(false); // keep on single official page
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
    // Center at x=55, y=95, width=100mm.
    $pdf->Image($logopath, 55, 95, 100, 0, 'PNG', '', '', false, 300, '', false, false, 0);
    $pdf->SetAlpha(1.0);
}

// Logo in header.
if (file_exists($logopath)) {
    $pdf->Image($logopath, 15, 14, 22, 24, 'PNG', '', '', false, 300, '', false, false, 0);
}

// Top Divider Line (dark red/maroon: RGB 128, 24, 24).
$pdf->SetLineStyle(['width' => 0.9, 'color' => [128, 24, 24]]);
$pdf->Line(14, 41, 196, 41);

// Bottom Divider Line (dark red/maroon).
$pdf->Line(14, 270, 196, 270);

$pdf->SetFont('times', '', 10);

$studentname = s($request->studentname);
$rollnumber = s($request->rollnumber);
$branch = s($request->branch);
$companyname = s($request->companyname);
$companyaddress = !empty($request->companyaddress) ? nl2br(s($request->companyaddress)) : '';

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

<div style="font-size: 10pt; line-height: 1.25;">
    To<br>
    <b>' . $companyname . '</b><br>
    ' . ($companyaddress ? $companyaddress . '<br>' : '') . '
</div>

<p align="left" style="font-size: 10.5pt;">
    <b><u>SUBJECT: ' . s($duration_upper) . ' INDUSTRIAL TRAINING FOR B.TECH. ' . s($year_ordinal) . ' STUDENTS.</u></b>
</p>

<div style="font-size: 10pt; line-height: 1.25;">
    Sir,<br><br>
    Greetings from GNDEC, Ludhiana.<br>
</div>

<p align="justify" style="font-size: 9.8pt; line-height: 1.3;">
Guru Nanak Dev Engineering College has emerged as one of the most prestigious engineering institute of North India over 62 years of its inception and is conducting B.TECH courses in seven disciplines as well as M.TECH, M.B.A. and Ph.D for meeting the research requirements of technical field.
</p>

<p align="justify" style="font-size: 9.8pt; line-height: 1.3;">
Since industrial training is equal in importance to theoretical foundation, the course curriculum is so designed that the students get exposure to practical aspects of their respective engineering branch. We are in a process of enrolling the ' . s($year_words) . ' year students of our institute to various Industrial Organisations for ' . s($duration_upper) . ' INDUSTRIAL TRAINING which is an essential component of their four year B.Tech programme.
</p>

<div style="font-size: 9.8pt; line-height: 1.25;">
The programme will be as under:
</div>
<ul style="font-size: 9.8pt; line-height: 1.25;">
    <li>To get familiar with the setup and working of the organisation.</li>
    <li>Working on the given project, 2 week onwards</li>
    <li>Submission of Final Report.</li>
    <li>Submission of Daily Diary at the end of training .</li>
</ul>

<p align="justify" style="font-size: 9.8pt; line-height: 1.35;">
We recommend our graduating student Mr./Ms. <u>&nbsp;<b>' . $studentname . '</b>&nbsp;</u> Roll No. <u>&nbsp;<b>' . $rollnumber . '</b>&nbsp;</u> Of B.Tech. (Branch) <u>&nbsp;<b>' . $branch . '</b>&nbsp;</u>, e-mail ID <u>&nbsp;<b>' . s($studentemail) . '</b>&nbsp;</u> Phone No. <u>&nbsp;<b>' . s($studentphone) . '</b>&nbsp;</u> to undergo ' . s($duration_lower) . ' training in your esteemed organization starting from <u>&nbsp;<b>' . s($startdatestr) . '</b>&nbsp;</u>
</p>

<div style="font-size: 8pt; color: #222;">
(* Exact date of joining may be intimated at a later stage. An early and favourable response will be highly appreciated.)
</div>

<p align="justify" style="font-size: 9.8pt; line-height: 1.3;">
We would very much appreciate if the student can be accommodated for the training programme in your esteemed organisation. Our students are sincere and hard working and we are sure that they will put in their best efforts during the training program. Looking forward to the confirmation from your side.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 9.8pt;">
    <tr>
        <td width="100%" align="left">
            Yours Sincerely<br><br><br>
            <b>Prof. G.S. Sodhi</b><br>
            Training &amp; Placement Officer
        </td>
    </tr>
</table>
';

$pdf->writeHTMLCell(180, 0, 15, 14, $html, 0, 1, false, true, 'J', true);

// Footer text at bottom inside border.
$footerhtml = '
<div align="center" style="font-size: 7.8pt; line-height: 1.2; font-family: times;">
    <b>Contact Us At:</b><br>
    <b>Address:</b> Training and Placement Cell, GNDEC, Gill Road, Ludhiana - 141006<br>
    <b>E-mail:</b> tpo@gndec.ac.in and gagan_sodhi76@yahoo.com<br>
    <b>Telefax:</b> +91-161-2501106 &nbsp; <b>Cell:</b> +91-98722-19178, &nbsp; <b>Phone No.</b> 0161-5064510
</div>
';

$pdf->writeHTMLCell(180, 0, 15, 271, $footerhtml, 0, 1, false, true, 'C', true);

$filename = 'training_letter_' . $request->rollnumber . '_' . $request->id . '.pdf';

$pdf->Output($filename, 'D');