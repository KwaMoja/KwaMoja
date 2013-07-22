<?php
$PageSecurity=1;
include('includes/session.inc');

$sql = "SELECT caption
			FROM favourites
			WHERE userid='" . $_SESSION['UserID'] . "'
				AND href='" . $_GET['Script'] . "'";
$result = DB_query($sql, $db);

if (DB_num_rows($result) == 0) {
	$sql = "INSERT INTO favourites ( userid,
									caption,
									href
								) VALUES (
									'" . $_SESSION['UserID'] . "',
									'" . $_GET['Title'] . "',
									'" . $_GET['Script'] . "'
								)";
	$result = DB_query($sql, $db);
} else {
	$sql = "DELETE FROM favourites
					WHERE userid='" . $_SESSION['UserID'] . "'
						AND href='" . $_GET['Script'] . "'";
	$result = DB_query($sql, $db);
}

?>