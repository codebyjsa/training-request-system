<?php
// This file is part of Moodle - https://moodle.org/
//
// Student's own training requests dashboard.

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
$PAGE->set_title(get_string('navmyrequests', 'local_training_letter'));
$PAGE->set_heading(get_string('navmyrequests', 'local_training_letter'));

global $DB, $USER;

$requests = $DB->get_records(
    'local_training_letter',
    ['userid' => $USER->id],
    'timecreated DESC'
);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('navmyrequests', 'local_training_letter'));

echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/training_letter/index.php'),
        '<i class="fa fa-plus"></i> ' . get_string('submitrequest', 'local_training_letter'),
        ['class' => 'btn btn-primary mb-3']
    )
);

if (empty($requests)) {
    echo $OUTPUT->notification(
        get_string('norequestsyet', 'local_training_letter'),
        \core\output\notification::NOTIFY_INFO
    );
} else {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable table table-striped table-hover align-middle';

    $table->head = [
        get_string('companyname', 'local_training_letter'),
        get_string('trainingtype', 'local_training_letter'),
        get_string('trainingdates', 'local_training_letter'),
        get_string('status', 'local_training_letter'),
        get_string('remarks', 'local_training_letter'),
        get_string('actions', 'local_training_letter'),
    ];

    $table->data = [];

    foreach ($requests as $request) {
        $trainingdates = userdate($request->startdate, '%d %b %Y')
            . ' &rarr; '
            . userdate($request->enddate, '%d %b %Y');

        $statusbadge = '';
        $letteractions = '-';
        $remarks = '-';

        if ($request->status === 'approved') {
            $statusbadge = '<span class="badge badge-success px-2 py-1"><i class="fa fa-check"></i> ' . get_string('status_approved', 'local_training_letter') . '</span>';
            $letterurl = new moodle_url('/local/training_letter/letter.php', ['id' => $request->id]);
            $downloadurl = new moodle_url('/local/training_letter/download.php', ['id' => $request->id]);
            $letteractions = html_writer::link($letterurl, '<i class="fa fa-file-text-o"></i> ' . get_string('viewletter', 'local_training_letter'), ['class' => 'btn btn-sm btn-primary mr-1'])
                . html_writer::link($downloadurl, '<i class="fa fa-download"></i> PDF', ['class' => 'btn btn-sm btn-outline-primary']);

            if (!empty($request->coordinatorremarks)) {
                $remarks = '<span class="text-success">' . s($request->coordinatorremarks) . '</span>';
            }
        } else if ($request->status === 'rejected') {
            $statusbadge = '<span class="badge badge-danger px-2 py-1"><i class="fa fa-times"></i> ' . get_string('status_rejected', 'local_training_letter') . '</span>';
            if (!empty($request->rejectionreason)) {
                $remarks = '<span class="text-danger"><strong>Reason:</strong> ' . s($request->rejectionreason) . '</span>';
            }
        } else {
            $statusbadge = '<span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> ' . get_string('status_pending', 'local_training_letter') . '</span>';
            $remarks = '<span class="text-muted"><i class="fa fa-hourglass-half"></i> Awaiting coordinator review</span>';
        }

        $type_display = ucfirst(s($request->trainingtype ?? 'software')) . '<br><small class="text-muted">(' . s($request->duration) . ')</small>';

        $table->data[] = [
            '<strong>' . s($request->companyname) . '</strong>',
            $type_display,
            $trainingdates,
            $statusbadge,
            $remarks,
            $letteractions,
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();