<?php
// This file is part of Moodle - https://moodle.org/
//
// Database upgrade script for local_training_letter.

defined('MOODLE_INTERNAL') || die();


function xmldb_local_training_letter_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026103100) {
        $table = new xmldb_table('local_training_letter');

        // Define field trainingtype to be added to local_training_letter.
        $field_type = new xmldb_field('trainingtype', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'software', 'phone');
        if (!$dbman->field_exists($table, $field_type)) {
            $dbman->add_field($table, $field_type);
        }

        // Define field coordinatorremarks to be added.
        $field_remarks = new xmldb_field('coordinatorremarks', XMLDB_TYPE_TEXT, null, null, null, null, null, 'rejectionreason');
        if (!$dbman->field_exists($table, $field_remarks)) {
            $dbman->add_field($table, $field_remarks);
        }

        // Define field reviewedby to be added.
        $field_reviewedby = new xmldb_field('reviewedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'coordinatorremarks');
        if (!$dbman->field_exists($table, $field_reviewedby)) {
            $dbman->add_field($table, $field_reviewedby);
        }

        // Define index trainingtype_idx to be added.
        $index = new xmldb_index('trainingtype_idx', XMLDB_INDEX_NOTUNIQUE, ['trainingtype']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2026103100, 'local', 'training_letter');
    }

    return true;
}
