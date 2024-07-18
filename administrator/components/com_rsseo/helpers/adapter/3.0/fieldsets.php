<?php
/**
* @package RSJoomla! Adapter
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

/**
 * Utility class for Fieldset elements.
 *
 * @package     RSJoomla!
 */
abstract class JHtmlRSFieldset {
	
	/**
	 * Creates the begining of the fieldset
	 *
	 * @param   string  $class   The class identifier.
	 * @legend  string  $legend  The legend.
	 *
	 * @return  string
	 */
	public static function start($class = 'adminform', $legend = null) {
		$html	= array();
		$html[] = '<fieldset class="' . $class . '">';
		
		if ($legend) {
			$html[] = "\t".'<legend>' . $legend . '</legend>';
		}
		
		return implode("\n",$html);
	}

	/**
	 * Close the current fieldset
	 *
	 * @return  string  HTML to close the pane
	 */
	public static function end() {
		$html	= array();
		$html[]	= '</fieldset>';

		return implode("\n",$html);
	}
	
	/**
	 * Begins the display of the field.
	 *
	 * @label   string  $label  The elements label.
	 * @label   string  $input  The elements input.
	 *
	 * @return  string  HTML to start a fieldset element
	 */
	public static function element($label, $input, $attribs=array(), $showlabel = true) {
		$class 	= '';
		$id 	= '';
		
		if (isset($attribs['class'])) {
			$class = ' '.self::escape($attribs['class']);
		}
		if (isset($attribs['id'])) {
			$id = ' id="'.self::escape($attribs['id']).'"';
		}
		
		$html   = array();
		$html[] = "\t".'<div class="control-group'.$class.'"'.$id.'>';
		
		if ($showlabel) {
			$html[] = "\t\t".'<div class="control-label">';
			$html[] = "\t\t\t".$label;
			$html[] = "\t\t".'</div>';
		}
		
		$html[] = "\t\t".'<div class="'.($showlabel ? 'controls' : 'rsepro-full-width').'">';
		$html[] = "\t\t\t".$input;
		$html[] = "\t\t".'</div>';
		
		$html[] = "\t\t".'</div>';
		
		return implode("\n",$html);
	}
	
	protected static function escape($text) {
		return htmlentities($text, ENT_COMPAT, 'utf-8');
	}
}