<?php
// This file is part of Moodle - https://moodle.org/
//
// My training requests.

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once(__DIR__ . '/../../config.php');
} else if (!empty($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/config.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
} else {
    require_once('/var/www/moodle/public/config.php');
}

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/training_letter/myrequests.php'));
$PAGE->set_title('My Training Requests');
$PAGE->set_heading('My Training Requests');

global $DB, $USER;

$requests = $DB->get_records(
    'local_training_letter',
    ['userid' => $USER->id],
    'timecreated DESC'
);

echo $OUTPUT->header();

echo $OUTPUT->heading('My Training Requests');

if (empty($requests)) {
    echo $OUTPUT->notification(
        'You have not submitted any training requests yet.',
        \core\output\notification::NOTIFY_INFO
    );
} else {
    $table = new html_table();

    $table->head = [
        'Company',
        'Training Dates',
        'Status',
        'Rejection Reason',
        'Letter'
    ];

    $table->data = [];

    foreach ($requests as $request) {
        $trainingdates = userdate($request->startdate, '%d %b %Y')
            . ' - '
            . userdate($request->enddate, '%d %b %Y');

        $letterlink = '';

        if ($request->status === 'approved') {
            $letterurl = new moodle_url(
                '/local/training_letter/letter.php',
                ['id' => $request->id]
            );

            $letterlink = html_writer::link(
                $letterurl,
                'View Letter'
            );
        } else {
            $letterlink = '-';
        }

        $rejectionreason = '-';

        if ($request->status === 'rejected' && !empty($request->rejectionreason)) {
            $rejectionreason = s($request->rejectionreason);
        }

        $table->data[] = [
            s($request->companyname),
            $trainingdates,
            ucfirst(s($request->status)),
            $rejectionreason,
            $letterlink
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();