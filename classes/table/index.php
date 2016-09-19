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
 * The flexible_table for index.php class
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extension\table;
use flexible_table;
use moodle_url;

require_once($CFG->libdir . '/tablelib.php');

/**
 * The flexible_table for index.php class
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class index extends flexible_table {

    /** @var int Incrementing table id. */
    private static $autoid = 0;

    /**
     * Constructor
     * @param moodle_url $baseurl
     * @param string|null $id to be used by the table, autogenerated if null.
     */
    public function __construct($baseurl, $id = null) {
        $id = (is_null($id) ? self::$autoid++ : $id);
        parent::__construct('local_extension' . $id);

        $columns = array(
            'rid',
            'userpic',
            'fullname',
            'timestamp',
            'length',
            'coursename',
            'activity',
            'state',
            'lastmod',
        );

        $headers = array(
            get_string('table_header_index_requestid', 'local_extension'),
            get_string('table_header_index_user', 'local_extension'),
            get_string('fullnameuser'),
            get_string('table_header_index_requestdate', 'local_extension'),
            get_string('table_header_index_requestlength', 'local_extension'),
            get_string('table_header_index_course', 'local_extension'),
            get_string('table_header_index_activity', 'local_extension'),
            get_string('table_header_index_status', 'local_extension'),
            get_string('table_header_index_lastmod', 'local_extension'),
        );

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->define_baseurl($baseurl);

        $this->set_attribute('class', 'generaltable admintable');
        $this->set_attribute('cellspacing', '0');

        $this->no_sorting('userpic');
        $this->sortable('requestid');

        $this->setup();
    }

}
