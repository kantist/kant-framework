<?php
function token($length = 32) {
	// Create random token
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	$max = strlen($string) - 1;

	$token = '';

	for ($i = 0; $i < $length; $i++) {
		$token .= $string[mt_rand(0, $max)];
	}	

	return $token;
}

function create_password($length = 6) {
	$string = '123456789';

	$max = strlen($string) - 1;

	$password = '';

	for ($i = 0; $i < $length; $i++) {
		$password .= $string[mt_rand(0, $max)];
	}

	return $password;
}

function format_isodate($date) {
	return date(DATE_ISO8601, strtotime($date));
}

function format_decimal($val, int $precision = 2): string {
	$input = str_replace(' ', '', $val);
	$number = str_replace(',', '.', $input);
	if (strpos($number, '.')) {
		$groups = explode('.', str_replace(',', '.', $number));
		$lastGroup = array_pop($groups);
		$number = implode('', $groups) . '.' . $lastGroup;
	}
	return bcadd($number, 0, $precision);
}

function html_decode($html, $iframe = false) {
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	// We decode twice because sometimes not clearing tags.
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = preg_replace('~[\r\n]+~', '', $html);

	if ($iframe) {
		preg_match('/ta-insert-video="[\s\S]*?"/', $html, $matches);
		if ($matches[0]) {
			// Remove class and last "
			$video_link = substr(str_replace('ta-insert-video="', '', $matches[0]), 0, -1);

			// Change image to iframe from string
			$html = preg_replace('#<img[^>]+class="[^"]*ta-insert-video[^"]*"[^>]*>#', '<iframe src="' . $video_link . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>', $html);
		}
	}

	return $html;
}