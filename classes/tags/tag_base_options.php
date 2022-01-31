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
 * This class handles tags with fixed options (for example set of schooltypes),
 *
 * Please note that tags are save using one table:
 * - All options are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * This means we can handle option values created by user (for example description)
 * the same way as default options like for example schooltype.
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\tags;

defined('MOODLE_INTERNAL') || die();

/**
 * This class handles tags with fixed options (for example set of schooltypes),
 *
 * Please note that tags are save using one table:
 * - All options are already stored in hub_tag_options
 * - Relationsship between template and options are stored in table hub_tag
 *
 * This means we can handle option values created by user (for example description)
 * the same way as default options like for example schooltype.
 *
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class tag_base_options extends tag_base {

    /** @var boolean store key not value returned by formelement, when true */
    protected $usekeys = true;

    /**
     * Get all (fixed) options stored in database for this element
     *
     * @return array values of options indexed by id of options table.
     */
    public function get_options() {
        global $DB;
        return $DB->get_records_menu('hub_tag_options', ['tagtype' => $this->name], '', 'id,value');
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

        // Delete existing entries.
        $sql = "SELECT tag.id
                FROM {hub_tag} tag
                JOIN {hub_tag_options} opt ON  opt.id = tag.optionid
                WHERE opt.tagtype = :tagtype AND tag.templateid = :templateid";

        if ($existids = $DB->get_fieldset_sql($sql, ['tagtype' => $this->name, 'templateid' => $templateid])) {
            $DB->delete_records_list('hub_tag_options', 'id', $existids);
        }

        if (empty($data->{$this->name})) {
            return;
        }

        // Insert new entries.
        foreach ($data->{$this->name} as $optionid => $value) {

            $tag             = new \stdClass();
            $tag->templateid = $templateid;
            $tag->optionid   = ($this->usekeys) ? $optionid : $value;

            $DB->insert_record('hub_tag', $tag);
        }
    }

    /**
     * Create options durgin install or upgrade.
     */
    public function create_tag_default_options() {
        // For some type there is no need to create default options.
    }

    /**
     * Delete specific option/value of tagtype.
     *
     * @param int $optionid Id of option to delete.
     */
    public function delete_option($optionid) {
        // Do nothing for all values which are already stored in hub_tag_options during install.
        // NOTE: This options are used by multiple templates, so prevent deletion here.
    }
}
