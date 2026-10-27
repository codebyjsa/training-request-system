<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request letter.

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
if ($request->userid != $USER->id) {
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

echo $OUTPUT->header();

$downloadurl = new moodle_url('/local/training_letter/download.php', [
    'id' => $request->id,
]);

echo html_writer::div(
    html_writer::link(
        $downloadurl,
        'Download PDF',
        ['class' => 'btn btn-primary']
    ),
    'mb-4'
);

echo html_writer::start_div('container');

echo html_writer::tag('h2', 'TRAINING REQUEST LETTER', [
    'style' => 'text-align:center; margin-bottom:30px;',
]);

echo html_writer::tag('p', 'To,');
echo html_writer::tag('p', 'The Training and Placement Officer,');
echo html_writer::tag('p', 'Guru Nanak Dev Engineering College, Ludhiana.');

echo html_writer::tag('p', 'Subject: Request for Permission to Attend Training');

echo html_writer::tag('p', 'Respected Sir/Madam,');

$letter = 'I, ' . $request->studentname
    . ', bearing University Roll Number ' . $request->rollnumber
    . ', studying in ' . $request->branch
    . ', Semester ' . $request->semester
    . ', request permission to attend training at '
    . $request->companyname
    . ' from ' . userdate($request->startdate, '%d %B %Y')
    . ' to ' . userdate($request->enddate, '%d %B %Y')
    . '.';

echo html_writer::tag('p', s($letter));

echo html_writer::tag(
    'p',
    s($request->description)
);

echo html_writer::tag(
    'p',
    'I kindly request you to grant me permission for the above-mentioned training.'
);

echo html_writer::tag('p', 'Thank you.');

echo html_writer::tag('p', 'Yours sincerely,');
echo html_writer::tag('p', s($request->studentname));
echo html_writer::tag('p', 'University Roll Number: ' . s($request->rollnumber));

echo html_writer::end_div();

echo $OUTPUT->footer();