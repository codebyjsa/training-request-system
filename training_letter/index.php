<?php
// This file is part of Moodle - https://moodle.org/
//
// Training Request plugin homepage.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}
require_once(__DIR__ . '/request_form.php');

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_training_letter'));
$PAGE->set_heading(get_string('pluginname', 'local_training_letter'));

$mform = new local_training_letter_request_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/training_letter/index.php'));
} else if ($data = $mform->get_data()) {
    global $DB, $USER;

    $record = new stdClass();

    $record->userid = $USER->id;
    $record->studentname = $data->studentname;
    $record->rollnumber = $data->rollnumber;
    $record->branch = $data->branch;
    $record->semester = $data->semester;
    $record->companyname = $data->companyname;
    $record->startdate = $data->startdate;
    $record->enddate = $data->enddate;
    $record->description = $data->description;

    $record->status = 'pending';
    $record->timecreated = time();
    $record->timemodified = time();

    $DB->insert_record('local_training_letter', $record);

    redirect(
        new moodle_url('/local/training_letter/index.php'),
        'Your training request has been submitted successfully.',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}


echo $OUTPUT->header();

echo $OUTPUT->heading('GNDEC Training Request Portal');

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/training_letter/myrequests.php'),
        'My Training Requests',
        ['class' => 'btn btn-secondary mb-3']
    )
);

$mform->display();

echo $OUTPUT->footer();
