<?php
// This file is part of Moodle - https://moodle.org/
//
// Language strings for the local_training_letter plugin.

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Training Request Letter Generator';
$string['privacy:metadata'] = 'The Training Request Letter Generator stores student training request details.';

// Capabilities.
$string['training_letter:reviewrequests'] = 'Review and manage training requests';
$string['training_letter:submitrequest'] = 'Submit training letter requests';
$string['training_letter:viewownrequests'] = 'View own training letter requests';

// Navigation.
$string['navportal'] = 'Training Request Portal';
$string['navmyrequests'] = 'My Training Requests';
$string['navmanage'] = 'Manage Training Requests';

// Student Form.
$string['portalheading'] = 'GNDEC Training Request Portal';
$string['studentdetails'] = 'Student Details';
$string['fullname'] = 'Full Name';
$string['rollnumber'] = 'University Roll Number';
$string['branch'] = 'Branch / Department';
$string['semester'] = 'Semester';
$string['phone'] = 'Phone / Mobile Number';

$string['trainingdetails'] = 'Training Particulars';
$string['trainingtype'] = 'Training Type';
$string['trainingtype_software'] = 'Software Training (At least 6 Weeks)';
$string['trainingtype_industrial'] = 'Industrial Training (6 Months / Semester)';
$string['duration'] = 'Training Duration';
$string['startdate'] = 'Training Start Date / Joining Date';
$string['enddate'] = 'Training End Date';
$string['description'] = 'Training Synopsis / Description';

$string['companydetails'] = 'Company Details';
$string['companyname'] = 'Organization / Company Name';
$string['companyaddress'] = 'Company Postal Address';

$string['submitrequest'] = 'Submit Training Request';
$string['requestsubmitted'] = 'Your training request has been submitted successfully.';

// Validation.
$string['error_fullname_req'] = 'Full name is required.';
$string['error_rollnumber_req'] = 'Roll number is required.';
$string['error_branch_req'] = 'Branch is required.';
$string['error_semester_req'] = 'Semester is required.';
$string['error_phone_req'] = 'Phone number is required.';
$string['error_phone_invalid'] = 'Please enter a valid 10-digit mobile number.';
$string['error_companyname_req'] = 'Company name is required.';
$string['error_companyaddress_req'] = 'Company postal address is required.';
$string['error_duration_req'] = 'Training duration is required.';
$string['error_description_req'] = 'Training synopsis / description is required.';
$string['error_dates_order'] = 'Training end date must be after the start date.';

// Status & Dashboard.
$string['status'] = 'Status';
$string['status_all'] = 'All';
$string['status_pending'] = 'Pending';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
$string['norequestsfound'] = 'No training requests have been submitted yet.';
$string['norequestsyet'] = 'You have not submitted any training requests yet.';
$string['actions'] = 'Actions';
$string['details'] = 'Details';
$string['viewletter'] = 'View Letter';
$string['downloadpdf'] = 'Download PDF';
$string['printletter'] = 'Print Letter';
$string['backtoportal'] = 'Back to Portal';
$string['backtorequests'] = 'Back to Requests';
$string['rejectionreason'] = 'Reason for Rejection';
$string['coordinatorremarks'] = 'Coordinator Remarks / Notes';
$string['remarks'] = 'Remarks';
$string['trainingdates'] = 'Training Dates';

// Coordinator Review.
$string['managerequests'] = 'Manage Training Requests';
$string['reviewrequest'] = 'Review Training Request';
$string['approve'] = 'Approve';
$string['reject'] = 'Reject';
$string['requestapproved'] = 'Training request has been approved successfully.';
$string['requestrejected'] = 'Training request has been rejected.';
$string['confirm_approve'] = 'Approve this training request?';
$string['optional_remarks'] = 'Remarks / Endorsement Notes (Optional)';
$string['rejection_reason_req'] = 'Please provide a reason for rejecting this request.';
$string['filter'] = 'Filter';
$string['search'] = 'Search';
$string['search_placeholder'] = 'Student, roll number, or company';

// Letter View.
$string['lettertitle'] = 'Training Request Letter';
$string['letter_not_approved_warning'] = 'Your training request must be approved by the coordinator before the official letter is available.';