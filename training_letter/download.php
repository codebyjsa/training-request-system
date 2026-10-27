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

// Create the PDF.
$pdf = new pdf('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('GNDEC Training Request System');
$pdf->SetAuthor($request->studentname);
$pdf->SetTitle('Training Request Letter');

$pdf->SetMargins(25, 25, 25);
$pdf->SetAutoPageBreak(true, 25);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Escape user-submitted values before placing them in HTML.
$studentname = s($request->studentname);
$rollnumber = s($request->rollnumber);
$branch = s($request->branch);
$semester = s($request->semester);
$companyname = s($request->companyname);
$description = nl2br(s($request->description));

$startdate = userdate($request->startdate, '%d %B %Y');
$enddate = userdate($request->enddate, '%d %B %Y');

$html = '
<h2 style="text-align:center;">TRAINING REQUEST LETTER</h2>
<br>
<p>To,<br>
The Training and Placement Officer,<br>
Guru Nanak Dev Engineering College,<br>
Ludhiana.</p>

<p><b>Subject:</b> Request for Permission to Attend Training</p>

<p>Respected Sir/Madam,</p>

<p>
I, <b>' . $studentname . '</b>, bearing University Roll Number
<b>' . $rollnumber . '</b>, studying in ' . $branch . ',
Semester ' . $semester . ', request permission to attend training at
<b>' . $companyname . '</b> from ' . $startdate . ' to ' . $enddate . '.
</p>

<p>' . $description . '</p>

<p>
I kindly request you to grant me permission for the
above-mentioned training.
</p>

<p>Thank you.</p>

<br>

<p>Yours sincerely,<br>
' . $studentname . '<br>
University Roll Number: ' . $rollnumber . '</p>
';

$pdf->writeHTML($html, true, false, true, false, '');

$filename = 'training_request_' . $request->id . '.pdf';

$pdf->Output($filename, 'D');