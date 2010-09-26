<?php

/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */

ini_set('memory_limit', '128M');
set_time_limit(0);

function lbtrim($str)
{
	return trim($str, "\r\n");
}

function check_error($conn, $check_errorion, $message, $code)
{
	if (!$check_errorion)
	{
		fputs(STDERR, "\n$message\n" . imap_last_error() . "\n");
		if ($conn)
			imap_close($conn);
		exit($code);
	}
}

$argc = count($argv);

if ($argc > 1 && $argv[1] == 'help')
{
	echo "Usage: php ${argv[0]} [IMAP server [port [account name [password]]]]\n";
	exit(1);
}

$server = $port = $name = $password = '';

if ($argc < 2)
{
	echo 'Server: ';
	$server = lbtrim(fgets(STDIN));
}
else
	$server = lbtrim($argv[1]);

if ($argc < 3)
{
	echo 'Port: ';
	$port = (int)fgets(STDIN);
}
else
	$port = (int)$argv[2];

check_error(NULL, $port > 0 && $port < 65536, "Invalid port number: $port.", 2);

if ($argc < 4)
{
	echo 'Account name: ';
	$name = lbtrim(fgets(STDIN));
}
else
	$name = lbtrim($argv[3]);

if ($argc < 5)
{
	echo 'Password: ';
	system('stty -echo');
	$password = lbtrim(fgets(STDIN));
	system('stty echo');
	echo "\n";
}
else
	$password = lbtrim($argv[4]);

echo "Connecting to $server:$port as $name... ";

$ref = '{' . "$server:$port/imap/ssl}";

$conn = imap_open ($ref, $name, $password);
check_error($conn, $conn !== false, "Unable to connect to $server:$port as $name.", 3);

echo "OK\n";

echo "Fetching list of folders... ";

$folders = imap_list($conn, $ref, "*");
check_error($conn, is_array($folders), 'Error retrieving list of IMAP folders.', 4);

echo "OK\n";

imap_close($conn);

echo "Creating directory: $name... ";

if (!is_dir("./$name"))
{
	$mkdir = mkdir("./$name", 0700);
	check_error(NULL, $mkdir !== false, "Error creating directory: ./$name.", 5);
}

$bytes = 0;
$folder_count = count($folders);
$current_folder = 0;
foreach ($folders as $folder)
{
	++$current_folder;
	$folder_name = substr($folder, strlen($ref));

	if (!is_dir("./$name/$folder_name"))
	{
		echo "Creating directory: ./$name/$folder_name... ";
		$mkdir = mkdir("./$name/$folder_name", 0700, true);
		check_error(NULL, $mkdir !== false, "Error creating directory: ./$name/$folder_name.", 6);
		echo "OK\n";
	}

	echo "Fetching mail from $folder_name... \n";

	$conn = imap_open($folder, $name, $password);
	check_error($conn, $conn !== false, "Unable to connect to $server:$port as $name.", 7);

	$messages = imap_search($conn, 'ALL', SE_UID);
	check_error($conn, is_array($messages), "Error retrieving list of messages in folder: $folder_name.", 8);

	$all = count($messages);
	$current = 0;
	$status = '';
	$last_percent = -1;
	foreach ($messages as $uid)
	{
		++$current;
		$percent = ((int)round(($current / $all) * 10000)) / 100;
		if ($percent != $last_percent)
		{
			$last_percent = $percent;
			echo str_repeat("\x08", strlen($status));
			$status = "$percent% ($bytes bytes downloaded, folders: $current_folder/$folder_count)";
			echo "$status";
		}

		$msgfile = "./$name/$folder_name/$uid.txt";
		if (!file_exists($msgfile))
		{
			$msg = imap_fetchbody($conn, $uid, '', FT_UID | FT_PEEK);
			$written = file_put_contents($msgfile, $msg);
			check_error($conn, $written !== false, "Error writing file: $msgfile.", 9);
			$bytes += $written;
		}
	}
	echo "\nAll mail from $folder_name has been downloaded.\n";
	imap_close($conn);
}

?>

