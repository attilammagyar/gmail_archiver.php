gmail_archiver.php
==================

`gmail_archiver.php` is a small PHP script to download all messages from a
given IMAP server. (Probably this should have been a GitHub Gist.)

Usage
-----

Create an app password for your GMail account, then run the script like this:

    ~$ cd my_mails
    ~/my_mails$ php gmail_archiver.php
    Server: imap.gmail.com
    Port: 993
    Query (e.g. ALL or ALL SINCE 15-Jan-2021): ALL
    Account name: your_name@gmail.com
    Password:

(When you type your password it will not be displayed on the screen.)

The script will create a directory named after your account name in the
directory where you are running it. Inside that directory subdirectories will
be created according to your IMAP folders. You will find your mails in those
subdirectories after the script terminates.

Example:

    ~/my_mails$ find
    .
    ./your_name@gmail.com
    ./your_name@gmail.com/INBOX
    ./your_name@gmail.com/INBOX/12/1234.txt
    ./your_name@gmail.com/INBOX/12/1235.txt
    ./your_name@gmail.com/INBOX/12/1236.txt
    ./your_name@gmail.com/a_label
    ./your_name@gmail.com/a_label/12/1237.txt
    ./your_name@gmail.com/a_label/12/1238.txt
    ./your_name@gmail.com/another_label
    ./your_name@gmail.com/another_label/12/1239.txt
    ./your_name@gmail.com/another_label/12/1240.txt

Or you can use `gmail_archiver_stdout.php` to just print all emails on
stdout and manage them any way you like. (E.g. gzip them into a single file.)

You can run the script automatically if you specify connection parameters
in the arguments.

    ~/my_mails$ php ~/gmail_archiver.php help
    Usage: php gmail_archiver.php [IMAP server [port [query [account name [password]]]]]

WARNING: do not run the script with your app password in the command line on
shared or untrusted computers, because it will be visible in the process list
and it might end up in the shell history!
