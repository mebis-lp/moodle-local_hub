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
 * Store a several tags for a template
 *
 * Please note that tags are save using tables:
 * - All values are stored in hub_tag_options
 * - Relationsship between values and template are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_hub\tags;

defined('MOODLE_INTERNAL') || die();

/**
 * Store a several tags for a template
 *
 * Please note that tags are save using tables:
 * - All values are stored in hub_tag_options
 * - Relationsship between values and template are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_tags extends tag_base {

    /** @var string tagtype of this element */
    protected $name = 'tags';

    /**
     * Add a suitable formelement to given moodleform.
     *
     * @param \MoodleQuickForm $mform
     */
    public function add_formelement(&$mform) {

        $attributes = array('size' => 30, 'placeholder' => get_string('tagsplaceholder', 'block_mbsteachshare'));

        $mform->addElement('text', 'tags', get_string('tags', 'block_mbsteachshare'), $attributes);
        $mform->setType('tags', PARAM_TEXT);
        $mform->addHelpButton('tags', 'tagshelpbutton', 'block_mbsteachshare');
    }

    /**
     * Overridden to prepare data for storing it in database, which means to explode
     * and trim entered string.
     *
     * @param int $templateid
     * @param object $data
     */
    public function save($templateid, $data) {

        if (!empty($data->tags)) {

            $items      = explode(',', trim($data->tags));
            $data->tags = [];

            foreach ($items as $item) {
                $data->tags[] = trim($item);
            }
        }

        parent::save($templateid, $data);
    }

    /**
     * tag_base override for handling tags type
     *
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
            // Check if value not empty.
            if (strlen(trim($value)) > 0) {

                $option = (object) [
                    'tagtype' => $this->name,
                    'value'   => $value,
                ];

                // Tag already used in option table?
                $optionidset = $DB->get_record_sql('SELECT id FROM {hub_tag_options} WHERE tagtype = ? AND LOWER(value) = ?', [$this->name, strtolower($value)]);
                if ($optionidset === false) {
                    // Create new tag.
                    $optionid = $DB->insert_record('hub_tag_options', $option);
                } else {
                    // Use existing tag.
                    $optionid = $optionidset->id;
                }

                $tag = (object) [
                    'templateid' => $templateid,
                    'optionid'   => $optionid,
                ];
                $DB->insert_record('hub_tag', $tag);
            }
        }
    }

    /**
     * Delete specific option/value of tagtype.
     *
     * @param int $optionid Id of option to delete.
     */
    public function delete_option($optionid) {
        global $DB;

        // Search if tag is being used by another template, if not, it can be deleted.
        if (!$DB->record_exists('hub_tag', ['optionid' => $optionid])) {
            $DB->delete_records('hub_tag_options', ['id' => $optionid]);
        }
    }
}
