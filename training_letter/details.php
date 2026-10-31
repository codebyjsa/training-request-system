<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request details view page.

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
$PAGE->set_url(new moodle_url('/local/training_letter/details.php', ['id' => $id]));
$PAGE->set_title(get_string('details', 'local_training_letter') . ' - ' . get_string('pluginname', 'local_training_letter'));
$PAGE->set_heading(get_string('details', 'local_training_letter'));

require_capability('local/training_letter:reviewrequests', $context);

global $DB;

$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

$studentuser = $DB->get_record('user', ['id' => $request->userid], 'id, email');
$studentemail = !empty($studentuser->email) ? $studentuser->email : '-';

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('details', 'local_training_letter') . ': ' . s($request->studentname));

$table = new html_table();
$table->attributes['class'] = 'generaltable table table-bordered';
$table->head = ['Particular', 'Details'];

$statusbadge = '';
if ($request->status === 'approved') {
    $statusbadge = '<span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> ' . get_string('status_approved', 'local_training_letter') . '</span>';
} else if ($request->status === 'rejected') {
    $statusbadge = '<span class="badge badge-danger px-2 py-1"><i class="fa fa-times"></i> ' . get_string('status_rejected', 'local_training_letter') . '</span>';
} else {
    $statusbadge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> ' . get_string('status_pending', 'local_training_letter') . '</span>';
}

$table->data = [
    [get_string('fullname', 'local_training_letter'), '<strong>' . s($request->studentname) . '</strong>'],
    [get_string('rollnumber', 'local_training_letter'), '<code>' . s($request->rollnumber) . '</code>'],
    [get_string('branch', 'local_training_letter'), s($request->branch)],
    [get_string('semester', 'local_training_letter'), 'Semester ' . s($request->semester)],
    ['Email', s($studentemail)],
    [get_string('phone', 'local_training_letter'), s($request->phone)],
    [get_string('companyname', 'local_training_letter'), '<strong>' . s($request->companyname) . '</strong>'],
    [get_string('companyaddress', 'local_training_letter'), nl2br(s($request->companyaddress))],
    [get_string('trainingtype', 'local_training_letter'), ucfirst(s($request->trainingtype ?? 'software')) . ' Training'],
    [get_string('duration', 'local_training_letter'), s($request->duration)],
    [get_string('startdate', 'local_training_letter'), userdate($request->startdate, '%d %B %Y')],
    [get_string('enddate', 'local_training_letter'), userdate($request->enddate, '%d %B %Y')],
    [get_string('description', 'local_training_letter'), nl2br(s($request->description))],
    [get_string('status', 'local_training_letter'), $statusbadge],
    [get_string('coordinatorremarks', 'local_training_letter'), !empty($request->coordinatorremarks) ? s($request->coordinatorremarks) : '-'],
    [get_string('rejectionreason', 'local_training_letter'), !empty($request->rejectionreason) ? '<span class="text-danger">' . s($request->rejectionreason) . '</span>' : '-'],
    ['Submitted On', userdate($request->timecreated, '%d %B %Y, %I:%M %p')],
    ['Last Modified', userdate($request->timemodified, '%d %B %Y, %I:%M %p')],
];

echo html_writer::table($table);

// Action buttons.
echo html_writer::start_div('mt-3 mb-4');

if ($request->status === 'pending') {
    $approveurl = new moodle_url('/local/training_letter/review.php', ['id' => $request->id, 'action' => 'approve']);
    $rejecturl = new moodle_url('/local/training_letter/review.php', ['id' => $request->id, 'action' => 'reject']);

    echo html_writer::link($approveurl, '<i class="fa fa-check"></i> ' . get_string('approve', 'local_training_letter'), ['class' => 'btn btn-success mr-2']);
    echo html_writer::link($rejecturl, '<i class="fa fa-times"></i> ' . get_string('reject', 'local_training_letter'), ['class' => 'btn btn-danger mr-2']);
} else if ($request->status === 'approved') {
    $letterurl = new moodle_url('/local/training_letter/letter.php', ['id' => $request->id]);
    $downloadurl = new moodle_url('/local/training_letter/download.php', ['id' => $request->id]);

    echo html_writer::link($letterurl, '<i class="fa fa-file-text-o"></i> ' . get_string('viewletter', 'local_training_letter'), ['class' => 'btn btn-primary mr-2']);
    echo html_writer::link($downloadurl, '<i class="fa fa-download"></i> ' . get_string('downloadpdf', 'local_training_letter'), ['class' => 'btn btn-outline-primary mr-2']);
}

echo html_writer::link(
    new moodle_url('/local/training_letter/manage.php'),
    '<i class="fa fa-arrow-left"></i> ' . get_string('backtorequests', 'local_training_letter'),
    ['class' => 'btn btn-secondary']
);

echo html_writer::end_div();

echo $OUTPUT->footer();