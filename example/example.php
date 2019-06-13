<?php

require __DIR__.'/../lib/JsonExplore.php';

$data = [
	'a' => 1,
	'b' => '2',
	'c' => [
		'x' => false,
		'y' => null,
	],
	'd' => [
		[
			'i' => 'hello',
		],
		[
			'i' => 1,
			'j' => 'world',
		],
		[
			'k' => [],
		],
	],
];

$json = json_encode($data, JSON_PRETTY_PRINT);

echo "For the JSON data:\n\n$json\n\nThe following analysis was produced:\n\n";

$explore = \M1ke\JsonExplore\JsonExplore::fromJson($json);
$explore->analyse();

echo $explore->asPathString()."\n\n";