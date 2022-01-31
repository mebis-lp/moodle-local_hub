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
 * Store a description for a template
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
 * Store a coursename for a template only for primary restore. After backup::launch_primary_restore it is deleted.
 *
 * Please note that tags are save using tables:
 * - All values are stored in hub_tag_options
 * - Relationsship between values and template are stored in table hub_tag
 *
 * @package    local_hub
 * @copyright 2018 Franziska Hübler, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tag_coursename extends tag_base {

    /** @var string tagtype of this element */
    protected $name = 'coursename';

    /**
     * Add a suitable formelement to given moodleform.
     *
     * @param \MoodleQuickForm $mform
     */
    public function add_formelement(&$mform) {
        $mform->addElement('text', 'coursename', get_string('coursename', 'block_mbsteachshare'));
        $mform->setType('coursename', PARAM_TEXT);
        $mform->addRule('coursename', null, 'required', null, 'client');
    }

}