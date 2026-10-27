<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class local_training_letter_review_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHA);

        $mform->addElement(
            'textarea',
            'rejectionreason',
            'Reason for rejection',
            ['rows' => 4, 'cols' => 50]
        );

        $mform->setType('rejectionreason', PARAM_TEXT);

        $mform->addRule(
            'rejectionreason',
            'Please provide a reason for rejection.',
            'required'
        );

        $this->add_action_buttons(true, 'Reject Request');
    }
}