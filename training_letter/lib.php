<?php
defined('MOODLE_INTERNAL') || die();


function local_training_letter_extend_navigation(global_navigation $navigation) {
    global $PAGE, $USER;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $context = context_system::instance();

    // Student portal.
    $navigation->add(
        'Training Request Portal',
        new moodle_url('/local/training_letter/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_training_letter_portal'
    );

    // Student's own requests.
    $navigation->add(
        'My Training Requests',
        new moodle_url('/local/training_letter/myrequests.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_training_letter_myrequests'
    );

    // Coordinator dashboard, only for users with the review capability.
    if (has_capability('local/training_letter:reviewrequests', $context)) {
        $navigation->add(
            'Manage Training Requests',
            new moodle_url('/local/training_letter/manage.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_training_letter_manage'
        );
    }

    function local_training_letter_extend_navigation_user(
    navigation_node $parentnode,
    stdClass $user,
    context $context
) {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    $parentnode->add(
        'Training Request Portal',
        new moodle_url('/local/training_letter/index.php')
    );

    $parentnode->add(
        'My Training Requests',
        new moodle_url('/local/training_letter/myrequests.php')
    );

    if (has_capability('local/training_letter:reviewrequests', context_system::instance())) {
        $parentnode->add(
            'Manage Training Requests',
            new moodle_url('/local/training_letter/manage.php')
        );
    }
}
}