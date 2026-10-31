<?php
// This file is part of Moodle - https://moodle.org/
//
// Training request form definition.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for students submitting a training letter request.
 */
class local_training_letter_request_form extends moodleform {

    
    public function definition() {
        $mform = $this->_form;

        // Student details section.
        $mform->addElement('header', 'studentdetails', get_string('studentdetails', 'local_training_letter'));

        $mform->addElement('text', 'studentname', get_string('fullname', 'local_training_letter'));
        $mform->setType('studentname', PARAM_TEXT);
        $mform->addRule('studentname', get_string('error_fullname_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('text', 'rollnumber', get_string('rollnumber', 'local_training_letter'));
        $mform->setType('rollnumber', PARAM_ALPHANUMEXT);
        $mform->addRule('rollnumber', get_string('error_rollnumber_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('text', 'branch', get_string('branch', 'local_training_letter'));
        $mform->setType('branch', PARAM_TEXT);
        $mform->addRule('branch', get_string('error_branch_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('select', 'semester', get_string('semester', 'local_training_letter'), [
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
            5 => '5',
            6 => '6',
            7 => '7',
            8 => '8',
        ]);
        $mform->setType('semester', PARAM_INT);
        $mform->setDefault('semester', 7);
        $mform->addRule('semester', get_string('error_semester_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('text', 'phone', get_string('phone', 'local_training_letter'));
        $mform->setType('phone', PARAM_TEXT);
        $mform->addRule('phone', get_string('error_phone_req', 'local_training_letter'), 'required', null, 'client');

        // Company details section.
        $mform->addElement('header', 'companydetails', get_string('companydetails', 'local_training_letter'));

        $mform->addElement('text', 'companyname', get_string('companyname', 'local_training_letter'));
        $mform->setType('companyname', PARAM_TEXT);
        $mform->addRule('companyname', get_string('error_companyname_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('textarea', 'companyaddress', get_string('companyaddress', 'local_training_letter'), ['rows' => 3, 'cols' => 50]);
        $mform->setType('companyaddress', PARAM_TEXT);
        $mform->addRule('companyaddress', get_string('error_companyaddress_req', 'local_training_letter'), 'required', null, 'client');

        // Training particulars section.
        $mform->addElement('header', 'trainingdetails', get_string('trainingdetails', 'local_training_letter'));

        $mform->addElement('select', 'trainingtype', get_string('trainingtype', 'local_training_letter'), [
            'software' => get_string('trainingtype_software', 'local_training_letter'),
            'industrial' => get_string('trainingtype_industrial', 'local_training_letter'),
        ]);
        $mform->setType('trainingtype', PARAM_ALPHA);
        $mform->setDefault('trainingtype', 'software');

        $mform->addElement('select', 'duration', get_string('duration', 'local_training_letter'), [
            '6 Weeks' => '6 Weeks',
            '6 Months' => '6 Months',
            '4 Weeks' => '4 Weeks',
        ]);
        $mform->setType('duration', PARAM_TEXT);
        $mform->setDefault('duration', '6 Weeks');
        $mform->addRule('duration', get_string('error_duration_req', 'local_training_letter'), 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_training_letter'));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_training_letter'));

        $mform->addElement('textarea', 'description', get_string('description', 'local_training_letter'), ['rows' => 3, 'cols' => 50]);
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', get_string('error_description_req', 'local_training_letter'), 'required', null, 'client');

        // Action buttons.
        $this->add_action_buttons(true, get_string('submitrequest', 'local_training_letter'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Date validation.
        if (!empty($data['startdate']) && !empty($data['enddate'])) {
            if ($data['enddate'] < $data['startdate']) {
                $errors['enddate'] = get_string('error_dates_order', 'local_training_letter');
            }
        }

        // Phone number format validation: should be a valid mobile/phone number (at least 10 digits).
        if (!empty($data['phone'])) {
            $cleanedphone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($cleanedphone) < 10) {
                $errors['phone'] = get_string('error_phone_invalid', 'local_training_letter');
            }
        }

        // Roll number validation: alphanumeric with at least 3 characters.
        if (!empty($data['rollnumber'])) {
            if (strlen(trim($data['rollnumber'])) < 2) {
                $errors['rollnumber'] = get_string('error_rollnumber_req', 'local_training_letter');
            }
        }

        return $errors;
    }
}
