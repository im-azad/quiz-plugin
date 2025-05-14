<?php
// This file is generated. Do not modify it manually.
return array(
	'quiz' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'quiz-plugin/quiz',
		'version' => '1.0.0',
		'title' => 'Quiz Block',
		'category' => 'widgets',
		'icon' => 'forms',
		'description' => 'A quiz block for creating interactive quizzes',
		'example' => array(
			
		),
		'keywords' => array(
			'quiz',
			'assessment',
			'test'
		),
		'supports' => array(
			'html' => false,
			'interactivity' => true
		),
		'attributes' => array(
			'id' => array(
				'type' => 'number',
				'default' => 0
			)
		),
		'textdomain' => 'quiz-plugin',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScriptModule' => 'file:./view.js'
	)
);
