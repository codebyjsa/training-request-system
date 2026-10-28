<?php
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
$PAGE->set_url(new moodle_url('/local/training_letter/details.php', [
    'id' => $id,
]));
$PAGE->set_title('Training Request Details');
$PAGE->set_heading('Training Request Details');

// Only coordinators can inspect requests through this page.
require_capability(
    'local/training_letter:reviewrequests',
    $context
);

global $DB;

$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

echo $OUTPUT->header();

echo $OUTPUT->heading('Request Details');

$table = new html_table();
$table->head = ['Field', 'Details'];

$table->data = [
    ['Student Name', s($request->studentname)],
    ['Roll Number', s($request->rollnumber)],
    ['Branch', s($request->branch)],
    ['Semester', s($request->semester)],
    ['Phone Number', s($request->phone)],
    ['Company', s($request->companyname)],
    ['Company Address', nl2br(s($request->companyaddress))],
    ['Training Duration', s($request->duration)],
    [
        'Training Start Date',
        userdate($request->startdate, '%d %B %Y')
    ],
    [
        'Training End Date',
        userdate($request->enddate, '%d %B %Y')
    ],
    ['Training Description', nl2br(s($request->description))],
    ['Current Status', ucfirst(s($request->status))],
];

echo html_writer::table($table);

echo html_writer::link(
    new moodle_url('/local/training_letter/manage.php'),
    'Back to Requests'
);

echo $OUTPUT->footer();