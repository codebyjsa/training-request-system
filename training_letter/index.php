<?php
// This file is part of Moodle - https://moodle.org/
//
// Training Request plugin student homepage.

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
$PAGE->set_title(get_string('portalheading', 'local_training_letter'));
$PAGE->set_heading(get_string('portalheading', 'local_training_letter'));

// Capability check if defined, otherwise logged-in check is sufficient.
if (has_capability('local/training_letter:submitrequest', $context)) {
    // Allowed.
}

$mform = new local_training_letter_request_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/training_letter/index.php'));
} else if ($data = $mform->get_data()) {
    global $DB, $USER;

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->studentname = trim($data->studentname);
    $record->rollnumber = trim($data->rollnumber);
    $record->branch = trim($data->branch);
    $record->semester = (int)$data->semester;
    $record->phone = trim($data->phone);

    $record->companyname = trim($data->companyname);
    $record->companyaddress = trim($data->companyaddress);

    $record->trainingtype = !empty($data->trainingtype) ? $data->trainingtype : 'software';
    $record->duration = trim($data->duration);
    $record->startdate = (int)$data->startdate;
    $record->enddate = (int)$data->enddate;
    $record->description = trim($data->description);

    $record->status = 'pending';
    $record->rejectionreason = null;
    $record->coordinatorremarks = null;
    $record->reviewedby = 0;
    $record->timecreated = time();
    $record->timemodified = time();

    $DB->insert_record('local_training_letter', $record);

    redirect(
        new moodle_url('/local/training_letter/myrequests.php'),
        get_string('requestsubmitted', 'local_training_letter'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Pre-fill student particulars from $USER profile if available.
global $USER;
$defaultdata = new stdClass();
$fullname = fullname($USER);
if ($fullname !== 'Guest User') {
    $defaultdata->studentname = $fullname;
}
if (!empty($USER->idnumber)) {
    $defaultdata->rollnumber = $USER->idnumber;
}
if (!empty($USER->department)) {
    $defaultdata->branch = $USER->department;
}
if (!empty($USER->phone1)) {
    $defaultdata->phone = $USER->phone1;
} else if (!empty($USER->phone2)) {
    $defaultdata->phone = $USER->phone2;
}
$defaultdata->semester = 7;
$defaultdata->trainingtype = 'software';
$defaultdata->duration = '6 Weeks';

$mform->set_data($defaultdata);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('portalheading', 'local_training_letter'));

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/training_letter/myrequests.php'),
        '<i class="fa fa-list"></i> ' . get_string('navmyrequests', 'local_training_letter'),
        ['class' => 'btn btn-secondary mb-3']
    )
);

$mform->display();

echo $OUTPUT->footer();
