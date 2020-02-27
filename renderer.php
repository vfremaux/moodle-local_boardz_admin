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
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 */
defined('MOODLE_INTERNAL') || die();

class local_boardz_admin_renderer extends plugin_renderer_base {

    /**
     * Prints the "like" stars
     */
    public function main_menu() {

        $entities = [
            'widgets' => 1,
            'panels' => 1,
            'indicators' => 1,
            'measurements' => 1,
            'storages' => 1,
            'datasources' => 1,
            'feeders' => 1,
            'parsers' => 1,
            'filters' => 1,
            'renderers' => 1,
        ];

        $template = new StdClass;

        foreach ($entities as $e => $available) {
            $entitytpl = new StdClass;
            $entitytpl->name = get_string($e, 'local_boardz_admin');
            $entitytpl->available = $available;
            $entitytpl->img = $this->output->image_url('entities/'.$e.'200', 'local_boardz_admin');
            $params = ['view' => $e];
            $entitytpl->url = new moodle_url('/local/boardz_admin/view.php', $params);
            $template->entities[] = $entitytpl;
        }

        $template->configurl = new moodle_url('/local/boardz_admin/config.php');
        $template->perfurl = new moodle_url('/local/boardz_admin/perf.php');
        $template->statsurl = new moodle_url('/local/boardz_admin/stats.php');
        $template->statusurl = new moodle_url('/local/boardz_admin/status.php');

        return $this->output->render_from_template('local_boardz_admin/mainmenu', $template);
    }

