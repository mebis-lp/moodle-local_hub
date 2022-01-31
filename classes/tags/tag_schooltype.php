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
 * Class tags which schooltype the template is assigned to.
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
 * Class tags which schooltype the template is assigned to.
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
class tag_schooltype extends tag_base_options {

    /** @var string tagtype of this element */
    protected $name = 'schooltype';

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

        $mform->addElement('group', 'schooltype', get_string('schooltype', 'block_mbsteachshare'), $items);
        $mform->addRule('schooltype', null, 'required', null, 'client');
        $mform->addHelpButton('schooltype', 'schooltype', 'block_mbsteachshare');
    }

    /**
     * This is called when the plugin is to be installed or upgraded only.
     */
    public function create_tag_default_options() {
        global $DB;

        $schooltypes = [
            'Grundschule',
            'Mittelschule',
            'Realschule',
            'Wirtschaftsschule',
            'Gymnasium',
            'Förderschule',
            'Berufschule',
            'Fachoberschule',
            'Berufsoberschule',
            'Fachschule',
            'Fachakademie'
        ];

        $existingschooltypes = $DB->get_fieldset_select('hub_tag_options', 'value', 'tagtype = ?', [$this->name]);

        foreach ($schooltypes as $schooltype) {

            // Only insert new schooltypes to DB.
            if (!in_array($schooltype, $existingschooltypes)) {
                $record = (object) [
                        'tagtype' => $this->name,
                        'value' => $schooltype
                ];
                $DB->insert_record('hub_tag_options', $record);
            }
        }
    }

}
