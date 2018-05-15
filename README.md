# Excavator

## Motivation

With the introduction of GDPR we have a lot of funny inventions beeing sold as neccesary evils.
One of these are the so-called "SecureMail Gateways".

As a receiver from outside beeing forced to login somewhere on a webserver instead of having the actual mail in my mailbox is annoying to me.
Also automatically sent acknowledgements of receipt do rather harm than protect my privacy.
There is only a little more transport security but no real security advantage for me, because mails on this servers are probably still unencrypted and still probably readable for operators of this Servers.

So while managment tells us that there is more security now, they are just enjoying the centralisation.
We all know:

 - There is no security by obsurity
 - Good Message Encryption works end-to-end
 - ["Bullshit made in Germany"](https://youtu.be/p56aVppK2W4) aka De-Mail failed.
 - The beA aka "Besonderes elektronisches Anwaltspostfach" [failed, too](https://youtu.be/I_tyTYAVYDo)

But my german diocese still does not know. And so they introduced another piece of centralised crypto-stuff, that has at least in my humble opinion some defective design.

## Solution
With this tool, you can completely simulate a remote login on their gateway, download your inbox-messages there and save it in one of your own IMAP-Directories, so that you get the messages in your old-famliar mail-client. Possibly even without sending those acknowledgements of receipt.

## Warning!
**Storing passwords in plaintext is a very risky thing!**
But to get this tool running, you need to save the credentials for your Mail-Gateway and your IMAP-Server in the *credentials.php*. So please never ever try this on file-storages where other people have access to and always transfer this file  secure.

You are using this tool on your own risk.


## Used Sources
Thanks to Quentin Stafford-Fraser for the file *imapdedup.py*, which I found on [GitHub](https://github.com/quentinsf/IMAPdedup) under GPLv2.
IMAPdedup is great, because we do not want to mark the downloaded mails as read and do not want to have duplicates in our IMAP directory. Thats what IMAPdedup solves.