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
 * Class tags which schoolyear the template is assigned to.
 *
 * Please note that tags are save using one table:
 * - All values are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\tags;

defined('MOODLE_INTERNAL') || die();

/**
 * Class tags which schoolyear the template is assigned to.
 *
 * Please note that tags are save using one table:
 * - All values are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_schoolyear extends tag_base_options {

    /** @var string tagtype of this element */
    protected $name = 'schoolyear';

    /**
     * Add a suitable formelement to given moodleform.
     *
     * @param \MoodleQuickForm $mform
     */
    public function add_formelement(&$mform) {

        $items = [];
        $options = $this->get_options();
        foreach ($options as $key => $value) {
            $items[] = $mform->createElement('checkbox', $key, '', $value, ['value' => $key]);
        }

        $mform->addElement('group', 'schoolyear', get_string('schoolyear', 'block_mbsteachshare'), $items);
        $mform->addHelpButton('schoolyear', 'schoolyear', 'block_mbsteachshare');
    }

    /**
     * This is called when the plugin is to be installed or upgraded only.
     */
    public function create_tag_default_options() {
        global $DB;

        $schoolyears = ['Jgst 1', 'Jgst 2', 'Jgst 3', 'Jgst 4', 'Jgst 5', 'Jgst 6', 'Jgst 7', 'Jgst 8',
                'Jgst 9', 'Jgst 10', 'Jgst 11', 'Jgst 12', 'Jgst 13'];

        $existingschoolyears = $DB->get_fieldset_select('hub_tag_options', 'value', 'tagtype = ?', [$this->name]);

        foreach ($schoolyears as $schoolyear) {

            // Only insert new schoolyears to DB.
            if (!in_array($schoolyear, $existingschoolyears)) {

                $record = (object) [
                    'tagtype' => $this->name,
                    'value' => $schoolyear
                ];
                $DB->insert_record('hub_tag_options', $record);
            }
        }
    }

}
