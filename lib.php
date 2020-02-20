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

defined('MOODLE_INTERNAL') || die();

/**
 * This file contains necessary functions to output
 * cms content on site or course level.
 *
 * @package    local_boardz_admin
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This is part of the dual release distribution system.
 * Tells wether a feature is supported or not. Gives back the
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 */
function local_boardz_admin_supports_feature($feature = null) {
    global $CFG;
    static $supports;

    $config = get_config('local_boardz_admin');

    if (!isset($supports)) {
        $supports = array(
            'pro' => array(
            ),
            'community' => array(
            ),
        );
    }

    // Check existance of the 'pro' dir in plugin.
    if (is_dir(__DIR__.'/pro')) {
        if ($feature == 'emulate/community') {
            return 'pro';
        }
        if (empty($config->emulatecommunity)) {
            $versionkey = 'pro';
        } else {
            $versionkey = 'community';
        }
    } else {
        $versionkey = 'community';
    }

    if (empty($feature)) {
        // Just return version.
        return $versionkey;
    }

    list($feat, $subfeat) = explode('/', $feature);

    if (!array_key_exists($feat, $supports[$versionkey])) {
        return false;
    }

    if (!in_array($subfeat, $supports[$versionkey][$feat])) {
        return false;
    }

    return $versionkey;
}

/**
 * Computes some initial values of the form data record based on attributes or fields defines.
 */
function boardz_process_defined_form_values(&$formrecord, $attributes) {
    global $CFG;

    foreach ($attributes as $attrname => $desc) {

        /*
         * Some complex form elements need attribute name remapping.
         * Moodle side : receive individual elements for a group and group them.
         * We get origin record fields remapped to hierselect components
         */
        if (!empty($desc->input)) {
            $formvalue = [];
            $inputkeys = explode(',', $desc->input);
            foreach ($inputkeys as $k) {
                if (isset($formrecord->$k)) {
                    $formvalue[] = $formrecord->$k;
                }
            }
            $formrecord->$attrname = $formvalue;
        }

        if (!empty($formrecord->$attrname)) {
            if (is_object($formrecord->$attrname)) {
                $formrecord->$attrname = json_encode($formrecord->$attrname);
            }
            continue;
        }

        if (!empty($desc->formvalue)) {
            if (strpos($desc->formvalue, 'config:') === 0) {
                // Extract a local config value.
                $configkey = str_replace('config:', '', $desc->formvalue);
                if (isset($CFG->$configkey)) {
                    $formrecord->$attrname = $CFG->$configkey;
                } else {
                    throw new Exception("Invalid config key $configkey for form value.");
                }
            } else if (preg_match('/config_([^:+])\\:(.*)$/', $desc->formvalue, $matches)) {
                // Extract a local plugin config value.
                $component = $matches[1];
                $configkey = $matches[2];
                $formrecord->$attrname = get_config($component, $key);
            } else if ($desc->formvalue == 'autogen') {
                // Generates unique id.
                $formrecord->$attrname = boardz_generate_uid();
            } else {
                $formrecord->$attrname = $desc->formvalue; // As last case, use formvalue as default
            }
        }

        if (!isset($formrecord->$attrname) && !empty($desc->default)) {
            $formrecord->$attrname = $desc->default;
        }
    }
}

function boardz_remap_data_before_call($data, $attributes) {

    foreach ($attributes as $attrname => $desc) {

        if (!empty($desc->input) && !empty($data->$attrname)) {
            $inputkeys = explode(',', $desc->input);
            // We need map hierselects to scalar params.
            $hierinput = $data->$attrname;
            unset($data->$attrname);
            foreach ($inputkeys as $k) {
                $d = array_shift($hierinput);
                $data->$k = $d;
            }
        }

        /*
         * Again, multiple selects should send a scalarized set of url params.
         * data->attrname is an array of values.
         */
        if (!empty($desc->multiple) && !empty($data->$attrname)) {
            if (is_array($data->$attrname)) {
                $data->$attrname = implode(',', $data->$attrname);
            }
        }
    }

    // Remove disabled attributes from class definition.
    foreach ($data as $fieldname => $unused) {
        if ($fieldname != 'classname' && $fieldname != 'entity') {
            if (!array_key_exists($fieldname, $attributes)) {
                unset($data->$fieldname);
                continue;
            }
            if (!in_array($data->classname, $attributes[$fieldname]->classes)) {
                unset($data->$fieldname);
            }
        }
    }
    return $data;
}

function boardz_generate_uid() {
    global $CFG;

    $seed = $CFG->wwwroot;
    $seed .= time();
    return md5($seed);
}

function local_boardz_admin_add_filter(&$params) {
    global $SESSION;

    $params['fname'] = optional_param('fname', '', PARAM_TEXT);
    $params['fclassname'] = optional_param('fclassname', '', PARAM_TEXT);
    $params['ftags'] = optional_param('ftags', @$SESSION->boardztags, PARAM_TEXT);
    $SESSION->boardztags = $params['ftags'];
    $attr = optional_param('fattr', '', PARAM_TEXT);
    $attrvalue = optional_param('fattrvalue', '', PARAM_TEXT);

    if (!empty($attr)) {
        $params['fattr'] = '"'.$attr.'":"'.$attrvalue.'"';
    }
}