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
 * Base class for tags.
 *
 * Please note that tags are save using two tables:
 * - All values are stored in hub_tag_options
 * - Relationsship between values and template are stored in table hub_tag
 *
 * This means we can handle option values created by user (for example description)
 * the same way as default options like for example schooltype.
 *
 * This base class uses user created options.
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\tags;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for tags.
 *
 * Please note that tags are save using two tables:
 * - All values are stored in hub_tag_options
 * - Relationsship between values and template are stored in table hub_tag
 *
 * This means we can handle option values created by user (for example description)
 * the same way as default options like for example schooltype.
 *
 * This base class uses user created options.
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tag_base {

    /**
     * Get an instance of this class.
     *
     * @return object
     */
    public static function get_instance() {
        $classname = get_called_class();
        return new $classname();
    }

    /**
     * Override this method to add a suitable formelement to given moodleform.
     *
     * @param \MoodleQuickForm $mform
     */
    abstract public function add_formelement(&$mform);

    /**
     * Get the values form the $data object by name of this tag and store it in
     * database.
     *
     * Please note that here tags are saved using two tables:
     * - All values are stored in hub_tag_options
     * - Relationsship between values and template are stored in table hub_tag
     *
     * @param int $templateid
     * @param object $data
     * @return void
     */
    protected function save_by_name($templateid, $data) {
        global $DB;

        // Get existing entries.
        $sql = "SELECT tag.id, tag.templateid, opt.id as optionid, opt.value
                FROM {hub_tag} tag
                JOIN {hub_tag_options} opt ON  opt.id = tag.optionid
                WHERE opt.tagtype = :tagtype AND tag.templateid = :templateid";

        // Delete tags and usercreated options.
        if ($existtags = $DB->get_records_sql($sql, ['tagtype' => $this->name, 'templateid' => $templateid])) {
            foreach ($existtags as $existtag) {
                $DB->delete_records('hub_tag_options', ['id', $existtag->optionid]);
                $DB->delete_records('hub_tag', ['id', $existtag->id]);
            }
        }

        if (empty($data->{$this->name})) {
            return;
        }

        $values = $data->{$this->name};
        if (!is_array($data->{$this->name})) {
            $values = [$data->{$this->name}];
        }

        foreach ($values as $value) {

            $option = (object) [
                    'tagtype' => $this->name,
                    'value' => $value
            ];
            $optionid = $DB->insert_record('hub_tag_options', $option);

            $tag = (object) [
                    'templateid' => $templateid,
                    'optionid' => $optionid
            ];
            $DB->insert_record('hub_tag', $tag);
        }
    }

    /**
     * Encapsulate save by name to give other classes a chance to modify data
     * before saving it.
     *
     * @param int $templateid
     * @param object $data
     */
    public function save($templateid, $data) {
        $this->save_by_name($templateid, $data);
    }

    /**
     * Delete specific option/value of tagtype.
     *
     * @param int $optionid Id of option to delete.
     */
    public function delete_option($optionid) {
        global $DB;

        $DB->delete_records('hub_tag_options', ['id' => $optionid]);
    }

    /**
     * Get values for this tag of a template
     *
     * @param int $templateid
     * @return string value of tags option assigned to template.
     */
    public function get_template_options($templateid) {
        global $DB;

        $sql = "SELECT opt.id, opt.value
                  FROM {hub_tag_options} opt
                  JOIN {hub_tag} tag ON tag.optionid = opt.id
                 WHERE tag.templateid = :templateid AND opt.tagtype = :tagtype
                 ORDER BY opt.id DESC";

        $params = [
            'templateid' => $templateid,
            'tagtype' => $this->name,
        ];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Get the most recent value of options for a template.
     *
     * @param int $templateid
     * @return string
     */
    public static function get_most_recent_value($templateid) {

        $tag = self::get_instance();
        if (!$options = $tag->get_template_options($templateid)) {
            return '';
        }

        $option = array_shift($options);
        return $option->value;
    }

    /**
     * Delete option values and their assignment to a template of this tag type.
     *
     * IMPORTANT NOTE: do not use this method for options that are used to be
     * assigned to multiple templates.
     *
     * @param int $templateid
     */
    public static function delete_template_options($templateid) {
        global $DB;

        $tag = self::get_instance();
        if (!$options = $tag->get_template_options($templateid)) {
            return;
        }
         // Delete course fullname.
        foreach ($options as $option) {
            // Delete option.
            $tag->delete_option($option->id);
            // Delete assingment to template.
            $DB->delete_records('hub_tag', ['templateid' => $templateid, 'optionid' => $option->id]);
        }

    }

}
