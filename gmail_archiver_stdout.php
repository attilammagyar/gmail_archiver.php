<?php

/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details. */

/**
 * Usage: create an app password for your GMail account, then run it like this:
 *
 *     php gmail_archiver_stdout.php [IMAP server [port [query [account [password]]]]] | gzip -9 -c >mails.txt.gz
 *     php gmail_archiver_stdout.php imap.gmail.com 993 'ALL SINCE 15-Jan-2021' account@gmail.com app-password | gzip -9 -c >mails.txt.gz
 *
 * WARNING: do not run it on untrusted or shared computers with the app password
 *          in the command line, because it will be visible in the process list!
 */

ini_set('memory_limit', '512M');
set_time_limit(0);

function lbtrim($str)
{
    return trim($str, "\r\n");
}

function check_error($conn, $condition, $message, $code)
{
    if (!$condition)
    {
        fputs(STDERR, "\n$message\n" . imap_last_error() . "\n");
        if ($conn)
            imap_close($conn);
        exit($code);
    }
}

function status($text, $add_newline=true)
{
    fputs(STDERR, $text);

    if ($add_newline) {
        fputs(STDERR, "\n");
    }
}

$argc = count($argv);

if ($argc > 1 && $argv[1] == 'help') {
    status("Usage: php ${argv[0]} [IMAP server [port [account name [password]]]]");
    exit(1);
}

$server = $port = $query = $name = $password = '';

if ($argc < 2) {
    status('Server: ', false);
    $server = lbtrim(fgets(STDIN));
} else {
    $server = lbtrim($argv[1]);
}

if ($argc < 3) {
    status('Port: ', false);
    $port = (int)fgets(STDIN);
} else {
    $port = (int)$argv[2];
}

check_error(NULL, $port > 0 && $port < 65536, "Invalid port number: $port.", 2);

if ($argc < 4) {
    status('Query (e.g. ALL or ALL SINCE 15-Jan-2021): ', false);
    $query = lbtrim(fgets(STDIN));
} else {
    $query = lbtrim($argv[3]);
}

if (!$query) {
    $query = 'ALL';
}

if ($argc < 5) {
    status('Account name: ', false);
    $name = lbtrim(fgets(STDIN));
} else {
    $name = lbtrim($argv[4]);
}

if ($argc < 6) {
    status('Password: ', false);
    system('stty -echo');
    $password = lbtrim(fgets(STDIN));
    system('stty echo');
    status("");
} else {
    $password = lbtrim($argv[5]);
}

status("Connecting to $server:$port as $name... ", false);

$ref = '{' . "$server:$port/imap/ssl}";

$conn = imap_open($ref, $name, $password);
check_error($conn, $conn !== false, "Unable to connect to $server:$port as $name.", 3);

status("OK");
status("Fetching list of folders... ", false);

$folders = imap_list($conn, $ref, "*");
check_error($conn, is_array($folders), 'Error retrieving list of IMAP folders.', 4);

status("OK");

imap_close($conn);

$bytes = 0;
$folder_count = count($folders);
$current_folder = 0;

foreach ($folders as $folder) {
    ++$current_folder;
    $folder_name = substr($folder, strlen($ref));

    status("Fetching mail from $folder_name...");

    $conn = imap_open($folder, $name, $password);
    check_error($conn, $conn !== false, "Unable to connect to $server:$port as $name.", 7);

    $messages = imap_search($conn, $query, SE_UID);

    if (!is_array($messages)) {
        fputs(STDERR, "\nError retrieving list of messages in folder: $folder_name. SKIPPED\n" . imap_last_error() . "\n");
        continue;
    }

    $all = count($messages);
    $current = 0;
    $status = '';
    $last_percent = -1;
    foreach ($messages as $uid) {
        ++$current;
        $percent = ((int)round(($current / $all) * 10000)) / 100;

        if ($percent != $last_percent) {
            $last_percent = $percent;
            status(str_repeat("\x08", strlen($status)), false);
            $status = "$percent% ($bytes bytes downloaded, folders: $current_folder/$folder_count)    ";
            status($status, false);
        }

        $msg = imap_fetchbody($conn, $uid, '', FT_UID | FT_PEEK);
        echo "########## MESSAGE $folder_name / $uid ##########\n$msg\n";
        echo "";
        $bytes += strlen($msg);
    }
    status("\nAll mail from $folder_name has been downloaded.");
    imap_close($conn);
}

?>

