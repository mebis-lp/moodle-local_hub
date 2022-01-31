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
 * @copyright 2018 Andre Scherl <andre.scherl@isb.bayern.de>
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
 * @copyright 22018 Andre Scherl <andre.scherl@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_oer extends tag_base_options {

    /** @var string tagtype of this element */
    protected $name = 'oer';

    /**
     * Add a suitable formelement to given moodleform.
     *
     * @param \MoodleQuickForm $mform
     */
    public function add_formelement(&$mform) {

        $mform->addElement('checkbox', 'oer', get_string('tag_oer', 'block_mbsteachshare'), get_string('tag_oerdesc', 'block_mbsteachshare'));
        $mform->addHelpButton('oer', 'tag_oer', 'block_mbsteachshare');
    }

    /**
     * This is called when the plugin is to be installed or upgraded only.
     */
    public function create_tag_default_options() {
        global $DB;

        $record          = new \stdClass();
        $record->tagtype = $this->name;
        $record->value   = 'OER';

        // Only insert new tags to DB.
        if (!$DB->record_exists('hub_tag_options',  ['tagtype' => $record->tagtype])) {
            $DB->insert_record('hub_tag_options', $record);
        }
    }

    /**
     * Get the values form the $data object by name of this tag and store it in
     * database.
     *
     * Please note that here tags are saved using one table:
     * - All values are already stored in hub_tag_options durgin install.
     * - Relationsship between values and options are stored in table hub_tag
     *
     * @param int $templateid
     * @param object $data
     * @return void
     */
    protected function save_by_name($templateid, $data) {
        global $DB;

        // Get the option data.
        $option = $DB->get_record('hub_tag_options', array('tagtype' => $this->name));

        if (empty($data->{$this->name})) {
            return;
        }

        $tag             = new \stdClass();
        $tag->templateid = $templateid;
        $tag->optionid   = $option->id;

        $DB->insert_record('hub_tag', $tag);
    }
}
