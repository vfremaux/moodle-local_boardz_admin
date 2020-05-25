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
 * @package    local_boardz_admin
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/boardz_admin/classes/admin_api.class.php');
require_once($CFG->dirroot.'/local/boardz_admin/lib.php');

define('AJAX_SCRIPT', 1);

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action = required_param('what', PARAM_ALPHA);
require_sesskey();

/*
 * Unused at the moment. Keep for future use.
 */

switch ($action) {
    case 'import': {
        // Imports an entity.
        $data = required_param('importdata', PARAM_TEXT);
        $jsonobject = base64_decode($data);
        $entitystub = json_decode($jsonobject);
        $entitystub->entity = preg_replace('/s$/', '', $entitystub->entity);

        $cmd = 'admin_defines';
        $defines = (array) \boardz\admin_api::call($cmd, ['entity' => $entitystub->entity]);
        $attributes = \boardz\admin_api::process_defines_for_form($defines);

        $cmd = 'admin_save_object';
        $params = boardz_remap_data_before_call($entitystub->record, $attributes);
        $params->entity = $entitystub->entity;
        // Ensure we have a brand new object.
        $params->name .= ' (Imported)';
        $params->uid = uniqid();
        unset($params->id);
        \boardz\admin_api::call($cmd, $params);
    }
}

