<?php
$this->_render( 'floating-message', array(
	'class'   => '{{- css_class }}',
	'message' => '{{= message }}',
) );