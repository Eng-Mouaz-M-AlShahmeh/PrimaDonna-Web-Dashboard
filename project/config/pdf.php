<?php
return [
	'mode'                 => '',
	'format'               => 'A4',
	'default_font_size'    => '12',
	'default_font'         => 'Arial',
	'margin_left'          => 10,
	'margin_right'         => 10,
	'margin_top'           => 10,
	'margin_bottom'        => 10,
	'margin_header'        => 0,
	'margin_footer'        => 0,
	'orientation'          => 'P',
	'title'                => 'Prima Donna',
	'author'               => '',
	'watermark'            => '',
	'show_watermark'       => false,
	'watermark_font'       => 'sans-serif',
	'display_mode'         => 'fullpage',
	'watermark_text_alpha' => 0.1,
	'custom_font_dir' => base_path('resources/fonts/'), // don't forget the trailing slash!
	'custom_font_data' => [
		'Almarai' => [
			'R'  => 'Almarai-Regular.ttf',    // regular font
			'B'  => 'Almarai-Bold.ttf',       // optional: bold font
			'I'  => 'Almarai-Light.ttf',     // optional: italic font
			'BI' => 'Almarai-ExtraBold.ttf' // optional: bold-italic font
		]
		// ...add as many as you want.
	],
	'auto_language_detection'  => true,
	'temp_dir'               => rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR),
	'pdfa' 			=> false,
    'pdfaauto' 		=> false,
];

?>