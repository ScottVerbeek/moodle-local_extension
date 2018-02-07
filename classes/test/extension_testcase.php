<?php
// This file is part of Extension Plugin
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
 * @package     local_extension
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extension\test;

use advanced_testcase;
use local_extension\request;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * @package     local_extension
 * @author      Daniel Thee Roperto <daniel.roperto@catalyst-au.net>
 * @copyright   2018 Catalyst IT Australia {@link http://www.catalyst-au.net}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class extension_testcase extends advanced_testcase {
    protected function create_request($userid, $searchstart = null, $searchend = null, $lastmod = null) {
        global $DB;

        $request = (object)[
            'userid'      => $userid,
            'lastmodid'   => $userid,
            'lastmod'     => $lastmod,
            'searchstart' => $searchstart ?: time(),
            'searchend'   => $searchend ?: time(),
            'timestamp'   => $lastmod ?: time(),
        ];

        $requestid = $DB->insert_record('local_extension_request', $request);

        $request = new request($requestid);
        $request->load();
        return $request;
    }
}