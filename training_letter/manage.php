<?php
// This file is part of Moodle - https://moodle.org/
//
// Coordinator management dashboard for training letter requests.

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
$PAGE->set_title(get_string('managerequests', 'local_training_letter'));
$PAGE->set_heading(get_string('managerequests', 'local_training_letter'));

require_capability('local/training_letter:reviewrequests', $context);

global $DB;

// Read filters from request.
$statusfilter = optional_param('status', '', PARAM_ALPHA);
$typefilter = optional_param('type', '', PARAM_ALPHA);
$search = optional_param('search', '', PARAM_TEXT);

// Build conditions.
$where = [];
$params = [];

if (in_array($statusfilter, ['pending', 'approved', 'rejected'], true)) {
    $where[] = "status = :status";
    $params['status'] = $statusfilter;
}

if (in_array($typefilter, ['software', 'industrial'], true)) {
    $where[] = "trainingtype = :type";
    $params['type'] = $typefilter;
}

if (!empty($search)) {
    $where[] = "(LOWER(studentname) LIKE :search OR LOWER(rollnumber) LIKE :search OR LOWER(companyname) LIKE :search)";
    $params['search'] = '%' . core_text::strtolower(trim($search)) . '%';
}

$wherestr = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT * FROM {local_training_letter} $wherestr ORDER BY timecreated DESC";
$requests = $DB->get_records_sql($sql, $params);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managerequests', 'local_training_letter'));

// Filter Toolbar.
echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => new moodle_url('/local/training_letter/manage.php'),
    'class' => 'form-inline mb-4 p-3 bg-light border rounded',
]);

echo html_writer::tag('label', get_string('status', 'local_training_letter') . ': ', ['class' => 'mr-2 font-weight-bold']);
echo html_writer::select(
    [
        '' => get_string('status_all', 'local_training_letter'),
        'pending' => get_string('status_pending', 'local_training_letter'),
        'approved' => get_string('status_approved', 'local_training_letter'),
        'rejected' => get_string('status_rejected', 'local_training_letter'),
    ],
    'status',
    $statusfilter,
    false,
    ['class' => 'custom-select mr-3']
);

echo html_writer::tag('label', get_string('trainingtype', 'local_training_letter') . ': ', ['class' => 'mr-2 font-weight-bold']);
echo html_writer::select(
    [
        '' => get_string('status_all', 'local_training_letter'),
        'software' => 'Software',
        'industrial' => 'Industrial',
    ],
    'type',
    $typefilter,
    false,
    ['class' => 'custom-select mr-3']
);

echo html_writer::tag('label', get_string('search', 'local_training_letter') . ': ', ['class' => 'mr-2 font-weight-bold']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'search',
    'value' => s($search),
    'placeholder' => get_string('search_placeholder', 'local_training_letter'),
    'class' => 'form-control mr-3',
]);

echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('filter', 'local_training_letter'),
    'class' => 'btn btn-primary mr-2',
]);

if (!empty($statusfilter) || !empty($typefilter) || !empty($search)) {
    echo html_writer::link(
        new moodle_url('/local/training_letter/manage.php'),
        'Clear Filters',
        ['class' => 'btn btn-outline-secondary']
    );
}

echo html_writer::end_tag('form');

if (empty($requests)) {
    echo $OUTPUT->notification(
        get_string('norequestsfound', 'local_training_letter'),
        \core\output\notification::NOTIFY_INFO
    );
} else {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable table table-striped table-hover align-middle';

    $table->head = [
        get_string('fullname', 'local_training_letter'),
        get_string('rollnumber', 'local_training_letter'),
        get_string('companyname', 'local_training_letter'),
        get_string('trainingtype', 'local_training_letter'),
        get_string('trainingdates', 'local_training_letter'),
        get_string('status', 'local_training_letter'),
        get_string('actions', 'local_training_letter'),
        get_string('details', 'local_training_letter'),
    ];

    $table->data = [];

    foreach ($requests as $request) {
        $trainingdates = userdate($request->startdate, '%d %b %Y')
            . ' &rarr; '
            . userdate($request->enddate, '%d %b %Y');

        // Status badge.
        $statusbadge = '';
        if ($request->status === 'approved') {
            $statusbadge = '<span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> ' . get_string('status_approved', 'local_training_letter') . '</span>';
        } else if ($request->status === 'rejected') {
            $statusbadge = '<span class="badge badge-danger px-2 py-1"><i class="fa fa-times"></i> ' . get_string('status_rejected', 'local_training_letter') . '</span>';
        } else {
            $statusbadge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> ' . get_string('status_pending', 'local_training_letter') . '</span>';
        }

        // Actions.
        $actions = '';
        if ($request->status === 'pending') {
            $approveurl = new moodle_url('/local/training_letter/review.php', [
                'id' => $request->id,
                'action' => 'approve',
            ]);
            $rejecturl = new moodle_url('/local/training_letter/review.php', [
                'id' => $request->id,
                'action' => 'reject',
            ]);

            $actions = html_writer::link($approveurl, '<i class="fa fa-check"></i> ' . get_string('approve', 'local_training_letter'), ['class' => 'btn btn-sm btn-success mr-1'])
                . html_writer::link($rejecturl, '<i class="fa fa-times"></i> ' . get_string('reject', 'local_training_letter'), ['class' => 'btn btn-sm btn-danger']);
        } else if ($request->status === 'approved') {
            $letterurl = new moodle_url('/local/training_letter/letter.php', ['id' => $request->id]);
            $downloadurl = new moodle_url('/local/training_letter/download.php', ['id' => $request->id]);
            $actions = html_writer::link($letterurl, '<i class="fa fa-file-text-o"></i> ' . get_string('viewletter', 'local_training_letter'), ['class' => 'btn btn-sm btn-outline-primary mr-1'])
                . html_writer::link($downloadurl, '<i class="fa fa-download"></i> PDF', ['class' => 'btn btn-sm btn-primary']);
        } else {
            $actions = '<span class="text-muted"><i class="fa fa-ban"></i> Rejected</span>';
        }

        $detailsurl = new moodle_url('/local/training_letter/details.php', ['id' => $request->id]);
        $detailslink = html_writer::link($detailsurl, '<i class="fa fa-eye"></i> ' . get_string('details', 'local_training_letter'), ['class' => 'btn btn-sm btn-info']);

        $type_display = ucfirst(s($request->trainingtype ?? 'software')) . ' <br><small class="text-muted">(' . s($request->duration) . ')</small>';

        $table->data[] = [
            '<strong>' . s($request->studentname) . '</strong>',
            '<code>' . s($request->rollnumber) . '</code>',
            s($request->companyname),
            $type_display,
            $trainingdates,
            $statusbadge,
            $actions,
            $detailslink,
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();