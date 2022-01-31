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
// this is a temporary file to manage download till file download design is done (most probably ws)

/**
 * This page display content of a course backup (if public only)
 * @package   localhub
 * @copyright 2010 Moodle Pty Ltd (http://moodle.com)
 * @author    Jerome Mouneyrac
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
// require_once($CFG->dirroot . '/admin/tool/customhub/classes/course_publish_manager.php'); //HUB_SCREENSHOT_FILE_TYPE and HUB_BACKUP_FILE_TYPE
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/hub/lib.php'); //HUBLOGOIMAGEWIDTH, HUBLOGOIMAGEHEIGHT
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/customhub/constants.php');

$courseid = optional_param('courseid', '', PARAM_INT);
$filetype = optional_param('filetype', '', PARAM_ALPHA); //can be screenshots, backup, ...
$screenshotnumber = optional_param('screenshotnumber', 1, PARAM_INT); //the screenshot number of this course
$imagewidth = optional_param('imagewidth', HUBLOGOIMAGEWIDTH, PARAM_ALPHANUM); //the screenshot width, can be set to 'original' to forcce original size
$imageheight = optional_param('imageheight', HUBLOGOIMAGEHEIGHT, PARAM_INT); //the screenshot height

if (!empty($courseid) and !empty($filetype) and get_config('local_hub', 'hubenabled')) {

    switch ($filetype) {

        case HUB_BACKUP_FILE_TYPE:
            // Check that the file is downloadable / set as visible
            $hubcourse = $DB->get_record('hub_course_directory', ['id' => $courseid]);
            if (!empty($hubcourse) && ($hubcourse->privacy or (!empty($USER) and is_siteadmin($USER->id)))) {
                // fwrite($fo, "\nFile is downloadable");

                // If the hub is set as PRIVATE, allow the download
                // either if the download is requested by a logged in user,
                // either if the download is requested by a site (server side request).
                $hubprivacy = get_config('local_hub', 'privacy');

                $token = optional_param('token', '', PARAM_ALPHANUM);

                // if (!empty($token)) {
                //     // Check the communication token.
                //     $hub = new local_hub();
                //     $communication = $hub->get_communication(WSSERVER, REGISTEREDSITE, '', $token);
                // }
                if ($hubprivacy != HUBPRIVATE ) { //or isloggedin() or !empty($communication)) {
                    // $userdir = "hub/" . $course->siteid . "/" . $course->sitecourseid;
                    $remotemoodleurl = optional_param('remotemoodleurl', '', PARAM_URL);
                    if (!empty($remotemoodleurl)) {
                        $remotemoodleurl = ',' . $remotemoodleurl . ',' . getremoteaddr();
                    } else {
                        $remotemoodleurl = ',' . 'unknown' . ',' . getremoteaddr();
                    }

                    // add_to_log(SITEID, 'local_hub', 'download backup', '', $courseid . $remotemoodleurl);
                    send_file(
                        $hubcourse->backupfilepath,
                        // $CFG->dataroot . '/' . $userdir . '/backup_' . $hubcourse->sitecourseid . ".mbz",
                        $hubcourse->shortname . ".mbz",
                        'default',
                        0,
                        false,
                        true,
                        '',
                        false
                    );
                }
            }
            break;

        case HUB_SCREENSHOT_FILE_TYPE:
            //check that the file is downloadable          
            $course = $DB->get_record('hub_course_directory', array('id' => $courseid));
            if (!empty($course) &&
                    ($course->privacy or (!empty($USER) and is_siteadmin($USER->id)))) {

                $userdir = "hub/" . $course->siteid . "/$courseid";
                $filepath = $CFG->dataroot . '/' . $userdir . '/screenshot_' . $courseid . "_" . $screenshotnumber;
                $imageinfo = getimagesize($filepath, $info);

                //TODO: make a way better check the requested size
                if (($imagewidth != HUBLOGOIMAGEWIDTH and $imageheight != HUBLOGOIMAGEHEIGHT)
                        and $imagewidth != 'original') {
                    throw new moodle_exception('wrongimagesize');
                }

                //check if the screenshot exists in the requested size           
                require_once($CFG->dirroot . "/repository/flickr_public/image.php");
                if ($imagewidth == 'original') {
                    $newfilepath = $filepath . "_original"; //need to be done if ever the picture changed
                } else {
                    $newfilepath = $filepath . "_" . $imagewidth . "x" . $imageheight;
                }

                //if the date of original newer than thumbnail all recreate a thumbnail
                if (!file_exists($newfilepath) or
                        (filemtime($filepath) > filemtime($newfilepath))) {
                    $image = new moodle_image($filepath);
                    if ($imagewidth != 'original') {
                        $image->resize($imagewidth, $imageheight);
                    }
                    $image->saveas($newfilepath);
                }
                send_file($newfilepath, 'image', 'default', 0, false, true, $imageinfo['mime'], false);
            }
            break;
    }
} else {
    //always give hub logo to anybody
    if ($filetype == HUB_HUBSCREENSHOT_FILE_TYPE) {
        $userdir = "hub/0";
        $filepath = $CFG->dataroot . '/' . $userdir . '/hublogo';
        $imageinfo = getimagesize($filepath, $info);

        //check if the screenshot exists in the requested size
        require_once($CFG->dirroot . "/repository/flickr_public/image.php");
        $newfilepath = $filepath . "_" . HUBLOGOIMAGEWIDTH . "x" . HUBLOGOIMAGEHEIGHT;

        if (!file_exists($newfilepath) or
                (filemtime($filepath) > filemtime($newfilepath))) {
            $image = new moodle_image($filepath);

            //scale to the max width/height dimension
            $imagewidth = $imageinfo[0];
            $imageheight = $imageinfo[1];
            if ($imagewidth > HUBLOGOIMAGEWIDTH) {
                $imagewidth = $imagewidth / ($imagewidth / HUBLOGOIMAGEWIDTH);
                $imageheight = $imageheight / ($imagewidth / HUBLOGOIMAGEWIDTH);
            }
            if ($imageheight > HUBLOGOIMAGEHEIGHT) {
                $imageheight = $imageheight / ($imageheight / HUBLOGOIMAGEWIDTH);
                $imagewidth = $imagewidth / ($imageheight / HUBLOGOIMAGEWIDTH);
            }

            $image->resize($imagewidth, $imageheight);
            $image->saveas($newfilepath);
        }

        send_file($newfilepath, 'image', 'default', 0, false, true, $imageinfo['mime'], false);
    }
}
