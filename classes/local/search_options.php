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

class search_options {

    const PUBLISH_TYPE_ALL = 2;
    const PUBLISH_TYPE_ENROLLABLE = 1;
    const PUBLISH_TYPE_DOWNLOADABLE = 0;
    const IS_ENROLLABLE = 1;
    const IS_DOWNLOADABLE = 0;
    

    /**
     * Get instance of search_options.
     * @return search_options
     */
    public static function get_instance() : search_options {
        static $helper;

        // Force new instance while executing unit test as config may have
        // changed in various testcases.
        $forcenewinstance = (defined('PHPUNIT_TEST') && PHPUNIT_TEST);

        if (isset($helper) && !$forcenewinstance) {
            return $helper;
        }
        $helper = new search_options();
        return $helper;
    }

    public static function get_sort_options() {
        return [
            [
                'value' => 'relevance',
                'text' => get_string('relevance', 'local_hub'),
                // TODO:
                'col' => 'timepublished',
                'direction' => 'desc'
            ],
            [
                'value' => 'rating',
                'text' => get_string('ratingsort', 'local_hub'),
                // TODO:
                'col' => 'timepublished',
                'direction' => 'desc'
            ],
            [
                'value' => 'date',
                'text' => get_string('datedesc', 'local_hub'),
                'col' => 'timepublished',
                'direction' => 'desc'
            ],
            [
                'value' => 'dateReverse',
                'text' => get_string('dateasc', 'local_hub'),
                'col' => 'timepublished',
                'direction' => 'asc'
            ],
            [
                'value' => 'alphabet',
                'text' => get_string('alphabetasc', 'local_hub'),
                'col' => 'fullname',
                'direction' => 'asc'
            ],
            [
                'value' => 'alphabetReverse',
                'text' => get_string('alphabetdesc', 'local_hub'),
                'col' => 'fullname',
                'direction' => 'desc'
            ],
        ];
        // return $sortorderoptions;
    }

    public static function get_searchfor_options() {
        return [
            [
                'value' => self::PUBLISH_TYPE_ALL,
                'text' => get_string('all'),
            ],
            [
                'value' => self::PUBLISH_TYPE_DOWNLOADABLE,
                'text' => get_string('downloadable', 'local_hub'),
            ],
            [
                'value' => self::PUBLISH_TYPE_ENROLLABLE,
                'text' => get_string('enrollable', 'local_hub'),
            ],
        ];
        // return $searchforoptions;
    }

}