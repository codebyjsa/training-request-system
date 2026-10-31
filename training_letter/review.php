<?php
// This file is part of Moodle - https://moodle.org/
//
// Coordinator review execution page.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}
require_once(__DIR__ . '/review_form.php');

require_login();

$context = context_system::instance();

$id = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_ALPHA);

if (!in_array($action, ['approve', 'reject'], true)) {
    throw new moodle_exception('invalidaction', 'error');
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/review.php', [
    'id' => $id,
    'action' => $action,
]));
$PAGE->set_title(get_string('reviewrequest', 'local_training_letter'));
$PAGE->set_heading(get_string('reviewrequest', 'local_training_letter'));

require_capability('local/training_letter:reviewrequests', $context);

global $DB, $USER;

$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

$mform = new local_training_letter_review_form(
    $PAGE->url,
    ['id' => $id, 'action' => $action, 'request' => $request]
);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/training_letter/manage.php'));
} else if ($data = $mform->get_data()) {
    require_sesskey();

    if ($action === 'approve') {
        $request->status = 'approved';
        $request->coordinatorremarks = !empty($data->remarks) ? trim($data->remarks) : null;
        $request->rejectionreason = null;
        $request->reviewedby = $USER->id;
        $request->timemodified = time();

        $DB->update_record('local_training_letter', $request);

        redirect(
            new moodle_url('/local/training_letter/manage.php'),
            get_string('requestapproved', 'local_training_letter'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        $request->status = 'rejected';
        $request->rejectionreason = trim($data->rejectionreason);
        $request->coordinatorremarks = null;
        $request->reviewedby = $USER->id;
        $request->timemodified = time();

        $DB->update_record('local_training_letter', $request);

        redirect(
            new moodle_url('/local/training_letter/manage.php'),
            get_string('requestrejected', 'local_training_letter'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }
}

echo $OUTPUT->header();

$actiontitle = ($action === 'approve') ? get_string('approve', 'local_training_letter') : get_string('reject', 'local_training_letter');
echo $OUTPUT->heading($actiontitle . ' - ' . get_string('reviewrequest', 'local_training_letter'));

$mform->display();

echo $OUTPUT->footer();