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
require_once($CFG->dirroot.'/local/boardz_admin/classes/local_api.class.php');
require_once($CFG->dirroot.'/local/boardz_admin/forms/entity_form.php');
require_once($CFG->dirroot.'/local/boardz_admin/lib.php');
// Security.

$context = context_system::instance();
require_login();
require_capability('local/boardz_admin:manage', $context);
$PAGE->requires->js_call_amd('local_boardz_admin/update', 'init');

// Page preparation.

$entity = required_param('entity', PARAM_ALPHA);
$uid = optional_param('uid', '', PARAM_TEXT); // Unique Object id.
$id = optional_param('id', '', PARAM_TEXT); // Unique Object id.
if (!empty($id)) {
    $url = new moodle_url('/local/boardz_admin/update.php', ['entity' => $entity, 'id' => $id]);
    $action = 'update';
} else {
    $url = new moodle_url('/local/boardz_admin/update.php', ['entity' => $entity]);
    $action = 'new';
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_boardz_admin'));
$PAGE->set_heading(get_string('pluginname', 'local_boardz_admin'));
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('local_boardz_admin');

// Get all defines per usable class.
$cmd = 'admin_defines';
$defines = (array) \boardz\admin_api::call($cmd, ['entity' => $entity]);
$attributes = \boardz\admin_api::process_defines_for_form($defines);
$mform = new entity_form($url, ['classes' => array_keys($defines), 'attributes' => $attributes]);

if ($mform->is_cancelled()) {
    $returnurl = new moodle_url('/local/boardz_admin/view.php', ['view' => $entity.'s']);
    redirect($returnurl);
}

if ($data = $mform->get_data()) {
    // Prepare and send data to server.
    $cmd = 'admin_save_object';
    $id = clean_param(@$_REQUEST['id'], PARAM_INT); // Fails comming in form return.
    $entity = clean_param($_REQUEST['entity'], PARAM_TEXT); // Fails comming in form return.
    $params = boardz_remap_data_before_call($data, $attributes);
    $params->entity = $entity;
    $params->id = $id;
    \boardz\admin_api::call($cmd, $params);
    $returnurl = new moodle_url('/local/boardz_admin/view.php', ['view' => $entity.'s']);
    redirect($returnurl);
}

if ($uid) {
    // Get the object info to load into the form.
    $cmd = 'admin_get_object';
    $entityrecord = \boardz\admin_api::call($cmd, ['entity' => $entity, 'uid' => $uid]);

    boardz_process_defined_form_values($entityrecord, $attributes);
    $entityrecord->entity = $entity;
    $entityrecord->siteid = $CFG->wwwroot; // Do NOT rely on internal siteid from boardz.
    $mform->set_data($entityrecord);
/*
// Should never use internal ids.
} else if ($id) {
    // Get the object info to load into the form.
    $cmd = 'admin_get_object';
    $entityrecord = \boardz\admin_api::call($cmd, ['entity' => $entity, 'id' => $id]);

    boardz_process_defined_form_values($entityrecord, $attributes);
    $entityrecord->entity = $entity;
    $mform->set_data($entityrecord);
*/
} else {
    // New record from scratch.
    $entityrecord = new StdClass;
    boardz_process_defined_form_values($entityrecord, $attributes);
    $entityrecord->entity = $entity;
    $mform->set_data($entityrecord);
}
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string($action.$entity, 'local_boardz_admin'));

$mform->display();

echo $OUTPUT->footer();
