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

// Security.

$context = context_system::instance();
require_login();
require_capability('local/boardz_admin:manage', $context);

// Page preparation.

$url = new moodle_url('/local/boardz_admin/index.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_boardz_admin'));
$PAGE->set_heading(get_string('pluginname', 'local_boardz_admin'));
$PAGE->set_pagelayout('admin');

$renderer = $PAGE->get_renderer('local_boardz_admin');

// Controller.

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('boardzperf', 'local_boardz_admin'), 1);

echo $renderer->back_to_main_link();

echo $OUTPUT->footer();
