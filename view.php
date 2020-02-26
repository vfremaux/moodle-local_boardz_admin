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
 * @package     local_boardz_admin
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/boardz_admin/classes/admin_api.class.php');
// Security.

$context = context_system::instance();
require_login();
require_capability('local/boardz_admin:manage', $context);

// Page preparation.

$view = required_param('view', PARAM_ALPHA);
$entity = preg_replace('/s$/', '', $view);
$url = new moodle_url('/local/boardz_admin/view.php', ['view' => $view]);
$action = optional_param('what', '', PARAM_ALPHA);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_boardz_admin'));
$PAGE->set_heading(get_string('pluginname', 'local_boardz_admin'));
$PAGE->navbar->add(get_string('pluginname', 'local_boardz_admin'));
$indexurl = new moodle_url('/local/boardz_admin/index.php');
$PAGE->navbar->add(get_string('boardzserver', 'local_boardz_admin'), $indexurl);
$PAGE->navbar->add(get_string('admin'.$view, 'local_boardz_admin'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('local_boardz_admin/clipboard', 'init');

if (!empty($action)) {
    include_once($CFG->dirroot.'/local/boardz_admin/view.controller.php');
    $controller = new \local_boardz_admin\view_controller();
    $controller->receive($action);
    $returnurl = $controller->process($action);
    if (!empty($returnurl)) {
        redirect($returnurl);
    }
}

$renderer = $PAGE->get_renderer('local_boardz_admin');

// Controller.

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('admin'.$view, 'local_boardz_admin'), 1);

$cmd = 'admin_get_objects';
$params = ['entity' => $entity];
local_boardz_admin_add_filter($params);
$entities = \boardz\admin_api::call($cmd, $params);

$cmd = 'admin_defines';
$defines = (array) \boardz\admin_api::call($cmd, ['entity' => $entity]);

echo $renderer->admin_list_filter_form();

echo $renderer->admin_list($entities, $view, $defines);

echo $OUTPUT->footer();