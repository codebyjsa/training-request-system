<?php
// This file is part of Moodle - https://moodle.org/
//
// Coordinator review form definition.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for coordinator review (Approve or Reject with remarks).
 */
class local_training_letter_review_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $id = $this->_customdata['id'] ?? 0;
        $action = $this->_customdata['action'] ?? 'approve';
        $request = $this->_customdata['request'] ?? null;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action', $action);
        $mform->setType('action', PARAM_ALPHA);

        if ($request) {
            $studentinfo = '<strong>' . s($request->studentname) . '</strong> (Roll: ' . s($request->rollnumber) . ', ' . s($request->branch) . ')<br>'
                . 'Company: <strong>' . s($request->companyname) . '</strong> | Type: ' . ucfirst(s($request->trainingtype ?? 'software')) . ' (' . s($request->duration) . ')';
            $mform->addElement('static', 'studentinfo', 'Student & Training', $studentinfo);
        }

        if ($action === 'approve') {
            $mform->addElement(
                'textarea',
                'remarks',
                get_string('optional_remarks', 'local_training_letter'),
                ['rows' => 4, 'cols' => 50]
            );
            $mform->setType('remarks', PARAM_TEXT);
            $this->add_action_buttons(true, get_string('approve', 'local_training_letter'));
        } else {
            $mform->addElement(
                'textarea',
                'rejectionreason',
                get_string('rejectionreason', 'local_training_letter'),
                ['rows' => 4, 'cols' => 50]
            );
            $mform->setType('rejectionreason', PARAM_TEXT);
            $mform->addRule(
                'rejectionreason',
                get_string('rejection_reason_req', 'local_training_letter'),
                'required',
                null,
                'client'
            );
            $this->add_action_buttons(true, get_string('reject', 'local_training_letter'));
        }
    }
}