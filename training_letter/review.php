<?php
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

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/review.php', [
    'id' => $id,
    'action' => $action,
]));
$PAGE->set_title('Review Training Request');
$PAGE->set_heading('Review Training Request');

require_capability('local/training_letter:reviewrequests', $context);

if (!in_array($action, ['approve', 'reject'], true)) {
    throw new moodle_exception('Invalid review action');
}

$request = $DB->get_record(
    'local_training_letter',
    ['id' => $id],
    '*',
    MUST_EXIST
);

if ($action === 'approve') {
    require_sesskey();

    $request->status = 'approved';
    $request->rejectionreason = null;
    $request->timemodified = time();

    $DB->update_record('local_training_letter', $request);

    redirect(
        new moodle_url('/local/training_letter/manage.php'),
        'Training request approved.',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$mform = new local_training_letter_review_form($PAGE->url);
$mform->set_data([
    'id' => $id,
    'action' => $action,
]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/training_letter/manage.php'));
} else if ($data = $mform->get_data()) {
    require_sesskey();

    $request->status = 'rejected';
    $request->rejectionreason = trim($data->rejectionreason);
    $request->timemodified = time();

    $DB->update_record('local_training_letter', $request);

    redirect(
        new moodle_url('/local/training_letter/manage.php'),
        'Training request rejected.',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading('Reject Training Request');

$mform->display();

echo $OUTPUT->footer();