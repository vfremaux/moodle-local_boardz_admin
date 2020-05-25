<?php
/**
 * This is a new element type for HTML_QuickForm which defines a grid of QuickForm elements
 *
 * PHP Versions 4 and 5
 *
 * @category HTML
 * @package  HTML_QuickForm_OrderedSelect
 * @license  http://www.gnu.org/copyleft/lesser.html  LGPL
 * @author   Justin Patrin <papercrane@reversefold.com>
 * @version  $Id$
 */

require_once 'HTML/QuickForm/element.php';

/**
 * An HTML_QuickForm element providing an html select which selection set can
 * be ordered by drag and drop before submitting.
 */
class HTML_QuickForm_OrderedSelect extends HTML_QuickForm_Select {

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
     * Holds options
     *
     * @var array
     */
    var $_options = array('actAsGroup' => false);

    /**
     * Constructor
     *
     * @param string name for the element
     * @param string label for the element
     */
    function __construct($name = null, $label = null, $options = null) {
        parent::__construct($name, $label);
        $this->updateAttributes(array('class' => 'orderedSelect'));
        if (is_array($options)) {
            $this->_options = array_merge($this->_options, $options);
        }
    }

    /**
     * Returns an HTML formatted attribute string
     * @param    array   $attributes
     * @return   string
     * @access   private
     */
    function _getAttrString($attributes) {
        $strAttr = '';

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if ($key != 'name') {
                    // Do NOT add the name in the attrlist as moved to "hidden" ordered result.
                    $strAttr .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
                }
            }
        }
        return $strAttr;
    } // end func _getAttrString

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
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    function toHtml() {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            if ($this->getComment() != '') {
                $strHtml .= $tabs . '<!-- ' . $this->getComment() . " //-->\n";
            }

            if (!$this->getMultiple()) {
                $attrString = $this->_getAttrString($this->_attributes);
            } else {
                $myName = $this->getName();
                $this->setName($myName . '[]');
                $attrString = $this->_getAttrString($this->_attributes);
                $this->setName($myName);
            }

            $selectionpod = '<div id="'.$this->getAttribute('id').'-selection">';

            $valuesarr = explode(',', $this->_values);

            foreach ($valuesarr as $option) {
                $optid = $this->getAttribute('id').'-'.$option['attr']['value'];
                $deloptid = $this->getAttribute('id').'-'.$option['attr']['value'].'-delete';
                $selectionpod .= '<div class="orderedselect-option" id="'.$optid.'" data-value="'.$option['attr']['value'].'">';
                $selectionpod .= $option['text'];
                $selectionpod .= '<div class="orderedselect-delete" id="'.$deloptid.'"></div>';
                $selectionpod .= '</div>';
            }

            $selectionpod .= '</div>';
            $selectionpod .= '<input type="hidden" id="'.$this->getAttribute('id').'-value" name="'.$this->getAttribute('name').'" />';

            if (!in_array('class', $this->_attributes)) {
                $this->_attributes['class'] = 'orderedselect-select';
            } else {
                $this->_attributes['class'] .= ' orderedselect-select';
            }

            $strHtml .= $tabs.$selectionpod.'<select' . $attrString . ">\n";

            foreach ($this->_options as $option) {
                if (in_array((string)$option['attr']['value'], $valuesarr)) {
                    $this->_updateAttrArray($option['attr'], array('selected' => 'selected'));
                }
                $strHtml .= $tabs . "\t<option" . $this->_getAttrString($option['attr']) . '>' .
                            $option['text'] . "</option>\n";
            }

            return $strHtml . $tabs . '</select>';
        }
    }

    /**
     * Returns a 'safe' element's value
     *
     * @param  array   array of submitted values to search
     * @param  bool    whether to return the value as associative array
     * @access public
     * @return mixed
     */
    function exportValue(&$submitValues, $assoc = false) {
        if ($this->_options['actAsGroup']) {
            return parent::exportValue($submitValues, $assoc);
        }
    }

    /**
     * Sets the default values of the select box
     * 
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setSelected($values) {
        if (is_array($values)) {
            $this->_values = implode(',', $values);
        } else {
            $this->_values = $values;
        }
    }
}

require_once 'HTML/QuickForm.php';
