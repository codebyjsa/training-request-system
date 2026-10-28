<?php
// This file is part of Moodle - https://moodle.org/
//
// Upgrade steps for the Training Request Letter Generator.

defined('MOODLE_INTERNAL') || die();

function xmldb_local_training_letter_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026102701) {
        $table = new xmldb_table('local_training_letter');

        // Create the table if it does not exist.
        if (!$dbman->table_exists($table)) {
            $table->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null));
            $table->addField(new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('studentname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, ''));
            $table->addField(new xmldb_field('rollnumber', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, ''));
            $table->addField(new xmldb_field('branch', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, ''));
            $table->addField(new xmldb_field('semester', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('companyname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, ''));
            $table->addField(new xmldb_field('startdate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('enddate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null));
            $table->addField(new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending'));
            $table->addField(new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
            $table->addField(new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('userid_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

            $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $table->add_index('rollnumber_idx', XMLDB_INDEX_NOTUNIQUE, ['rollnumber']);

            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2026102701, 'local', 'training_letter');
    }

    if ($oldversion < 2026102702) {
        upgrade_plugin_savepoint(true, 2026102702, 'local', 'training_letter');
    }

    if ($oldversion < 2026102704) {
        $table = new xmldb_table('local_training_letter');

        $field = new xmldb_field(
            'rejectionreason',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null
        );

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(
            true,
            2026102704,
            'local',
            'training_letter'
        );
    }

    if ($oldversion < 2026102800) {
        $table = new xmldb_table('local_training_letter');

        $phonefield = new xmldb_field('phone', XMLDB_TYPE_CHAR, '20', null, null, null, '');
        if (!$dbman->field_exists($table, $phonefield)) {
            $dbman->add_field($table, $phonefield);
        }

        $durationfield = new xmldb_field('duration', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '6 Weeks');
        if (!$dbman->field_exists($table, $durationfield)) {
            $dbman->add_field($table, $durationfield);
        }

        $addressfield = new xmldb_field('companyaddress', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($table, $addressfield)) {
            $dbman->add_field($table, $addressfield);
        }

        upgrade_plugin_savepoint(
            true,
            2026102800,
            'local',
            'training_letter'
        );
    }

    return true;
}
