<?php

function array_get_safe($key, $array, $defaultValue = null) {
	return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
}
