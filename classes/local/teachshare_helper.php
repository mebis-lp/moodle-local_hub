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
 * This page display the publication metadata form
 *
 * @package    tool_customhub
 * @author     Jerome Mouneyrac <jerome@mouneyrac.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
// defined('MOODLE_INTERNAL') || die();

namespace  local_hub\local;

global $CFG;

use block_mbsteachshare\local\template;

class teachshare_helper {

    /**
     * Insert template, add backupfile and schedule deployment.
     *
     * Sample request: curl http://localhost/mbsmoodle/webservice/rest/server.php?wstoken=444cb15d64671fcc6ca1972893157196 -d "wsfunction=block_mbsteachshare_receive_template"
     * @return array list of oer course templates
     */
    public static function initialize_workflow($coursedata, $templatemeta, $userdatacmids, $excludedeploydatacmids, $backupfile, $filename) {
        global $CFG;

        $fo = fopen(__DIR__ . "/log.txt", "a+");
        fwrite($fo, "\ntsPOS1");

        fwrite($fo, "\n" . json_encode(template::create($coursedata, $userdatacmids, $excludedeploydatacmids)));

        if (!$templatemeta = template::create($coursedata, $userdatacmids, $excludedeploydatacmids)) {
            print_error('errorcreatingtemplate', 'block_mbsteachshare');
        }

        // Write backupfile.
        $target = $CFG->dataroot . '/' . \block_mbsteachshare\backup::BACKUP_LOCALPATH . '/backup/ ' . $filename;
        if (!file_put_contents($target, $backupfile)) {
            return false;
        }

        // Request a primary deployment.
        template::set_status($templatemeta, template::STATUS_REQUESTED);
        return true;
    }
}
