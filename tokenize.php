<?php
	/*
require_once 'library/App/Tokenizer.php';

$tokenizer = new App_Tokenizer();
$tokenizer->parse(file_get_contents('library/Zend/View/Helper/FormLabel.php'));

var_dump($tokenizer->getTokenData());*/

$a = array();
$b = array();

$c = array();
$a[] =& $c;

$c['foo'] = 'bar';
var_dump($a);
