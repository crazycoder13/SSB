<?php

session_start();

include_once("config.php");
include("functions.php");
include("bbcode.php");

if (!isset($_SESSION['ssb-user']) && !isset($_SESSION['ssb-pass'])) { echo "ERROR: Not logged in!"; header("Location: index.php"); exit(1); }

if (isset($_GET['msg']) && $_GET['msg']!="" && isset($_GET['nick']) && !isset($_GET['friend'])){

	$nick = $_GET['nick'];
	$msg  = bbcode_format(nl2br(htmlentities(stripcslashes($_GET['msg']))));
	$line = "<table><tr><td style='vertical-align: top;'><img class='avatar_chat' src='?do=avatarlocation&user=" . $nick . "' title='User Avatar'></td><td class='message'><b>$nick</b>: $msg</td></tr></table>\n";
	$old_content = file_get_contents($chat_db);

	$lines = count(file($chat_db));

	if($lines>$server_msgcount) {
		$old_content = implode("\n", array_slice(explode("\n", $old_content), 1));
	}

	file_put_contents($chat_db, $old_content.$line);
	echo $line;

} else if (isset($_GET['msg']) && $_GET['msg']!="" && isset($_GET['nick']) && isset($_GET['friend'])){

	$friendNick = $_GET['friend'];
	$nick = $_SESSION['ssb-user'];

	$friendcount = file_get_contents("ssb_db/friends/" . $nick . ".count");
   	include "ssb_db/friends/" . $nick . ".php";
	// Checking if you're friend
    for($x = 1; $x <= $friendcount; $x++)
    {
		if($friendNick == ${"friend" . $x}) {
			
			$msgCount = file_get_contents("ssb_db/friends/" . ${"friend_chat_db" . $x} . ".count");
			$msgCount = $msgCount + 1;
			$msg  = bbcode_format(nl2br(htmlentities(stripcslashes($_GET['msg']))));
			$line_start = "<?php \$msg" . $msgCount . " = \"<table><tr><td style='vertical-align: top;'><img class='avatar_chat' src='?do=avatarlocation&user=" . $nick . "' title='User Avatar'></td><td class='message'><b>$nick</b>: $msg</td></tr></table>";
			$line_end = "\"; ?>\n";
		
			$old_content = file_get_contents("ssb_db/friends/" . ${"friend_chat_db" . $x} . ".php");
			$notifications = file_get_contents("ssb_db/friends/" . ${"friend" . $x} . ".notifications");
			// update conversation message count
			file_put_contents("ssb_db/friends/" . ${"friend_chat_db" . $x} . ".count", $msgCount);
			// conents into database
			file_put_contents("ssb_db/friends/" . ${"friend_chat_db" . $x} . ".php", $old_content . $line_start . $line_end);
			// notifications!
			file_put_contents("ssb_db/friends/" . ${"friend" . $x} . ".notifications", "<b>" . $nick . "</b> sent you a <a href='?do=privmsg&friend=" . $nick . "'>message</a>\n" . $notifications);
		}
	}
} else if (isset($_GET['get'])){

	$friendNick = $_GET['get'];
	$nick = $_SESSION['ssb-user'];

	$friendcount = file_get_contents("ssb_db/friends/" . $nick . ".count");
    include "ssb_db/friends/" . $nick . ".php";
    for($x = 1; $x <= $friendcount; $x++)
    {
		if($friendNick == ${"friend" . $x}) {
			$msgCount = file_get_contents("ssb_db/friends/" . ${"friend_chat_db" . $x} . ".count");
			include "ssb_db/friends/" . ${"friend_chat_db" . $x} . ".php";
			for($y = 1; $y <= $msgCount; $y++) {
				echo ${"msg" . $y};
			}
		} //else { echo "Not friend!"; }
		//echo "Finding friend in slot " . $x;
	} 
} else if (isset($_GET['all'])) {
	//$content = file_get_contents($server_db);
	// This is faster
	$flag = file($chat_db);
	$content = "";
	foreach ($flag as $value) {
		$content .= $value;
	}
	echo $content;

}/* else if(isset($_GET['ping'])) {
	$username = $_GET['nick'];

} else if(isset($_GET['pong'])) {

}*/
?>
