Charles Wolfe's Facebook Like ALL script written in PHP
==============
Not sure if I can legally say 'Facebook' or not.
"the book, with the faces" like ALL friends posts in PHP

![Script in action](https://i.imgur.com/Ll0irSu.jpg)

This script will allow you to like [almost]everything your friends post.
What a supportive and kind person you are. You must have read Dale Carnegie. Okay, maybe not, but you get the gist.

This is written in PHP, so all you have to do is run a cron job for wherever you put it.
You need PHP installed, but should run on Windows, OSX and Linux.
Uses lib cURL.

Facebook SDK not required.
PHP curl support required.
Must create your own facebook app, which requires a Facebook account (which, for some reason, may require a phone number, use a throwaway).
Requires "the book, with the faces" publish_actions and read_stream permissions



The scripts:
index.html - designed to get my long access token
If running in production, you are going to want to gett he long access token server side, not client side

facebooklikeall.php - this is designed to be called from another script, you pass it the users long access toekn and user id. The idea is that if you had several accounts, you could fork off a bunch of these for each account. I call mine very hour, and it looks at posts 30 minites before the last run time (just to be safe). This doesnt need to be be avaiabale publically, treat it as CGI.

I have no affiliation with Facebook, this is not official or anything.



