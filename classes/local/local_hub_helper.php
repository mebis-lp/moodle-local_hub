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
 * Helper class for local_hub plugin
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
namespace  local_hub\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Helper class for local_hub plugin
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
class local_hub_helper {

    /* Demo Course Category Name */
    const HUB_COURSECATEGORY_DEMO_NAME = "Hub Course Demo";

    /**
     * Events callback method to trigger the creation of a demo course.
     */
    public static function callback_make_demo_course($event) {
        $other = json_decode($event->get_data()['other']);
        self::make_demo_course($other->courseinfo);
    }

    /**
     * Restore the backup and provide a demo course.
     * @param object $course Object submitted from tool_cutsomhub course submission form.
     */        
    public static function make_demo_course($course) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        // Get the courserecord from hub_course_directory.
        $hubcourse = self::get_registered_course($course->sitecourseid, $course->siteid, $course->enrollable);

        $backupfilepath = $hubcourse->backupfilepath;       
        if (!is_file($backupfilepath)) {
            self::trigger_restore_error_event('Backupfile was not found. ('.$backupfilepath.')');
            throw new \moodle_exception('errorrestore_archive_not_found', 'local_hub', '', null, $backupfilepath);
        }

        // Create the temp/backup directory if necessary.
        $backuptmpdir = $CFG->tempdir . DIRECTORY_SEPARATOR . 'backup/hub';
        if (!check_dir_exists($backuptmpdir, true, true)) {
            self::trigger_restore_error_event('Backuptempdir could not be created.');
            throw new \restore_controller_exception('cannot_create_backup_temp_dir');
        }
        $tempdir   = 'hubtemplating_' . $hubcourse->id . '_' . time();
        $backuptmpdir = make_backup_temp_directory($tempdir);

        // Copy and extract backup file to temp dir in order to be restored.
        $fp = get_file_packer('application/vnd.moodle.backup');
        $extracted = $fp->extract_to_pathname($backupfilepath, $backuptmpdir);
        $moodlefile = $backuptmpdir . '/' . 'moodle_backup.xml';
        if (!$extracted || !is_readable($moodlefile)) {
            self::trigger_restore_error_event('moodle_backup.xml was not found.');
            throw new \backup_helper_exception('missing_moodle_backup_xml_file', $moodlefile);
        }

        // Create a demo course category if necessary.
        $coursecat = self::create_demo_course_category();

        // Load info.
        $info = \backup_general_helper::get_backup_information($tempdir);

        $shortname = self::get_unused_shortname("demo_" . $hubcourse->id . "_" . $course->shortname);
        // Transaction.
        $transaction = $DB->start_delegated_transaction();
        $cdata = (object) [
            'category' => $coursecat->id,
            'shortname' => $shortname,
            'fullname' => $info->original_course_fullname,
            'visible' => 0,
            'newsitems' => 0, // Prevent creation of a new forum when course_created event is fired.
        ];

        $newcourse = create_course($cdata);

        // Commit.
        $transaction->allow_commit();

        // Transaction.
        $transaction = $DB->start_delegated_transaction();
        // Restore the backup to make a course viewable.

        // Restore.
        $admin = self::get_admin_user();

        // mebis Tafel material copy is necessary. Store this info with courseid and no userid, because its the teachSHARE user.
        // self::$tafelcopyinfos = [1, $course->id];

        // Setting this value causes mapping of ids in restore_local_mbsteachshare_plugin.
        // self::$excludedeploydatacmids = self::get_exploded_ids($template->excludedeploydatacmids);
        // self::$targetnewcourse        = true;

        try {
            $rc = new \restore_controller(
                $tempdir,
                $newcourse->id,
                \backup::INTERACTIVE_NO,
                \backup::MODE_SAMESITE,
                $admin->id,
                \backup::TARGET_EXISTING_DELETING
            );
            $rc->execute_precheck();
            $rc->execute_plan();
        } catch (\Exception $e) {
            self::trigger_restore_error_event('Error when restoring the backup file.');
            throw new \moodle_exception('error_restoring_backupfile', 'local_hub', '', $e->getMessage());
        }
        // Commit.
        $transaction->allow_commit();

