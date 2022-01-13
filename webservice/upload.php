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
 * this is a temporary file to manage upload till file upload design is done (most probably ws)
 * no time spend on identified the right course ID (we will probably need a new course secret string and
 * a new db field, or maybe return the real id during metadata record)
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->dirroot . '/local/hub/lib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/customhub/constants.php');
// require_once($CFG->dirroot . '/admin/tool/customhub/classes/course_publish_manager.php'); //HUB_SCREENSHOT_FILE_TYPE and HUB_BACKUP_FILE_TYPE

$token = optional_param('token', '', PARAM_ALPHANUM);
$filetype = optional_param('filetype', '', PARAM_ALPHA); //can be screenshots, backup, ...
$screenshotnumber = optional_param('screenshotnumber', 1, PARAM_INT); //the screenshot number of this course
$courseid = optional_param('courseid', '', PARAM_ALPHANUM);

// check the communication token
$hub = new local_hub();
$communication = $hub->get_communication(WSSERVER, REGISTEREDSITE, '', $token);
if (!empty($token) && !empty($communication) and get_config('local_hub', 'hubenabled')) {

    //retrieve the site
    $siteurl = $communication->remoteurl;
    $site = $hub->get_site_by_url($siteurl);

    // Check that the course exist.
    $course = $DB->get_record(
        'hub_course_directory',
        [
            'sitecourseid' => $courseid,
            'siteid' => $site->id
        ]
    );

    // TODO: Was passiert, wenn ein Kurs mehrfach hochgeladen/aktualisiert werden muss.
    if (!empty($course) && !empty($_FILES)) {
        switch ($filetype) {
            case HUB_BACKUP_FILE_TYPE:
                //check that the backup doesn't already exist
                $backup = $hub->backup_exits($siteid, $course->sitecourseid);
                if (empty($backup)) {
                    $hub->add_backup($_FILES['file'], $site->id, $course->sitecourseid);
                }
                $event = \local_hub\event\backup_uploaded::create(
                    [
                        'context' => context_system::instance(),
                        'other' => json_encode(['siteinfo' => $site, 'courseinfo' => $course])
                    ]
                );
                $event->trigger();
                break;
            case HUB_SCREENSHOT_FILE_TYPE:
                $hub->add_screenshot($_FILES['file'], $siteid, $course->sitecourseid, $screenshotnumber);
                break;
        }
    }
}
