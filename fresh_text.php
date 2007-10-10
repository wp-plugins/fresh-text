<?php

	### GET TEXT PARAMETERS ###
	
	$font_file  = 'saxmono.ttf';	// default font
	$font_size  = 14;		// default size
	$font_color = '#000000';	// default font color
	$background_color = '#ffffff';	// default background color
	$transparent_background  = true;// use transparent background
	$cache_images = true;		// cache images
	$cache_folder = 'cache';	// cache folder name
	
	
	if( isset( $_REQUEST['size'] ) && $_REQUEST['size'] && is_numeric( $_REQUEST['size'] ) )
	{
		$font_size = $_REQUEST['size'];
	}
	
	
	if( isset( $_REQUEST['font'] ) && $_REQUEST['font'] && !is_numeric($_REQUEST['font']) && $_REQUEST['font']!='' )
	{
		$font_file = $_REQUEST['font'] . '.ttf';
	}
	
	
	if( isset( $_REQUEST['color'] ) && $_REQUEST['color'] && !is_numeric($_REQUEST['color']) && $_REQUEST['color']!='' )
	{
		$font_color = $_REQUEST['color'];
	}
	
	
	$font_file = 'fonts/' . $font_file;

	
	
	
	### SEND IMAGE ###
	
	$mime_type = 'image/png' ;
	$extension = '.png' ;
	$send_buffer_size = 4096 ;
	
	// check for GD support
	if(!function_exists('ImageCreate'))
	    fatal_error('Error: Server does not support PHP image generation') ;
	
	// clean up text
	if(empty($_GET['text']))
	    fatal_error('Error: No text specified.') ;
	    
	$text = $_GET['text'] ;
	if(get_magic_quotes_gpc())
	    $text = stripslashes($text) ;
	$text = javascript_to_html($text) ;
	
	// look for cached copy, send if it exists
	$hash = md5(basename($font_file) . $font_size . $font_color .
	            $background_color . $transparent_background . $text) ;
	$cache_filename = $cache_folder . '/' . $hash . $extension ;
	if($cache_images && ($file = @fopen($cache_filename,'rb')))
	{
	    header('Content-type: ' . $mime_type) ;
	    while(!feof($file))
	        print(($buffer = fread($file,$send_buffer_size))) ;
	    fclose($file) ;
	    exit ;
	}
	
	// check font availability
	$font_found = is_readable($font_file) ;
	if(!$font_found)
	{
	    fatal_error('Error: The server is missing the specified font.') ;
	}
	
	// create image
	$background_rgb = hex_to_rgb($background_color) ;
	$font_rgb = hex_to_rgb($font_color) ;
	$dip = get_dip($font_file,$font_size) ;
	$box = @ImageTTFBBox($font_size,0,$font_file,$text) ;
	$image = @ImageCreate(abs($box[2]-$box[0])+20,abs($box[5]-$dip)+20) ;
	if(!$image || !$box)
	{
	    fatal_error('Error: The server could not create this heading image.') ;
	}
	
	// allocate colors and draw text
	$background_color = @ImageColorAllocate($image,$background_rgb['red'],
	    $background_rgb['green'],$background_rgb['blue']) ;
	$font_color = ImageColorAllocate($image,$font_rgb['red'],
	    $font_rgb['green'],$font_rgb['blue']) ;   
	ImageTTFText($image,$font_size,0,-$box[0]+10,abs($box[5]-$box[3])-$box[1]+10,
	    $font_color,$font_file,$text) ;
	
	// set transparency
	if($transparent_background)
	    ImageColorTransparent($image,$background_color) ;
	
	header('Content-type: ' . $mime_type) ;
	ImagePNG($image) ;
	
	// save copy of image for cache
	if($cache_images)
	{
	    @ImagePNG($image,$cache_filename) ;
	}
	
	ImageDestroy($image) ;
	exit ;
	
	
	/*
		try to determine the "dip" (pixels dropped below baseline) of this
		font for this size.
	*/
	function get_dip($font,$size)
	{
		$test_chars = 'abcdefghijklmnopqrstuvwxyz' .
				      'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
					  '1234567890' .
					  '!@#$%^&*()\'"\\/;.,`~<>[]{}-+_-=' ;
		$box = @ImageTTFBBox($size,0,$font,$test_chars) ;
		return $box[3] ;
	}
	
	
	/*
	    attempt to create an image containing the error message given. 
	    if this works, the image is sent to the browser. if not, an error
	    is logged, and passed back to the browser as a 500 code instead.
	*/
	function fatal_error($message)
	{
	    // send an image
	    if(function_exists('ImageCreate'))
	    {
	        $width = ImageFontWidth(5) * strlen($message) + 10 ;
	        $height = ImageFontHeight(5) + 10 ;
	        if($image = ImageCreate($width,$height))
	        {
	            $background = ImageColorAllocate($image,255,255,255) ;
	            $text_color = ImageColorAllocate($image,0,0,0) ;
	            ImageString($image,5,5,5,$message,$text_color) ;    
	            header('Content-type: image/png') ;
	            ImagePNG($image) ;
	            ImageDestroy($image) ;
	            exit ;
	        }
	    }
	
	    // send 500 code
	    header("HTTP/1.0 500 Internal Server Error") ;
	    print($message) ;
	    exit ;
	}
	
	
	/* 
	    decode an HTML hex-code into an array of R,G, and B values.
	    accepts these formats: (case insensitive) #ffffff, ffffff, #fff, fff 
	*/    
	function hex_to_rgb($hex)
	{
	    // remove '#'
	    if(substr($hex,0,1) == '#')
	        $hex = substr($hex,1) ;
	
	    // expand short form ('fff') color
	    if(strlen($hex) == 3)
	    {
	        $hex = substr($hex,0,1) . substr($hex,0,1) .
	               substr($hex,1,1) . substr($hex,1,1) .
	               substr($hex,2,1) . substr($hex,2,1) ;
	    }
	
	    if(strlen($hex) != 6)
	        fatal_error('Error: Invalid color "'.$hex.'"') ;
	
	    // convert
	    $rgb['red'] = hexdec(substr($hex,0,2)) ;
	    $rgb['green'] = hexdec(substr($hex,2,2)) ;
	    $rgb['blue'] = hexdec(substr($hex,4,2)) ;
	
	    return $rgb ;
	}
	
	
	/*
	    convert embedded, javascript unicode characters into embedded HTML
	    entities. (e.g. '%u2018' => '&#8216;'). returns the converted string.
	*/
	function javascript_to_html($text)
	{
	    $matches = null ;
	    preg_match_all('/%u([0-9A-F]{4})/i',$text,$matches) ;
	    if(!empty($matches)) for($i=0;$i<sizeof($matches[0]);$i++)
	        $text = str_replace($matches[0][$i],
	                            '&#'.hexdec($matches[1][$i]).';',$text) ;
	
	    return $text ;
	}
	
?>
