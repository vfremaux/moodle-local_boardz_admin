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
 * Button form element
 *
 * Contains HTML class for a button type element
 *
 * @package   core_form
 * @copyright 2007 Jamie Pratt <me@jamiep.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!class_exists('MoodleQuickForm_colourpicker')) {
    if (file_exists($CFG->libdir.'/pear/HTML/QuickForm/colourpicker.php')) {
        require_once ("HTML/QuickForm/colourpicker.php");
    } else {
        require_once ($CFG->dirroot."/local/boardz_admin/__other/HTML/QuickForm/colourpicker.php");
    }

/**
 * HTML class for a colourpicker type element
 *
 * Overloaded {@link HTML_QuickForm_button} to add help button
 *
 * @package   core_form
 * @category  form
 * @copyright 2007 Jamie Pratt <me@jamiep.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_colourpicker extends HTML_QuickForm_ColourPicker {

        /** @var string html for help button, if empty then no help */
        public $_helpbutton='';

        /**
         * get html for help button
         *
         * @return string html for help button
         */
        function getHelpButton() {
            return $this->_helpbutton;
        }

        /**
         * Slightly different container template when frozen.
         *
         * @return string
         */
        function getElementTemplateType() {
            if ($this->_flagFrozen){
                return 'nodisplay';
            } else {
                return 'default';
            }
        }

        /**
         * Returns Html for the element
         *
         * @access      public
         * @return      string
         */
        function toHtml(){
            global $PAGE, $OUTPUT;

            $str .= '<div class="form-colourpicker defaultsnext">';
            $str .= '    <div class="admin_colourpicker clearfix">';
            $str .= $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', ['class' => 'loadingicon']);
            $str .= '    </div>';
            $str .= '    <input type="text" '.$this->_getAttrString($this->_attributes).' size="12" class="text-ltr">';
            if ($this->_haspreview) {
                $previewstr = get_string('preview');
                $str .= '<input type="button" id="'.$this->getAttribute('id').'_preview" value="'.$previewstr.'" class="admin_colourpicker_preview">';
            }
            $str .= '</div>';

            $PAGE->requires->js_init_call('M.util.init_colour_picker', array($this->getAttribute('id'), $this->_previewconfig));

            return $str;
        }
    }

    if (file_exists($CFG->libdir.'/form/colourpicker.php')) {
        MoodleQuickForm::registerElementType('colourpicker', "$CFG->libdir/form/colourpicker.php", 'MoodleQuickForm_colourpicker');
    } else {
        MoodleQuickForm::registerElementType('colourpicker', $CFG->dirroot.'/local/boardz_admin/__other/colourpicker.php', 'MoodleQuickForm_colourpicker');
    }
}