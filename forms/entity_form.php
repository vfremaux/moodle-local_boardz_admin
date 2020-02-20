<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/boardz_admin/extralib/hierselect.php');

class entity_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $classoptions = array_combine($this->_customdata['classes'], $this->_customdata['classes']);
        $mform->addElement('select', 'classname', get_string('classname', 'local_boardz_admin'), $classoptions);


        if (!empty($this->_customdata['attributes'])) {

            // Start arranging elements by section.
            foreach ($this->_customdata['attributes'] as $attrname => $desc) {
                $bysection[@$desc->section][$attrname] = $desc;
            }


            foreach ($bysection as $section => $attributes) {

                if (!empty($section)) {
                    $mform->addElement('header', 'hdr'.$section, get_string($section, 'local_boardz_admin'));
                }

                foreach ($attributes as $attrname => $desc) {
                    if ($attrname == 'classname' || $attrname == 'id') {
                        continue;
                    }

                    if ($desc->type == 'internal') {
                        // Do not present any internally managed attribute.
                        $mform->addElement('hidden', $attrname, 0);
                        $mform->setType($attrname, PARAM_RAW);
                        continue;
                    }

                    if ($desc->type == 'static') {
                        $mform->addElement('static', $attrname, get_string($attrname, 'local_boardz_admin'));
                        continue;
                    }

                    $attrs = [];
                    if (!empty($desc->classes)) {
                        // clean classnames for HTML classes.
                        $classes = str_replace('\\', '-', implode(' ', $desc->classes));
                        $attrs['class'] = $classes;
                    }

                    switch ($desc->type) {

                        case 'checkbox': {
                            $mform->addElement('checkbox', $attrname, get_string($attrname, 'local_boardz_admin'), '', $attrs);
                            $mform->setType($attrname, PARAM_BOOL);
                            break;
                        }

                        case 'text':
                        case 'numeric': {
                            if (!empty($desc->maxlength)) {
                                $attrs['maxlength'] = $desc->maxlength;
                            }
                            if (!empty($desc->size)) {
                                $attrs['size'] = $desc->size;
                            }
                            $mform->addElement('text', $attrname, get_string($attrname, 'local_boardz_admin'), $attrs);
                            if ($desc->type == 'text') {
                                $mform->setType($attrname, PARAM_TEXT);
                            } else {
                                $mform->setType($attrname, PARAM_INT);
                            }
                            break;
                        }

                        case 'textarea': {
                            if (!empty($desc->cols)) {
                                $attrs['cols'] = $desc->cols;
                            }
                            if (!empty($desc->rows)) {
                                $attrs['rows'] = $desc->rows;
                            }
                            $mform->addElement('textarea', $attrname, get_string($attrname, 'local_boardz_admin'), $attrs);
                            break;
                        }

                        case 'editor': {
                            $options = ['maxfiles' => 0];
                            $mform->addElement('editor', $attrname, get_string($attrname, 'local_boardz_admin'), $options, $attrs);
                            break;
                        }

                        case 'hidden': {
                            $mform->addElement('hidden', $attrname);
                            $mform->setType($attrname, PARAM_TEXT);
                            break;
                        }

                        case 'selectyesno': {
                            $yesnooptions = array(0 => get_string('no'), 1 => get_string('yes'));
                            $mform->addElement('select', $attrname, get_string($attrname, 'local_boardz_admin'), $yesnooptions, $attrs);
                            $mform->setDefault($attrname, @$desc->default);
                            break;
                        }

                        case 'date': {
                            $mform->addElement('date_selector', $attrname, get_string($attrname, 'local_boardz_admin'), $attrs);
                            break;
                        }

                        case 'hierselect': {
                            if (!empty($desc->main)) {
                                // This is a shadow descriptor of another main hierselect.
                                break;
                            }
                            $hierselect = & $mform->addElement('hierselect', $attrname, get_string($attrname, 'local_boardz_admin'), $attrs);
                            $dimoptions = [];
                            if (!is_array($desc->dimensions)) {
                                $dimensions = explode(',', $desc->dimensions);
                            } else {
                                $dimensions = $desc->dimensions;
                            }

                            if (!empty($dimensions)) {
                                foreach ($dimensions as $dim) {
                                    $dimoptions[] = $this->get_options($dim, false, true);
                                }
                                $hierselect->setOptions($dimoptions);
                            }

                            break;
                        }

                        case 'select': {
                            $selectoptions = $this->get_options($desc->options, !empty($desc->passthru));
                            $select = & $mform->addElement('select', $attrname, get_string($attrname, 'local_boardz_admin'), $selectoptions, $attrs);
                            if (!empty($desc->multiple)) {
                                $select->setMultiple(true);
                            }
                            $mform->setDefault($attrname, @$desc->default);
                            break;
                        }

                        default:
                            $mform->addElement('static', $attrname, get_string('unsupportedfieldtype', 'local_boardz_admin', "$attrname->{$desc->type}"));
                    }

                    if (!empty($desc->if)) {
                        if (is_array($desc->if)) {
                            if (count($desc->if) == 3) {
                                list($target, $op, $value) = $desc->if;
                                $mform->disabledIf($attrname, $target, $op, $value);
                            } else {
                                list($target, $op) = $desc->if;
                                $mform->disabledIf($attrname, $target, $op);
                            }
                        }
                    }

                    if (!empty($desc->help)) {
                        if ($desc->help == 1) {
                            $mform->addHelpButton($attrname, $attrname, 'local_boardz_admin');
                        } else {
                            $mform->addHelpButton($attrname, $desc->help, 'local_boardz_admin');
                        }
                    }

                    if (!empty($desc->extended)) {
                        $mform->setAdvanced($attrname, true);
                    }
                }
            }
        }

        $this->add_action_buttons();

    }

    public function validate($data, $files = []) {

        foreach ($this->_customdata['attributes'] as $attrname => $desc) {
            if (!empty($desc->errorhandling)) {
                foreach ($desc->errorhandling as $rule => $message) {
                    $datum = $data['$attrname'];
                    $rulestr = "  \$test = '$datum' $rule ;";
                    eval($rulestr);
                    if (!$test) {
                        $errors[$attrname] = $message;
                    }
                }
            }
        }
    }

    /**
     * Get options for a select or a hierarchical select.
     * @param mixed $optionsource an array of direct options, or a string indicating a remote or local foreing source.
     * @param bool $passthru if true, do not try to translate direct options
     * @param vool $hier if true, get the options for a hierarchical select, i.e. come back maped on a upper level mapping.
     * the called entity will know what is the upper dimension to encode on.
     * @return A flat array of options in the simple select case. Inthe hierarchical case, may return a single flat array for
     * the top dimension, or a n-stage level array for lower hierarchical dimension.
     */
    protected function get_options($optionssource, $passthru = false, $hierarchic = false) {
        $selectoptions = [];
        if (is_array($optionssource)) {
            foreach ($optionssource as $opt) {
                if (empty($passthru)) {
                    $selectoptions[$opt] = get_string($opt, 'local_boardz_admin');
                } else {
                    $selectoptions[$opt] = $opt;
                }
            }
        } else {
            // Options is a string indicating the source of valid options.
            if (preg_match('/^remote::entity::(.*)$/', $optionssource, $matches)) {
                if ($hierarchic) {
                    $cmd = 'admin_get_list_hierarchic';
                } else {
                    $cmd = 'admin_get_list';
                }
                $remoteentity = $matches[1];
                $params = ['entity' => $remoteentity, 'chooseoption' => 1];
                $selectoptions = (array) \boardz\admin_api::call($cmd, $params);
            }
            if (preg_match('/^local::moodle::(.*)$/', $optionssource, $matches)) {
                $selectoptions = \boardz\local_api::get_local_select_options($matches[1], $hierarchic);
            }
            if (preg_match('/^local::calendar::(.*)$/', $optionssource, $matches)) {
                $selectoptions = \boardz\local_api::get_local_calendar_options($matches[1], $hierarchic);
            }
        }
        return $selectoptions;
    }
}