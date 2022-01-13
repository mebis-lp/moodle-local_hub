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
 * Event Observers for local_hub
 *
 * @package   local_hub
 * @copyright 2022 ISB Bayern
 * @author    Peter Mayer
 * @license   http://www.gnu.org/copyeft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\local_hub\event\backup_uploaded',
        'callback' => '\local_hub\local\local_hub_helper::callback_make_demo_course',
        'includefile' => '/local/hub/classes/local/local_hub_helper.php',
        'internal' => true
    ],
];