        $courserecord = $DB->get_record('course', ['id' => $newcourse->id]);
        if(empty($courserecord)) {
            self::trigger_restore_error_event('New course record not found.');
            throw new \moodle_exception('error_restored_course_not_found', 'local_hub', '', $e->getMessage());
        }

        // Set the demo course url.
        self::set_demo_course_url($hubcourse->id, $newcourse->id);
        self::set_demo_courseid($hubcourse->id, $newcourse->id);

        $event = \local_hub\event\course_restore_completed::create(
            [
                'context' => \context_system::instance(),
                'courseid' => $newcourse->id,
                'other' => json_encode(['courseregid' => $hubcourse->id])
            ]
        );
        $event->trigger();      
        
        // TODO: REMOVE THIS CALL. THIS IS ONLY FOR DEV PURPOSES.
        self::toggle_course_visibility($hubcourse->id);
    }

    /**
     * Create a Demo Course Category if necessary.
     * @return $object course_category record
     */
    public static function create_demo_course_category() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/testing/generator/data_generator.php');

        $coursecat = $DB->get_record('course_categories', ['name' => self::HUB_COURSECATEGORY_DEMO_NAME]);

        if (empty($coursecat)) {
            $generator = new \testing_data_generator();
            $record = [
                'name' => self::HUB_COURSECATEGORY_DEMO_NAME,
                'parent' => 0,
                'descriptionformat' => 0,
                'visible' => 1,
                'description' => '',
            ];
            $coursecat = $generator->create_category($record);
        }
        return $coursecat;
    }

    /**
     * Get a (primary) admin user to execute all the backups and restore processes.
     * @return object User record.
     */
    public static function get_admin_user() {
        global $CFG, $DB;

        if (empty($CFG->siteadmins)) {
            throw new \moodle_exception('error_no_siteadmins_defined', 'local_hub');
        }

        // Use the first defined Admin.
        foreach (explode(',', $CFG->siteadmins) as $admin) {
            $admin = (int)$admin;
            break;
        }
        return  $DB->get_record('user', ['id' => $admin]);
    }

    /**
     * Get the first registered instance of a course, related to hub_course_directory.
     */
    public static function get_registered_course ($courseid, $siteid, $enrollable = 1) {
        global $DB;
        $conditions = [
            'sitecourseid' => $courseid,
            'siteid' => $siteid
        ];

        if (empty($enrolable)) {
            $conditions['enrollable'] = $enrollable;
        }

        $courses = $DB->get_records(
            'hub_course_directory',
            $conditions
        );
        return array_shift($courses);
    }

    /**
     * Sets the demo course url to hub_course_directory.
     * @param int $id ID of the hub_course_directory
     * @param int $courseid of the course.
     */
    public static function set_demo_course_url($id, $courseid) {
        global $DB;
        $url = new \moodle_url('/course/view.php', ['id' => $courseid]);
        $DB->set_field('hub_course_directory', 'courseurl', (string) $url, ['id' => $id]);
    }

    /**
     * Sets the demo courseid to hub_course_directory.
     * @param int $id ID of the hub_course_directory
     * @param int $courseid of the course.
     */
    public static function set_demo_courseid($id, $courseid) {
        global $DB;
        $DB->set_field('hub_course_directory', 'coursemapid', $courseid, ['id' => $id]);
    }

    /**
     * Toggle the course visibility in hub search.
     * @param int $courseregid
     */
    public static function toggle_course_visibility($courseregid) {
        global $DB;
        $record = $DB->get_record('hub_course_directory', ['id' => $courseregid]);
        $DB->set_field('hub_course_directory', 'privacy', 1 - $record->privacy, ['id' => $courseregid]);
    }

    /**
     * Trigger an error event, to see whats wrong.
     * @param string $step
     */
    public static function trigger_restore_error_event($step) {
        $event = \local_hub\event\restore_error_occured::create(
            [
                'context' => \context_system::instance(),
                'other' => 'step => ' . $step,
            ]
        );
        $event->trigger();
    }

    /**
     * Get unused Shortname
     * @param string $shortname
     * @return string unused shortname
     */
    public static function get_unused_shortname($shortname){
        global $DB;
        $seperator = "";
        $i = "";
        while (!empty($DB->get_record('course', ['shortname' => $shortname . $seperator . $i]))) {
            $i++;
            $seperator = "_";
        }
        return $shortname . $seperator . $i;
    }
}
