<?php
// This file is part of Moodle - https://moodle.org/
//
// Library functions and navigation integration for Training Request Letter Generator.

defined('MOODLE_INTERNAL') || die();


function local_training_letter_extend_navigation(global_navigation $navigation) {
    if (!isloggedin() || isguestuser()) {
        return;
    }

    $context = context_system::instance();

    // Student portal node.
    $navigation->add(
        get_string('navportal', 'local_training_letter'),
        new moodle_url('/local/training_letter/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_training_letter_portal'
    );

    // Student's own requests node.
    $navigation->add(
        get_string('navmyrequests', 'local_training_letter'),
        new moodle_url('/local/training_letter/myrequests.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_training_letter_myrequests'
    );

    // Coordinator dashboard, only for users with the review capability.
    if (has_capability('local/training_letter:reviewrequests', $context)) {
        $navigation->add(
            get_string('navmanage', 'local_training_letter'),
            new moodle_url('/local/training_letter/manage.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_training_letter_manage'
        );
    }
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
        get_string('navportal', 'local_training_letter'),
        new moodle_url('/local/training_letter/index.php')
    );

    $parentnode->add(
        get_string('navmyrequests', 'local_training_letter'),
        new moodle_url('/local/training_letter/myrequests.php')
    );

    if (has_capability('local/training_letter:reviewrequests', context_system::instance())) {
        $parentnode->add(
            get_string('navmanage', 'local_training_letter'),
            new moodle_url('/local/training_letter/manage.php')
        );
    }
}