    /**
     * Renders a list of administrable objects
     * @param array $entities
     * @param string $view
     * @param array $defines the object defines
     */
    public function admin_list($entities, $view, $defines) {
        global $CFG;

        $config = get_config('local_boardz_admin');

        $template = new StdClass;

        $template->view = $view;
        $template->entity = preg_replace('/s$/', '', $view);
        $template->addurl = new moodle_url('/local/boardz_admin/update.php', ['entity' => $template->entity]);
        $template->addstr = get_string('new'.$template->entity, 'local_boardz_admin');
        $template->hasentities = false;

        // print_object($entities);

        if (!empty($entities)) {

            $template->hasentities = true;

            $headersdone = false;

            foreach ($entities as $uid => $entity) {
                $define = $defines[$entity->classname][0];

                $datatpl = new StdClass;
                $i = 0;

                foreach ($entity as $label => $data) {

                    if (in_array($label, ['deleted', 'deletetime', 'uid'])) {
                        continue;
                    }

                    $deletable = true;
                    $mutable = true;
                    if ($label == 'attrs') {
                        $attrsdata = json_decode($data);
                        $attributes = new StdClass;
                        if (!empty($attrsdata)) {
                            foreach ($attrsdata as $k => $v) {

                                if ($k == 'nondeletable') {
                                    $deletable = false;
                                    continue;
                                }

                                if ($k == 'nonmutable') {
                                    $mutable = false;
                                    continue;
                                }

                                $attributetpl = new Stdclass;
                                $attributetpl->name = $k;
                                $attributetpl->class = 'type1';

                                if ($entity->classname == '\\boardz\\data_processing\\MoodleCubeMeasurement') {
                                    if (@$define->attributes->$k->class == 'dimension') {
                                        $attributetpl->class = 'type2';
                                        if (empty($v)) {
                                            $v = '<span class="meta">'.get_string('any', 'local_boardz_admin').'</span>';
                                        }

                                        if ($v == '*') {
                                            $v = '<span class="meta">'.get_string('each', 'local_boardz_admin').'</span>';
                                        }

                                        if ($v == 'a') {
                                            $v = '<span class="meta">'.get_string('agregate', 'local_boardz_admin').'</span>';
                                        }
                                    }
                                }

                                if (is_object($v)) {
                                    $attributetpl->value = json_encode($v);
                                } else if (is_array($v)) {
                                    $attributetpl->value = '['.implode(',', $v).']';
                                } else {
                                    $attributetpl->value = $v;
                                }
                                $attributes->attributes[] = $attributetpl;
                            }
                        }

                        $data = $this->output->render_from_template('local_boardz_admin/attribute', $attributes);
                    }

                    if (in_array(@$define->fields->$label->type, ['internal', 'hidden'])) {
                        continue;
                    }

                    if (!$headersdone) {
                        $headertpl = new StdClass;
                        $headertpl->header = $label;
                        $headertpl->i = $i;
                        $template->headers[] = $headertpl;
                    }

                    $datumtpl = new StdClass;
                    $datumtpl->datum = $data;
                    $datumtpl->label = $label;
                    $datumtpl->i = $i;
                    if ($label == 'id') {
                        $datumtpl->title = "UID: {$entity->uid}";
                    } else {
                        $datumtpl->title = false;
                    }

                    $datatpl->data[] = $datumtpl;
                    $i++;
                }

                $cmds = [];

                // Add commands
                if ($mutable) {
                    $params = ['entity' => $template->entity, 'what' => 'copy', 'uid' => $entity->uid, 'view' => $view];
                    $copyurl = new moodle_url('/local/boardz_admin/view.php', $params);
                    $cmds[] = '<a href="'.$copyurl.'">'.$this->output->pix_icon('t/copy', get_string('copy'), 'core').'</a>';

                    $params = ['entity' => $template->entity, 'id' => $entity->id, 'uid' => $entity->uid];
                    $updateurl = new moodle_url('/local/boardz_admin/update.php', $params);
                    $cmds[] = '<a href="'.$updateurl.'">'.$this->output->pix_icon('t/edit', get_string('edit'), 'core').'</a>';
                }

                if ($deletable) {
                    $params = ['view' => $template->entity, 'id' => $entity->id, 'uid' => $entity->uid, 'what' => 'delete'];
                    $deleteurl = new moodle_url('/local/boardz_admin/view.php', $params);
                    $cmds[] = '<a href="'.$deleteurl.'">'.$this->output->pix_icon('t/delete', get_string('delete'), 'core').'</a>';
                }

                $entitystub = new StdClass;
                $entitystub->record = $entity;
                $entitystub->entity = preg_replace('/s$/', '', $view); // Need singularize.
                $serialized = base64_encode(json_encode($entitystub));
                $snapstr = get_string('snapobject', 'local_boardz_admin');
                $cmds[] = '<i class="fa fa-clipboard snappable" data-target="self" data-str="'.$serialized.'" title="'.$snapstr.'"></i>';

                if ($template->entity == 'widget' || $template->entity == 'panel') {
                    $params = [];
                    $viewurl = $config->baseurl;
                    $viewurl .= '/rest/server.php';
                    $viewurl .= '?token='.$config->viewtoken;
                    $viewurl .= '&wsfunction=view_'.$template->entity;
                    $viewurl .= '&wwwroot='.urlencode($CFG->wwwroot);
                    $viewurl .= '&'.substr($template->entity, 0, 1).'id='.$entity->id;
                    $viewurl .= '&output=styledhtml';
                    $cmds[] = '<a href="'.$viewurl.'" target="_blank">'.$this->output->pix_icon('i/hide', get_string('display', 'local_boardz_admin'), 'core').'</a>';
                }

                if ($template->entity == 'measurement' || $template->entity == 'indicator') {
                    $viewurl = $config->baseurl;
                    $viewurl .= '/rest/server.php';
                    $viewurl .= '?token='.$config->viewtoken;
                    $viewurl .= '&wsfunction=view_'.$template->entity;
                    $viewurl .= '&wwwroot='.urlencode($CFG->wwwroot);
                    $viewurl .= '&'.substr($template->entity, 0, 1).'id='.$entity->id;
                    $viewurl .= '&output=styledhtml';
                    $cmds[] = '<a href="'.$viewurl.'" target="_blank">'.$this->output->pix_icon('i/hide', get_string('test', 'local_boardz_admin'), 'core').'</a>';

                    $viewurl = $config->baseurl;
                    $viewurl .= '/rest/server.php';
                    $viewurl .= '?token='.$config->viewtoken;
                    $viewurl .= '&wsfunction=view_'.$template->entity;
                    $viewurl .= '&wwwroot='.urlencode($CFG->wwwroot);
                    $viewurl .= '&'.substr($template->entity, 0, 1).'id='.$entity->id;
                    $viewurl .= '&output=raw';
                    $cmds[] = '<a href="'.$viewurl.'" target="_blank" title="'.get_string('viewraw', 'local_boardz_admin').'">&lt;o&gt;</a>';
                }

                if ($template->entity == 'measurement') {
                    $viewurl = $config->baseurl;
                    $viewurl .= '/rest/server.php';
                    $viewurl .= '?token='.$config->viewtoken;
                    $viewurl .= '&wsfunction=view_'.$template->entity.'_acquire';
                    $viewurl .= '&wwwroot='.urlencode($CFG->wwwroot);
                    $viewurl .= '&'.substr($template->entity, 0, 1).'id='.$entity->id;
                    $viewurl .= '&output=raw';
                    $cmds[] = '<a href="'.$viewurl.'" target="_blank" title="'.get_string('viewacquire', 'local_boardz_admin').'">&lt;a&gt;</a>';
                }

                $datatpl->cmds = implode ('&nbsp;', $cmds);
                $datatpl->uid = $entity->uid;
                $datatpl->id = $entity->id;

                $template->entities[] = $datatpl;
                $headersdone = true;
            }
        }

        return $this->output->render_from_template('local_boardz_admin/entitylist', $template);
    }

    public function admin_list_filter_form() {
        global $SESSION;

        $template = new StdClass;

        $template->actionurl = new moodle_url('/local/boardz_admin/view.php');

        $template->view = required_param('view', PARAM_TEXT);
        $template->fname = optional_param('fname', '', PARAM_TEXT);
        $template->fclassname = optional_param('fclassname', '', PARAM_TEXT);
        $template->fattr = optional_param('fattr', '', PARAM_TEXT);
        $template->fattrvalue = optional_param('fattrvalue', '', PARAM_TEXT);
        $template->ftags = optional_param('ftags', @$SESSION->boardztags, PARAM_TEXT);
        // memorizes any change in session.
        $SESSION->boardztags = $template->ftags;

        return $this->output->render_from_template('local_boardz_admin/entitylistform', $template);
    }

    public function back_to_main_link() {
        global $OUTPUT;

        $template = new StdClass;

        return $OUTPUT->render_from_template('local_boardz_admin/backtomainlink', $template);
    }

}