<?php
// This file is part of Moodle - https://moodle.org/
//
// Capability definitions for the local_training_letter plugin.

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/training_letter:submitrequest' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
        ],
    ],
    'local/training_letter:viewownrequests' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
        ],
    ],
    'local/training_letter:reviewrequests' => [
        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ],
    ],
];