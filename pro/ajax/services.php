<?php
// This file is NOT part of Moodle - http://moodle.org/
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

/*
 * @package    local_boardz_admin
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright  Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Ajax services for Local boardz_admin.
 */
require('../../../../config.php');
require_once($CFG->dirroot.'/local/boardz_admin/pro/prolib.php');

$action = required_param('what', PARAM_TEXT);

// Security.

if ($action == 'license') {
    $customerkey = required_param('customerkey', PARAM_TEXT);
    $provider = required_param('provider', PARAM_TEXT);
    if (function_exists('debug_trace')) {
        debug_trace('Firing '.$customerkey.' to provider '.$provider);
    }
    $result = \local_boardz_admin\pro_manager::set_and_check_license_key($customerkey, $provider);
    echo $result;
    die;
}