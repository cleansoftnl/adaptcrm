<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The LGPL License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license LGPL License (http://www.opensource.org/licenses/lgpl-2.1.php)
 */

/**
 * TinyMCE Helper
 *
 * @package tiny_m_c_e
 * @subpackage tiny_m_c_e.views.helpers
 */

class TinyMceHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Configuration
 *
 * @var array
 */
	public $configs = array();

/**
 * Default values
 *
 * @var array
 */
	protected $_defaults = array();

/**
 * Adds a new editor to the script block in the head
 *
 * @see http://wiki.moxiecode.com/index.php/TinyMCE:Configuration for a list of keys
 * @param mixed If array camel cased TinyMce Init config keys, if string it checks if a config with that name exists
 * @return void
 */
	public function editor($options = array()) {
		if (is_string($options)) {
			if (isset($this->configs[$options])) {
				$options = $this->configs[$options];
			} else {
				throw new OutOfBoundsException(sprintf(__('Invalid TinyMCE configuration preset %s', true), $options));
			}
		}
		$options = array_merge($this->_defaults, $options);
		$lines = '';
		
		foreach ($options as $option => $value) {
			$lines .= Inflector::underscore($option) . ' : "' . $value . '",' . "\n";
		}
		// remove last comma from lines to avoid the editor breaking in Internet Explorer
		$lines = rtrim($lines);
		$lines = rtrim($lines, ',');
		echo "<script type='text/javascript'>
		tinyMCE.init({
			// General options
			mode : 'textareas',
			theme : 'advanced',
			elements : 'abshosturls',
			plugins : 'spellchecker,preview,searchreplace,emotions,media,contextmenu,wordcount,pagebreak,tinyautosave',

			theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,fontsizeselect,formatselect',
			theme_advanced_buttons2 : 'bullist,numlist,|,undo,redo,|,link,unlink,image,cleanup,code,preview,replace,spellchecker,emotions,media,pagebreak,tinyautosave',
			theme_advanced_buttons3 : '',
			theme_advanced_toolbar_location : 'top',
			theme_advanced_toolbar_align : 'left',
			theme_advanced_statusbar_location : 'bottom',
			theme_advanced_resizing : true,

			remove_linebreaks : false,
	        force_p_newlines : false,
			debug : false,
			relative_urls : false,
			remove_script_host : false
		});
	</script>";
	$this->Html->script('/js/tiny_mce/tiny_mce.js', false);
		// $this->Html->scriptBlock('tinyMCE.init({' . "\n" . $lines . "\n" . '});' . "\n", array(
			// 'inline' => false));
	}

/**
 * beforeRender callback
 *
 * @return void
 */
	public function beforeRender() {
		$appOptions = Configure::read('TinyMCE.editorOptions');
		if ($appOptions !== false && is_array($appOptions)) {
			$this->_defaults = $appOptions;
		}	
	}
}