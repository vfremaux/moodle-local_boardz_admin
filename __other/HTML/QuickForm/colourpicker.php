<?php
/**
 * This is a new element type for HTML_QuickForm which defines a colour picker element
 *
 * PHP Versions > 4
 *
 * @category HTML
 * @package  HTML_QuickForm_ColourPicker
 * @license  http://www.gnu.org/copyleft/lesser.html  LGPL
 * @author   Valery Fremaux <valery.fremaux@gmail.com>
 * @version  $Id$
 */

require_once('HTML/QuickForm/element.php');
require_once('HTML/QuickForm/input.php');

/**
 * An HTML_QuickForm element which holds a colour picker.
 */
class HTML_QuickForm_ColourPicker extends HTML_QuickForm_input {

    /**
     * Holds this element's name
     *
     * @var string
     */
    var $_name;

    /**
     * Holds a reference to the form for use when adding elements
     *
     * @var HTML_QuickForm
     */
    var $_form;

    /**
     *
     */
    var $_options;

    /**
     * Constructor
     *
     * @param string name for the element
     * @param string label for the element
     */
    function __construct($name = null, $label = null, $options = null, $attributes = null) {
        $this->_name = $name;
        $this->_type = 'colourpicker';
        parent::__construct($name, $label);
        if (!empty($attributes['class'])) {
            $attributes['class'] .= ' colourPicker';
        }
        $this->updateAttributes($attributes);
        if (is_array($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
    }

    /**
     * Sets this element's name
     *
     * @param string name
     */
    function setName($name) {
        $this->_name = $name;
    }

    /**
     * Gets this element's name
     *
     * @return string name
     */
    function getName() {
        return $this->_name;
    }

    /**
     * Freezes all elements in the grid
     */
    function freeze() {
        parent::freeze();
    }

    /**
     * Returns Html for the element
     * this method is fully overriden by the Moodle element grid Wrapper
     * as we cannot use pear HTML_Table class in Moodle.
     *
     * @access      public
     * @return      string
     */
    function toHtml() {
        assert(1);
        // Let moodle element to the job.
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string  Name of event
     * @param     mixed   event arguments
     * @param     object  calling object
     * @access    public
     * @return    bool    true
     */
    function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'updateValue': {
                //store form for use in addRow
                $this->_form =& $caller;
                break;
            }

            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }
        return true;
    }
}

require_once 'HTML/QuickForm.php';
