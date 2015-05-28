<?php

function array_remove($array, $value_to_remove) {
	return array_diff($array, array($value_to_remove));
}

function he($text) {
	return htmlentities($text, ENT_COMPAT, 'UTF-8');
}

function phe($text) {
	echo he($text);
}

function js($text) {
    return str_replace("'", "\\'", $text);
}

function pjs($text) {
    echo js($text);
}

function string_contains($haystack, $needle) {
    return strpos($haystack, $needle) !== FALSE;
}

function array_contains($haystack, $needle) {
    if (empty($haystack)) return FALSE;
    return array_search($needle, $haystack) !== FALSE;
}
