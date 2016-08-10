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
 * Adpater trigger rules.
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_extension\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->libdir . '/formslib.php');

/**
 * A form to enter in adapter trigger rules.
 *
 * @package    local_extension
 * @author     Nicholas Hoobin <nicholashoobin@catalyst-au.net>
 * @copyright  Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule extends \moodleform {
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition() {
        $mform = $this->_form;

        $datatype = $this->_customdata['datatype'];
        $parents = $this->_customdata['parents'];
        $editordata = $this->_customdata['editordata'];

        // Edit Rule Header.
        $mform->addElement('header', 'name_set', get_string('form_rule_header_edit', 'local_extension'), null, null);
        $mform->setExpanded('name_set');

        // Name.
        $mform->addElement('text', 'name', get_string('form_rule_label_name', 'local_extension'), '');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required');

        // Priority.
        $optionspriority = array();
        for ($i = 1; $i <= 10; $i++) {
            $optionspriority[] = $i;
        }

        $mform->addElement('select', 'priority', get_string('form_rule_label_priority', 'local_extension'), $optionspriority);

        // Only activate when [parent] has been triggered.
        $parentgroup = array();

        $optionsparent = array('N/A');
        foreach ($parents as $id => $name) {
            $optionsparent[$id] = $name;
        }

        $parentgroup[] = $mform->createElement('select', 'parent', null, $optionsparent);
        $parentgroup[] = $mform->createElement('static', 'hastriggered', '', get_string('form_rule_label_parent_end', 'local_extension'));

        $mform->addGroup($parentgroup, 'parentgroup', get_string('form_rule_label_parent', 'local_extension'), array(' '), false);

        // If the requested length is [lt/ge] [x] days long.
        $lengthfromduedate = array();

        $lengthtypes = array(
            \local_extension\rule::RULE_CONDITION_LT => get_string('form_rule_less_than', 'local_extension'),
            \local_extension\rule::RULE_CONDITION_GE => get_string('form_rule_greater_or_equal', 'local_extension'),
            \local_extension\rule::RULE_CONDITION_ANY => get_string('form_rule_any_value', 'local_extension'),
        );

        $lengthfromduedategroup[] = $mform->createElement('select', 'lengthtype', '', $lengthtypes);
        $lengthfromduedategroup[] = $mform->createElement('text', 'lengthfromduedate', '');
        $lengthfromduedategroup[] = $mform->createElement('static', 'dayslong', '', get_string('form_rule_label_days_long', 'local_extension'));

        $mform->setType('lengthfromduedate', PARAM_INT);
        $mform->addGroup($lengthfromduedategroup, 'lengthfromduedategroup', get_string('form_rule_label_request_length', 'local_extension'), array(' '), false);
        //$mform->addGroupRule('lengthfromduedategroup', get_string('err_numeric', 'form'), 'numeric', 'server');

        // and the request is [lt/ge] [x] days old.
        $elapsedtime = array();

        $elapsedtimegroup[] = $mform->createElement('select', 'elapsedtype', '', $lengthtypes);
        $elapsedtimegroup[] = $mform->createElement('text', 'elapsedfromrequest', '');
        $elapsedtimegroup[] = $mform->createElement('static', 'daysold', '', get_string('form_rule_label_days_old', 'local_extension'));

        $mform->setType('elapsedfromrequest', PARAM_INT);
        $mform->addGroup($elapsedtimegroup, 'elapsedtimegroup', get_string('form_rule_label_elapsed_length', 'local_extension'), array(' '), false);
        //$mform->addGroupRule('elapsedtimegroup', get_string('err_numeric', 'form'), 'numeric', 'server');

        // then set all roles equal to [roletypes] to [action] this request.
        $actionarray = array();

        $actiontypes = array(
            \local_extension\rule::RULE_ACTION_APPROVE   => get_string('form_rule_select_approve', 'local_extension'),
            \local_extension\rule::RULE_ACTION_SUBSCRIBE => get_string('form_rule_select_subscribe', 'local_extension'),
        );

        $roletypes = role_get_names(\context_system::instance(), ROLENAME_ALIAS, true);

        $actiongroup[] = $mform->createElement('select', 'role', null, $roletypes);
        $actiongroup[] = $mform->createElement('static', 'to', '', get_string('form_rule_label_to', 'local_extension'));
        $actiongroup[] = $mform->createElement('select', 'action', null, $actiontypes);
        $actiongroup[] = $mform->createElement('static', 'approve', '', get_string('form_rule_label_this_request', 'local_extension'));

        $mform->addGroup($actiongroup, 'actiongroup', get_string('form_rule_label_set_roles', 'local_extension'), array(' '), false);

        // Email templates.
        $mform->addElement('text', 'template_notify_subject', get_string('form_rule_label_template', 'local_extension'));
        $mform->setType('template_notify_subject', PARAM_RAW);

        $editor_notify = $mform->addElement('editor', 'template_notify' );
        $mform->setType('template_notify', PARAM_RAW);
        $editor_notify->setValue($editordata['template_notify']);

        $mform->addElement('text', 'template_user_subject', get_string('form_rule_label_template_request', 'local_extension'));
        $mform->setType('template_user_subject', PARAM_RAW);

        $editor_user = $mform->addElement('editor', 'template_user');
        $mform->setType('template_user', PARAM_RAW);
        $editor_user->setValue($editordata['template_user']);

        // ID.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // Context.
        $mform->addElement('hidden', 'context', 1);
        $mform->setType('context', PARAM_INT);

        $mform->addElement('hidden', 'datatype', $datatype);
        $mform->setType('datatype', PARAM_ALPHANUM);

        $this->add_action_buttons();
    }

    /**
     * Validate the parts of the request form for this module
     *
     * @param array $data An array of form data
     * @param array $files An array of form files
     * @return array of error messages
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $mform = $this->_form;

        if ($data['lengthfromduedate'] < 1) {
            //$errors['lengthfromduedategroup'] = get_string('form_rule_validate_greater_than_zero', 'local_extension');
        }

        if ($data['elapsedfromrequest'] < 1) {
            //$errors['elapsedtimegroup'] = get_string('form_rule_validate_greater_than_zero', 'local_extension');
        }

        return $errors;
    }

}