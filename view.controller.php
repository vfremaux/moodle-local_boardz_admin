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
 * Controller for boardz admin entity view.
 *
 * @package     local_boardz_admin
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_boardz_admin;

defined('MOODLE_INTERNAL') || die();

// Note that other use cases are handled by the edit_catalogue.php script.

class view_controller {

    protected $data;

    protected $received;

    protected $mform;

    public function receive($cmd, $data = array(), $mform = null) {

        $this->mform = $mform;

        if (!empty($data)) {
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'copy': {
                $this->data->uid = required_param('uid', PARAM_TEXT);
                $view = required_param('view', PARAM_TEXT);
                $this->data->entity = preg_replace('/s$/', '', $view);
                break;
            }

            case 'delete': {
                $this->data->uid = required_param('uid', PARAM_TEXT);
                $view = required_param('view', PARAM_TEXT);
                $this->data->entity = preg_replace('/s$/', '', $view);
                break;
            }
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        if ($cmd == 'copy') {

            $cmd = 'admin_copy_object';
            $params = ['entity' => $this->data->entity, 'uid' => $this->data->uid];
            \boardz\admin_api::call($cmd, $params);

            $params = ['view' => $this->data->entity.'s'];
            return new \moodle_url('/local/boardz_admin/view.php', $params);

        } else if ($cmd == 'delete') {

            $cmd = 'admin_delete_object';
            $params = ['entity' => $this->data->entity, 'uid' => $this->data->uid];
            \boardz\admin_api::call($cmd, $params);

            $params = ['view' => $this->data->entity.'s'];
            return new \moodle_url('/local/boardz_admin/view.php', $params);
        }
    }

    public static function info() {
        return [
            'copy' => [
                'uid' => 'ID of item to copy',
                'entity' => 'Name of copied entity'],
            'delete' => [
                'uid' => 'ID of entity to delete',
                'entity' => 'Name of deleted entity'],
        ];
    }
}