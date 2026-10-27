<?php
// This file is part of Moodle - https://moodle.org/
//
// Manage training requests.

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
$PAGE->set_url(new moodle_url('/local/training_letter/manage.php'));
$PAGE->set_title('Manage Training Requests');
$PAGE->set_heading('Manage Training Requests');

// Temporary access restriction: Moodle administrators only.
require_capability('local/training_letter:reviewrequests', $context);

global $DB;

// Read filters from the request.
$statusfilter = optional_param('status', '', PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);

// Build the database conditions.
$conditions = [];
$params = [];

if (in_array($statusfilter, ['pending', 'approved', 'rejected'], true)) {
    $conditions['status'] = $statusfilter;
}

if (!empty($search)) {
    $sql = "SELECT *
              FROM {local_training_letter}
             WHERE " . (!empty($conditions) ? "status = :status AND " : "") .
             "(LOWER(studentname) LIKE :search
                OR LOWER(rollnumber) LIKE :search
                OR LOWER(companyname) LIKE :search)
          ORDER BY timecreated DESC";

    $params['search'] = '%' . core_text::strtolower($search) . '%';

    if (!empty($conditions)) {
        $params['status'] = $statusfilter;
    }

    $requests = $DB->get_records_sql($sql, $params);
} else {
    $requests = $DB->get_records(
        'local_training_letter',
        $conditions,
        'timecreated DESC'
    );
}

echo $OUTPUT->header();

echo $OUTPUT->heading('Training Requests');

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => new moodle_url('/local/training_letter/manage.php'),
    'class' => 'mb-3',
]);

echo html_writer::tag('label', 'Status: ', [
    'for' => 'status',
]);

echo html_writer::select(
    [
        '' => 'All',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
    'status',
    $statusfilter,
    false
);

echo html_writer::tag('label', ' Search: ', [
    'for' => 'search',
]);

echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'search',
    'value' => s($search),
    'placeholder' => 'Student, roll number, or company',
]);

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => 'Filter',
    'class' => 'btn btn-primary',
]);

echo html_writer::end_tag('form');

if (empty($requests)) {
    echo $OUTPUT->notification(
        'No training requests have been submitted yet.',
        \core\output\notification::NOTIFY_INFO
    );
} else {
    $table = new html_table();

    $table->head = [
        'Student Name',
        'Roll Number',
        'Company',
        'Training Dates',
        'Status',
        'Actions',
        'Details'
    ];

    $table->data = [];

    foreach ($requests as $request) {
        $trainingdates = userdate($request->startdate, '%d %b %Y')
            . ' - '
            . userdate($request->enddate, '%d %b %Y');

        $approveurl = new moodle_url('/local/training_letter/review.php', [
            'id' => $request->id,
            'action' => 'approve',
            'sesskey' => sesskey()
        ]);

        $rejecturl = new moodle_url('/local/training_letter/review.php', [
            'id' => $request->id,
            'action' => 'reject',
            'sesskey' => sesskey()
        ]);

        $actions = '';

        if ($request->status === 'pending') {
            $actions = html_writer::link($approveurl, 'Approve')
                . ' | '
                . html_writer::link($rejecturl, 'Reject');
    }

    $detailsurl = new moodle_url(
    '/local/training_letter/details.php',
    ['id' => $request->id]
    );

    $detailslink = html_writer::link(
        $detailsurl,
        'View Details'
    );

    $table->data[] = [
            s($request->studentname),
            s($request->rollnumber),
            s($request->companyname),
            $trainingdates,
            ucfirst(s($request->status)),
            $actions,
            $detailslink
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();