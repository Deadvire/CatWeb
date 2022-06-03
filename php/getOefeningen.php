<?php
// Gets the data from the `site_oefeningen` table and returns it as JSON
if(
	(isset($_SERVER['HTTP_ACCEPT']) && !preg_match('/(application\/(json|\*))|\*\/\*/', $_SERVER['HTTP_ACCEPT'])) ||
	(isset($_SERVER['HTTP_ACCEPT_CHARSET']) && !preg_match('/utf-8/i', $_SERVER['HTTP_ACCEPT_CHARSET']))
) {
	header($_SERVER["SERVER_PROTOCOL"]." 406 Not Acceptable", true, 406);
	echo '{"error":"Can only provide \'application/json; charset=UTF-8\'"}';
	exit();
}
header("Content-Type: application/json");
if(!file_exists(__DIR__ .'/credentialFunctions.php')) {
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
	echo '{"error":"Missing file"}';
	exit();
}
require_once __DIR__ .'/credentialFunctions.php';
$perm = null;
if(session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['ID'])) {
	$perm = getPerms($_SESSION['ID'], $_SESSION['loginToken']);
} elseif(isset($_SERVER['PHP_AUTH_USER'])) {
	$perm = getPerms($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}
if(!is_int($perm)) {
	$result = DatbQuery(
		"SELECT
			o.*,
			GROUP_CONCAT(DISTINCT m.link ORDER BY m.ID ASC SEPARATOR '\n') AS images,
			GROUP_CONCAT(DISTINCT t.link ORDER BY t.ID ASC SEPARATOR '\n') AS videos
		FROM site_oefeningen o
		LEFT JOIN (
			site_link_media ml JOIN site_media m ON ml.mediaID = m.ID
		) ON ml.oefeningenID = o.ID
		LEFT JOIN (
			site_link_tube tl JOIN site_tube t ON tl.mediaID = t.ID
		) ON tl.oefeningenID = o.ID
		GROUP BY o.ID
		ORDER BY o.ID ASC;"
	);
} else {
	$result = DatbQuery(
		"SELECT
		o.*,
		GROUP_CONCAT(DISTINCT m.link ORDER BY m.ID ASC SEPARATOR '\n') AS images,
		GROUP_CONCAT(DISTINCT t.link ORDER BY t.ID ASC SEPARATOR '\n') AS videos,
		IF(f.ID_oefeningen IS NULL,0,1) AS favorite
		FROM site_oefeningen o
		LEFT JOIN (
			site_link_media ml JOIN site_media m ON ml.mediaID = m.ID
		) ON ml.oefeningenID = o.ID
		LEFT JOIN (
			site_link_tube tl JOIN site_tube t ON tl.mediaID = t.ID
		) ON tl.oefeningenID = o.ID
		LEFT JOIN (SELECT ID_oefeningen FROM site_favorites WHERE ID_users=?) f ON o.ID = f.ID_oefeningen
		LEFT JOIN (
			SELECT site_link_workout wl FROM site_workout w WHERE wl.workoutID = m.ID 
		) ON wl.oefeningID = o.ID
		GROUP BY o.ID
		ORDER BY o.ID ASC;",
		'i', $_SERVER['PHP_AUTH_USER']
	);
}
if(!($result instanceof mysqli_result)) {
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
	echo (is_string($result))?
		'{"error":"'. $result .'"}' :
		'{"error":"Expected mysqli_result but got int instead"}';
	exit();
}
/** @var array<int,array<string,string|int|null>> $output */
$output = $result->fetch_all(MYSQLI_ASSOC);
$result->close();
/** @var array<int,array<string,string|int|string[]|null|bool>> $output */
for($i=0; $i < count($output); $i++) {
	if($output[$i]['images'] != null)
		$output[$i]['images'] = explode("\n", $output[$i]['images']);
	if($output[$i]['videos'] != null)
		$output[$i]['videos'] = explode("\n", $output[$i]['videos']);
	if(isset($output[$i]['favorite']))
		$output[$i]['favorite'] = boolval($output[$i]['favorite']);
}
$output = json_encode($output);
if($output == false) {
	header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error", true, 500);
	echo '{"error":"Failed to encode JSON"}';
	exit();
}
echo $output;