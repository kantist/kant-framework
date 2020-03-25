<?php
function is_valid_array(array $params, array $required, $empty_checker = false) {
	$valid = true;

	foreach ($required as $value) {
		if (!isset($params[$value])) {
			$valid = false;
		} elseif (!$params[$value] && $empty_checker) {
			$valid = false;
		}
	}

	return $valid;
}