<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * On this page administrator can change hub settings
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/hub/admin/forms.php');
require_once($CFG->dirroot . '/webservice/lib.php');

admin_externalpage_setup('hubsettings');

$hubsettingsform = new \local_hub\form\hub_settings_form();
$fromform = $hubsettingsform->get_data();

echo $OUTPUT->header();

//check that the PHP xmlrpc extension is enabled
if (!extension_loaded('xmlrpc')) {
    $xmlrpcnotification = $OUTPUT->doc_link('admin/environment/php_extension/xmlrpc', '');
    $xmlrpcnotification .= get_string('xmlrpcdisabled', 'local_hub');
    echo $OUTPUT->notification($xmlrpcnotification);
    echo $OUTPUT->footer();
    die();
}

if (!empty($fromform) and confirm_sesskey()) {
    // if ($fromform->privacy != HUBPRIVATE and !empty($fromform->password)) {
    //     $fromform->password = null;
    // }

    // Save settings.
    set_config('name', $fromform->name, 'local_hub');
    set_config(
        'hubenabled',
        empty($fromform->enabled) ? 0 : $fromform->enabled,
        'local_hub'
    );

    set_config('description', $fromform->desc, 'local_hub');
    set_config('contactname', $fromform->contactname, 'local_hub');
    set_config('contactemail', $fromform->contactemail, 'local_hub');
    set_config('maxwscourseresult', $fromform->maxwscourseresult, 'local_hub');
    set_config('maxcoursesperday', $fromform->maxcoursesperday, 'local_hub');
    set_config('searchfornologin', empty($fromform->searchfornologin) ? 0 : 1, 'local_hub');

    set_config('enablerssfeeds', empty($fromform->enablerssfeeds) ? 0 : $fromform->enablerssfeeds, 'local_hub');
    set_config('rsssecret', empty($fromform->rsssecret) ? '' : $fromform->rsssecret, 'local_hub');

    // set_config('sendyurl', empty($fromform->sendyurl)?'':$fromform->sendyurl, 'local_hub');
    // set_config('sendylistid', empty($fromform->sendylistid)?'':$fromform->sendylistid, 'local_hub');
    // set_config('sendyapikey', empty($fromform->sendyapikey)?'':$fromform->sendyapikey, 'local_hub');

    set_config('language', $fromform->lang, 'local_hub');

    set_config(
        'password',
        empty($fromform->password) ? null : $fromform->password,
        'local_hub'
    );

    //save the hub logo
    if (empty($fromform->keepcurrentimage)) {
        $file = $hubsettingsform->save_temp_file('hubimage');

        if (!empty($file)) {

            $userdir = "hub/0/";

            //create directory if doesn't exist
            $directory = make_upload_directory($userdir);

            //save the image into the directory
            copy($file,  $directory . 'hublogo');

            set_config('hublogo', true, 'local_hub');

            $updatelogo = true;
        } else {
            if (file_exists($CFG->dataroot . '/hub/0/hublogo')) {
                unlink($CFG->dataroot . '/hub/0/hublogo');
            }
        }
    }

    if (empty($updatelogo) and empty($fromform->keepcurrentimage)) {
        set_config('hublogo', false, 'local_hub');
    }

    $hubsettingsform->update_hublogo();

    //display confirmation
    echo $OUTPUT->notification(get_string('settingsupdated', 'local_hub'), 'notifysuccess');
}

if (!get_config('moodle', 'extendedusernamechars')) {
    echo $OUTPUT->notification(get_string('noextendedusernamechars', 'local_hub'));
}

$hubsettingsform->display();

echo $OUTPUT->footer();
