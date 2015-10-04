<?php
namespace Rocket\Api;

function is_date($date, $format = 'Y-m-d H:i:s')
{
	$d = \DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}