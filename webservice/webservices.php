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
 * Hub XML-RPC web service entry point. The authentication is done via hub tokens (hidden).
 *
 * @package   localhub
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// disable moodle specific debug messages and any errors in output
define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);

require('../../../config.php');
require_once($CFG->dirroot."/local/hub/webservice/locallib.php");

if (!webservice_protocol_is_enabled('xmlrpc') and !get_config('local_hub', 'hubenabled')) {
    die("Webservice is disabled!");
}

$server = new hub_webservice_xmlrpc_server(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
$server->run();
die;
