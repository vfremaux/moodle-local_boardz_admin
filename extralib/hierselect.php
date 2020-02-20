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
 * select type form element
 *
 * Contains HTML class for a select type element
 *
 * @package   core_form
 * @copyright 2006 Jamie Pratt <me@jamiep.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('HTML/QuickForm/hierselect.php');

/**
 * select type form element
 *
 * HTML class for a hierarchical select type element
 *
 * @package   core_form
 * @category  form
 * @copyright 2019 Valery Fremaux <valery.fremaux@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_hierselect extends HTML_QuickForm_hierselect {

    /** @var string html for help button, if empty then no help */
    var $_helpbutton='';

    /** @var bool if true label will be hidden */
    var $_hiddenLabel=false;

    /**
     * constructor
     *
     * @param     string    $elementName    (optional)Input field name attribute
     * @param     string    $elementLabel   (optional)Input field label in form
     * @param     mixed     $attributes     (optional)Either a typical HTML attribute string
     *                                      or an associative array. Date format is passed along the attributes.
     * @param     mixed     $separator      (optional)Use a string for one separator,
     *                                      use an array to alternate the separators.
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $separator=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $separator);
    }

    /**
     * Sets label to be hidden
     *
     * @param bool $hiddenLabel sets if label should be hidden
     */
    function setHiddenLabel($hiddenLabel) {
        $this->_hiddenLabel = $hiddenLabel;
    }

    /**
     * Returns HTML for select form element.
     *
     * @return string
     */
    function toHtml(){
        $html = '';
        if ($this->_hiddenLabel) {
            $this->_generateId();
            $html .= '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.$this->getLabel().'</label>';
        }
        $html .= parent::toHtml();
        return $html;
    }

    /**
     * get html for help button
     *
     * @return string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }
}

MoodleQuickForm::registerElementType('hierselect', $CFG->dirroot.'/local/boardz_admin/extralib/hierselect.php', 'MoodleQuickForm_hierselect');

