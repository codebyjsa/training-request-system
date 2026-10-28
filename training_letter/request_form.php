<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request form.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class local_training_letter_request_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // Student details.
        $mform->addElement('header', 'studentdetails', 'Student Details');

        $mform->addElement('text', 'studentname', 'Full Name');
        $mform->setType('studentname', PARAM_TEXT);
        $mform->addRule('studentname', 'Full name is required', 'required');

        $mform->addElement('text', 'rollnumber', 'University Roll Number');
        $mform->setType('rollnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('rollnumber', 'Roll number is required', 'required');

        $mform->addElement('text', 'branch', 'Branch');
        $mform->setType('branch', PARAM_TEXT);
        $mform->addRule('branch', 'Branch is required', 'required');

        $mform->addElement('select', 'semester', 'Semester', [
    1 => '1',
    2 => '2',
    3 => '3',
    4 => '4',
    5 => '5',
    6 => '6',
    7 => '7',
    8 => '8',
]);

$mform->addRule('semester', 'Semester is required', 'required');
$mform->setType('semester', PARAM_INT);

        $mform->addElement('text', 'phone', 'Phone Number');
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', 'Phone number is required', 'required');

        // Training details.
        $mform->addElement('header', 'trainingdetails', 'Training Details');

        $mform->addElement('text', 'companyname', 'Company Name');
        $mform->setType('companyname', PARAM_TEXT);
        $mform->addRule('companyname', 'Company name is required', 'required');

        $mform->addElement('textarea', 'companyaddress', 'Company Address');
        $mform->setType('companyaddress', PARAM_TEXT);
        $mform->addRule('companyaddress', 'Company address is required', 'required');

        $mform->addElement('select', 'duration', 'Training Duration', [
            '4 Weeks' => '4 Weeks',
            '6 Weeks' => '6 Weeks',
            '6 Months' => '6 Months',
        ]);
        $mform->setType('duration', PARAM_TEXT);
        $mform->setDefault('duration', '6 Weeks');
        $mform->addRule('duration', 'Training duration is required', 'required');

        $mform->addElement('date_selector', 'startdate', 'Training Start Date');
        $mform->addElement('date_selector', 'enddate', 'Training End Date');

        $mform->addElement('textarea', 'description', 'Training Description');
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', 'Training description is required', 'required');

        // Submit button.
        $this->add_action_buttons(true, 'Submit Training Request');
    }

public function validation($data, $files) {
    $errors = parent::validation($data, $files);

    if (!empty($data['startdate']) && !empty($data['enddate'])) {
        if ($data['enddate'] < $data['startdate']) {
            $errors['enddate'] = 'Training end date must be after the start date.';
        }
    }

    return $errors;
}

}
