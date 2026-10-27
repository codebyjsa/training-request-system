<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/training_letter:reviewrequests' => [
        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];