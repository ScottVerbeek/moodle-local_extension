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
 * Request state class.
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extension;

use coding_exception;
use html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Request state class.
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state {
    /** @var state An instance of the state class. */
    private static $instance;

    /** @var int New request. */
    const STATE_NEW = 1;

    /** @var int Approved request. */
    const STATE_APPROVED = 2;

    /** @var int Denied request. */
    const STATE_DENIED = 3;

    /** @var int Reopened request. */
    const STATE_REOPENED = 4;

    /** @var int Cancelled request. */
    const STATE_CANCEL = 5;

    /** @var array An array of state ids */
    public $statearray = array();

    /**
     * Obtain an instance of this state machine.
     *
     * @return state
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new state();
        }

        return self::$instance;
    }

    /**
     * The state constructor.
     */
    protected function __construct() {
        $this->statearray = array(
            self::STATE_NEW => 'new',
            self::STATE_APPROVED => 'approve',
            self::STATE_DENIED => 'deny',
            self::STATE_REOPENED => 'reopen',
            self::STATE_CANCEL => 'cancel',
        );
    }

    /**
     * Protected clone.
     */
    protected function __clone() {
    }

    /**
     * Returns true if the specified state is in the window for length modification.
     *
     * @param int $stateid
     * @return bool
     */
    public static function can_modify_length_state($stateid) {
        switch ($stateid) {
            case self::STATE_NEW;
                return true;
            case self::STATE_REOPENED:
                return true;
            default:
                return false;
        }
    }

    /**
     * Returns a human readable state name.
     *
     * @param int $stateid State id.
     * @param boolean $raw If true, return the string only.
     * @throws \coding_exception
     * @return string the human-readable status name.
     */
    public function get_state_name($stateid, $raw = false) {
        switch ($stateid) {
            case self::STATE_NEW:
                $str = get_string('state_new',      'local_extension');
                if ($raw) {
                    return $str;
                }

                return html_writer::span($str, 'statusnew label');

            case self::STATE_DENIED:
                $str = get_string('state_denied',   'local_extension');
                if ($raw) {
                    return $str;
                }

                return html_writer::span($str, 'statusdenied label');

            case self::STATE_APPROVED:
                $str = get_string('state_approved', 'local_extension');
                if ($raw) {
                    return $str;
                }

                return html_writer::span($str, 'statusapproved label');

            case self::STATE_REOPENED:
                $str = get_string('state_reopened', 'local_extension');
                if ($raw) {
                    return $str;
                }

                return html_writer::span($str, 'statusreopened label');

            case self::STATE_CANCEL:
                $str = get_string('state_cancel',   'local_extension');
                if ($raw) {
                    return $str;
                }

                return html_writer::span($str, 'statuscancel label');

            default:
                throw new coding_exception('Unknown cm state.');
        }
    }

    /**
     * Returns a string based on the state result.
     *
     * @param int $stateid State id.
     * @return string
     * @throws \coding_exception
     */
    public function get_state_result($stateid) {
        switch ($stateid) {
            case self::STATE_NEW:
            case self::STATE_REOPENED:
                return get_string('state_result_pending',   'local_extension');
            case self::STATE_DENIED:
                return get_string('state_result_denied',    'local_extension');
            case self::STATE_APPROVED:
                return get_string('state_result_approved',  'local_extension');
            case self::STATE_CANCEL:
                return get_string('state_result_cancelled', 'local_extension');
            default:
                throw new coding_exception('Unknown cm state.');
        }
    }

    /**
     * Returns true if the state is open/pending for a given $stateid.
     *
     * @param int $stateid
     * @return bool
     */
    public function is_open_state($stateid) {

        switch ($stateid) {
            case self::STATE_NEW:
            case self::STATE_REOPENED:
                return true;

            case self::STATE_DENIED:
            case self::STATE_APPROVED:
            case self::STATE_CANCEL:
                return false;

            default:
                return false;
        }

    }

    /**
     * Renders the approve buttons for a standard user that can approve or deny an extension.
     *
     * @param \MoodleQuickForm $mform
     * @param int $state
     * @param int $id
     */
    public function render_approve_buttons(&$mform, $state, $id) {
        $buttonarray = array();

        $approvestr = get_string('state_button_approve', 'local_extension');
        $denystr = get_string('state_button_deny', 'local_extension');

        $deny = $this->statearray[self::STATE_DENIED];
        $approve = $this->statearray[self::STATE_APPROVED];

        switch ($state) {
            case self::STATE_NEW:
                $buttonarray[] = $mform->createElement('submit', $approve . $id, $approvestr);
                $buttonarray[] = $mform->createElement('submit', $deny . $id, $denystr);
                break;
            case self::STATE_REOPENED:
                $buttonarray[] = $mform->createElement('submit', $approve . $id, $approvestr);
                $buttonarray[] = $mform->createElement('submit', $deny . $id, $denystr);
                break;
            default:
                break;
        }

        if (!empty($buttonarray)) {
            $mform->addGroup($buttonarray, 'statusmodgroup' . $id, '', ' ', false);
        }
    }

    /**
     * Renders the approve buttons for a the owner of the request/cms.
     *
     * @param \MoodleQuickForm $mform
     * @param int $state
     * @param int $id
     */
    public function render_owner_buttons(&$mform, $state, $id) {
        $buttonarray = array();

        $cancelstr = get_string('state_button_cancel', 'local_extension');
        $reopenstr = get_string('state_button_reopen', 'local_extension');

        $cancel = $this->statearray[self::STATE_CANCEL];
        $reopen = $this->statearray[self::STATE_REOPENED];

        switch ($state) {
            case self::STATE_NEW:
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            case self::STATE_REOPENED:
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            case self::STATE_CANCEL:
                $buttonarray[] = $mform->createElement('submit', $reopen . $id, $reopenstr);
                break;
            default:
                break;
        }

        if (!empty($buttonarray)) {
            $mform->addGroup($buttonarray, 'statusmodgroup' . $id, '', ' ', false);
        }
    }

    /**
     * Renders the approve buttons for an admin user that can approve, deny or cancel an extension.
     *
     * @param \MoodleQuickForm $mform
     * @param int $state
     * @param int $id
     */
    public function render_force_buttons(&$mform, $state, $id) {
        $buttonarray = array();

        $approvestr = get_string('state_button_approve', 'local_extension');
        $cancelstr = get_string('state_button_cancel', 'local_extension');
        $denystr = get_string('state_button_deny', 'local_extension');
        $reopenstr = get_string('state_button_reopen', 'local_extension');

        $deny = $this->statearray[self::STATE_DENIED];
        $approve = $this->statearray[self::STATE_APPROVED];
        $cancel = $this->statearray[self::STATE_CANCEL];
        $reopen = $this->statearray[self::STATE_REOPENED];

        switch ($state) {
            case self::STATE_NEW:
                $buttonarray[] = $mform->createElement('submit', $approve . $id, $approvestr);
                $buttonarray[] = $mform->createElement('submit', $deny . $id, $denystr);
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            case self::STATE_REOPENED:
                $buttonarray[] = $mform->createElement('submit', $approve . $id, $approvestr);
                $buttonarray[] = $mform->createElement('submit', $deny . $id, $denystr);
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            case self::STATE_DENIED:
                $buttonarray[] = $mform->createElement('submit', $approve . $id, $approvestr);
                $buttonarray[] = $mform->createElement('submit', $reopen . $id, $reopenstr);
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            case self::STATE_CANCEL:
                $buttonarray[] = $mform->createElement('submit', $reopen . $id, $reopenstr);
                break;
            case self::STATE_APPROVED:
                $buttonarray[] = $mform->createElement('submit', $deny . $id, $denystr);
                $buttonarray[] = $mform->createElement('submit', $cancel . $id, $cancelstr);
                break;
            default:
                break;
        }

        if (!empty($buttonarray)) {
            $mform->addGroup($buttonarray, 'statusmodgroup' . $id, '', ' ', false);
        }
    }

    /**
     * If the $data object contains a state change reference, redirect the page to an intermediate acknowledgement.
     *
     * @param stdClass $data
     * @param request $request
     */
    public function has_submitted_state($data, $request) {
        // Iterate over the request mods to obtain the cmid.
        foreach ($request->mods as $id => $mod) {
            // Iterate over the possible states.
            foreach ($this->statearray as $state => $name) {
                // A state could be approve19.
                $item = $name . $id;

                // We found it! The state has changed.
                if (!empty($data->$item)) {
                    $cm = $mod->cm;
                    $params = array(
                        'id' => $request->requestid,
                        'course' => $cm->course,
                        'cmid' => $cm->id,
                        's' => $state,
                    );

                    redirect(new moodle_url('/local/extension/state.php', $params));
                }
            }
        }
    }

    /**
     * Updates the cm state with posted data.
     *
     * @param \local_extension\request $request
     * @param int $user
     * @param stdClass $data
     * @return object|bool
     */
    public function update_cm_state($request, $user, $data) {

        $mod = $request->mods[$data->cmid];

        $handler = $mod->handler;

        $localcm = $mod->localcm;
        $event   = $mod->event;
        $course  = $mod->course;

        // The form data passed through contains the state.
        $state = $data->s;

        // The extension has been approved. Lets hook into the handler and extend the items length.
        if ($state == self::STATE_APPROVED) {
            $handler->submit_extension($event->instance,
                                       $request->request->userid,
                                       $localcm->cm->data);

        } else if ($state == self::STATE_CANCEL ||
                   $state == self::STATE_DENIED) {
            $handler->cancel_extension($event->instance,
                                       $request->request->userid);
        }

        $ret = $localcm->set_state($state);
        if (empty($ret)) {
            return false;
        }

        $status = $this->get_state_name($localcm->cm->state);

        // After writing the history it will return the ID of the new row.
        $history = $localcm->write_history($mod, $state, $user->id);

        $log = new stdClass();
        $log->status = $status;
        $log->course = $course->fullname;
        $log->event = $event->name;

        $history->message = get_string('request_state_history_log', 'local_extension', $log);

        // Update the lastmod.
        $request->update_lastmod($user->id);

        // You can only edit one state at a time, returning here is ok!
        return $history;
    }

    /**
     * When a cm has been approved and the length has been updated then we must call the handler and update the extension.
     *
     * @param \local_extension\request $request
     * @param int $user The user that has called this operation.
     * @param stdClass $data
     * @return object|bool
     */
    public function extend_cm_length($request, $user, $data) {

        $mod = $request->mods[$data->cmid];
        $handler = $mod->handler;

        $localcm = $mod->localcm;
        $event   = $mod->event;
        $course  = $mod->course;

        $handler->cancel_extension($event->instance, $request->request->userid);
        $handler->submit_extension($event->instance, $request->request->userid, $localcm->cm->data);

        $state = $localcm->get_stateid();
        $status = $this->get_state_name($state);

        // After writing the history it will return the ID of the new row.
        $history = $localcm->write_history($mod, $state, $user->id);

        $log = new stdClass();
        $log->status = $status;
        $log->course = $course->fullname;
        $log->event = $event->name;

        $history->message = get_string('request_state_history_log', 'local_extension', $log);

        // Update the lastmod.
        $request->update_lastmod($user->id);

        // You can only edit one state at a time, returning here is ok!
        return $history;
    }

    /**
     * Checks the current state and returns true if the requested state is possible.
     *
     * @param int $currentstate
     * @param int $requestedstate
     * @param bool $approved
     * @return bool
     */
    public function state_is_possible($currentstate, $requestedstate, $approved) {

        $states = array();

        switch ($currentstate) {
            case self::STATE_NEW:
                $states = array(self::STATE_CANCEL);

                if ($approved) {
                    $states[] = self::STATE_APPROVED;
                    $states[] = self::STATE_DENIED;
                }
                break;

            case self::STATE_REOPENED:
                $states = array(self::STATE_CANCEL);

                if ($approved) {
                    $states[] = self::STATE_DENIED;
                    $states[] = self::STATE_APPROVED;
                }
                break;

            case self::STATE_DENIED:
                if ($approved) {
                    $states = array(
                        self::STATE_APPROVED,
                        self::STATE_REOPENED,
                        self::STATE_CANCEL,
                    );
                }
                break;

            case self::STATE_CANCEL:
                $states = array(self::STATE_REOPENED);
                break;

            case self::STATE_APPROVED:
                if ($approved) {
                    $states = array(
                        self::STATE_CANCEL,
                        self::STATE_DENIED,
                    );
                }
                break;

            default:
                break;
        }

        if (in_array($requestedstate, $states)) {
            return true;
        }

        return false;
    }

}
