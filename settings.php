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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/boardz_admin/lib.php');

/**
 * @package    local_boardz_admin
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// settings default init
if (is_dir($CFG->dirroot.'/local/adminsettings')) {
    // Integration driven code.
    require_once($CFG->dirroot.'/local/adminsettings/lib.php');
    list($hasconfig, $hassiteconfig, $capability) = local_adminsettings_access('local_courseindex');
} else {
    // Standard Moodle code.
    $hasconfig = $hassiteconfig = has_capability('moodle/site:config', context_system::instance());
}

if ($hassiteconfig) {

    // Needs this condition or there is error on login page.

    $settings = new admin_settingpage('local_boardz_admin', get_string('pluginname', 'local_boardz_admin'));
    $ADMIN->add('localplugins', $settings);

    $ADMIN->add('root', new admin_category('categ_boardz_admin', get_string('boardzserver', 'local_boardz_admin')));

    $url = new moodle_url('/local/boardz_admin/index.php');
    $extpage = new admin_externalpage('local_boardz_admin_index', get_string('index', 'local_boardz_admin'), $url, 'local/boardz_admin:manage');
    $ADMIN->add('categ_boardz_admin', $extpage);

    $key = 'local_boardz_admin/baseurl';
    $label = get_string('configbaseurl', 'local_boardz_admin');
    $desc = get_string('configbaseurl_desc', 'local_boardz_admin');
    $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

    $key = 'local_boardz_admin/tcpport';
    $label = get_string('configtcpport', 'local_boardz_admin');
    $desc = get_string('configtcpport_desc', 'local_boardz_admin');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '80'));

    $key = 'local_boardz_admin/admintoken';
    $label = get_string('configadmintoken', 'local_boardz_admin');
    $desc = get_string('configadmintoken_desc', 'local_boardz_admin');
    $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

    $key = 'local_boardz_admin/viewtoken';
    $label = get_string('configviewtoken', 'local_boardz_admin');
    $desc = get_string('configviewtoken_desc', 'local_boardz_admin');
    $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

    if (local_boardz_admin_supports_feature('emulate/community') == 'pro') {
        include_once($CFG->dirroot.'/local/boardz_admin/pro/prolib.php');
        \local_boardz_admin\pro_manager::add_settings($ADMIN, $settings);
    } else {
        $label = get_string('plugindist', 'local_boardz_admin');
        $desc = get_string('plugindist_desc', 'local_boardz_admin');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }
}

