gmail_archiver.php
==================

`gmail_archiver.php` is a small PHP script to download all messages from a
given IMAP server. (Probably this should have been a GitHub Gist.)

Usage
-----

You need to enable IMAP access for _less secure apps_ in Gmail.

    ~$ cd my_mails
    ~/my_mails$ php gmail_archiver.php
    Server: imap.gmail.com
    Port: 993
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
    ./your_name@gmail.com/INBOX/1234.txt
    ./your_name@gmail.com/INBOX/1235.txt
    ./your_name@gmail.com/INBOX/1236.txt
    ./your_name@gmail.com/a_label
    ./your_name@gmail.com/a_label/1.txt
    ./your_name@gmail.com/a_label/2.txt
    ./your_name@gmail.com/another_label
    ./your_name@gmail.com/another_label/42.txt
    ./your_name@gmail.com/another_label/43.txt

Or you can use `gmail_archiver_stdout.php` to just print all emails on
stdout and manage them any way you like. (E.g. gzip them into a single file.)

You can run the script automatically if you specify connection parameters
in the arguments.

    ~/my_mails$ php ~/gmail_archiver.php help
    Usage: php gmail_archiver.php [IMAP server [port [account name [password]]]]

Note: be careful when running the script this way as your password might be
recorded into your bash history or be seen in the process list, etc. Specify
your password in the command line only if you are sure you know what you are doing!

