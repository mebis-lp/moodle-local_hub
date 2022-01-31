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
 * @package    local_hub
 * @copyright  2022 ISB Bayern
 * @author     Peter Mayer <peter.mayer@isb.bayern.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_hub\local;

use stdClass;

defined('MOODLE_INTERNAL') || die();

class tag_options {

    /**
     * @var array
     */
    public static $registeredtagnames = [
        'subject', 'schooltype', 'schoolyear', 'compuse', 'description', 'tags', 'oer', 'coursename',
    ];

    /**
     * @var array
     */
    public static $formtagtypes = [
        'subject', 'schooltype', 'schoolyear', 'compuse', 'oer',
    ];

    /**
     * @param $name
     */
    public static function get_tag_instance($name) {

        $classname = '\local_hub\tags\tag_' . $name;
        if (!class_exists($classname)) {
            print_error('tagnamenotimplemented', 'local_hub', '', $name);
        }

        return new $classname();
    }

    /**
     * @param $mform
     * @param $names
     */
    public static function add_formelements(&$mform, $names) {

        foreach ($names as $name) {
            $tag = self::get_tag_instance($name);
            $tag->add_formelement($mform);
        }
    }

    /**
     * @param $templateid
     * @param $data
     */
    public static function save($templateid, $data) {

        foreach (self::$registeredtagnames as $name) {
            $tag = self::get_tag_instance($name);
            $tag->save($templateid, $data);
        }
    }

    /**
     * @param $templateid
     * @return mixed
     */
    public static function get_template_tags($templateid) {
        global $DB;

        $sqltags   = "SELECT {hub_tag_options}.* FROM {hub_tag_options}, {hub_tag} WHERE {hub_tag}.templateid = ? AND
         {hub_tag}.optionid={hub_tag_options}.id";
        $resulttag = $DB->get_records_sql($sqltags, [$templateid]);

        $tags           = (object) [];
        $tags->tags     = "";
        $tmpschooltype = [];
        $tmpschoolyear = [];
        $tmpsubject    = [];
        $tmpcompuse    = [];
        foreach ($resulttag as $val) {
            switch ($val->tagtype) {
                case 'schooltype':
                    $tmpschooltype[$val->id] = '1';
                    break;
                case 'schoolyear':
                    $tmpschoolyear[$val->id] = '1';
                    break;
                case 'compuse':
                    $tmpcompuse[$val->id] = '1';
                    break;
                case 'subject':
                    $tmpsubject[] = $val->id;
                    break;
                case 'description':
                    $tags->description = $val->value;
                    break;
                case 'tags':
                    $tags->tags .= $val->value . ",";
                    break;
                case 'oer':
                    $tags->oer = 1;
                    break;
                default:
                    // Code...
                    break;
            }
        }
        $tags->schooltype = $tmpschooltype;
        $tags->schoolyear = $tmpschoolyear;
        $tags->subject    = $tmpsubject;
        $tags->compuse    = $tmpcompuse;
        $tags->tags       = substr($tags->tags, 0, -1);
        return $tags;
    }

    /**
     * Create options for the available tag types. Called by install or upgrade.
     */
    public static function create_tag_defaultoptions() {

        // Create schooltypes.
        foreach (self::$registeredtagnames as $name) {

            $tag = self::get_tag_instance($name);
            if (method_exists($tag, 'create_tag_default_options')) {
                $tag->create_tag_default_options();
            }
        }
    }

    /**
     * Delete options of a specific template.
     *
     * @param int $templateid Template id which tags to delete.
     * @param array $optionids Ids of options to delete.
     */
    public static function delete($templateid, $optionids) {
        global $DB;

        $DB->delete_records('hub_tag', ['templateid' => $templateid]);

        // Nothing to do anymore, if no options are set for a tag
        if (!$optionids) {
            return;
        }
        
        list($insql, $inparams) = $DB->get_in_or_equal(array_values($optionids), SQL_PARAMS_NAMED);
        $options                = $DB->get_recordset_select('hub_tag_options', "id $insql", $inparams);
        foreach ($options as $option) {
            $tag = self::get_tag_instance($option->tagtype);
            $tag->delete_option($option->id);
        }
        $options->close();
    }

    /**
     * Get all tags that schould be used in forms.
     * @return array
     */
    public static function get_all_formtags () {
        global $DB;
        $returntags = new stdClass();
        foreach (self::$formtagtypes as $tagtype) {
            $tags = $DB->get_records('hub_tag_options', ['tagtype' => $tagtype]);
            foreach ($tags as $tag) {
                $returntags->{$tagtype}[] = (object)[
                    'id' => $tag->id,
                    'text' => $tag->value,
                ];
            }
        }
        return $returntags;
    }

}
