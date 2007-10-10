<?php

	/* 
	Plugin Name: Fresh Text
	Plugin URI: http://goodies.freshout.us/
	Description: Replace text with images of the words. Usage: &lt;span class="freshout" title="SIZE,FONT,COLOR"&gt;Text to replace.&lt;/span&gt;, ie, &lt;span class="freshout" title="14,masexy,FF77EE"&gt;Sexy text.&lt;/span&gt;
	Version: .1
	Author: Freshout
	Author URI: http://freshout.us/
	
	Original text replacement code by Stewart Rosenberger
	http://www.stewartspeak.com/headings/    
	
	This script generates PNG images of text, written in
	the font/size that you specify. These PNG images are passed
	back to the browser. Optionally, they can be cached for later use. 
	If a cached image is found, a new image will not be generated,
	and the existing copy will be sent to the browser.
	*/

	function fresh_text_add_js()
	{
		echo '<script>';
		echo require_once 'replacement.php';
		echo '</script>';
	}

	register_activation_hook(dirname(__FILE__) . '/Main.php', 'Install');

	add_action('wp_print_scripts', 'fresh_text_add_js'); 
?>
