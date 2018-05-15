<?php

/**
 * Filename: index.php
 * Feature: Fetch mails from smg and put them to imap - directory
 *
 * PHP Version 5.1
 *
 * @author  Jonas Hess <mail@jonas-hess.de>
 * @license GNU GPL v3.0
 * @link    https://github.com/re4jh/excavator
 */

require 'credentials.php';
echo "<html><body><pre>";
$cookies = tempnam('/tmp', 'cookie.txt');


// AUX1: Imap Connection

$connect_to = '{' . $aCredentials['imap']['server'];
$connect_to .= ':' . $aCredentials['imap']['port'] . '/imap/ssl}';
$connect_to .= $aCredentials['imap']['folder'];
$imap_conn = imap_open(
    $connect_to,
    $aCredentials['imap']['user'],
    $aCredentials['imap']['password']
)
or die("Can't connect to '$connect_to': " . imap_last_error());


// 01. Get my ViewState

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $aCredentials['smg']['loginpage']);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);

// decode response
curl_setopt($ch, CURLOPT_ENCODING, true);
$response = curl_exec($ch);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($response);
$oVsNode = $doc->getElementById('j_id__v_0:javax.faces.ViewState:1');
$oVsValue = $oVsNode->getAttribute('value');

// 02: Make a Login on SMG

curl_setopt($ch, CURLOPT_URL, $aCredentials['smg']['loginpage']);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);

curl_setopt(
    $ch, CURLOPT_POSTFIELDS,
    'userName=' . $aCredentials['smg']['user']
    . '&password=' . $aCredentials['smg']['pass']
    . '&captchaWasNecessary=false'
    . '&captcha_hidden='
    . '&kickMailMessageCacheKey&org.apache.myfaces.trinidad.faces.FORM=loginForm'
    . '&_noJavaScript=false'
    . '&javax.faces.ViewState='
    . $oVsValue . '&source=submitButton'
);

// receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


$server_output = curl_exec($ch);

$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($server_output, 0, $header_size);

$aHeaders = array();

foreach (explode("\r\n", $header) as $i => $line) {
    if ($i === 0) {
        $aHeaders['http_code'] = $line;
    } else {
        list ($key, $value) = explode(': ', $line);

        $aHeaders[$key] = $value;
    }
}


if ($aHeaders['http_code'] !== "HTTP/1.1 302 Found") {
    die('Login not successfull!');
}

//03: Get my Inbox-List

curl_setopt($ch, CURLOPT_URL, $aCredentials['smg']['inboxpage']);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// decode response
curl_setopt($ch, CURLOPT_ENCODING, true);
$response = curl_exec($ch);

$doc = new DOMDocument();
$doc->loadHTML($response);

$oMessageList = $doc->getElementById('j_id_1:0:messagesList');

$xpath = new DomXPath($doc);

$aMessageLinks = $xpath->query(".//a", $oMessageList);

echo $aMessageLinks->length . ' MessageLinks available!' . "\n";

foreach ($aMessageLinks as $oMessageLink) {
    if ($oMessageLink->hasAttribute('class') == false) {
        echo 'NO CLASS!';
        continue;
    } else {
        $sClasses = $oMessageLink->getAttribute('class');
        if (strpos($sClasses, 'messagelink') !== false
            && strpos($sClasses, 'new') !== false
        ) {
            echo 'New Messagelink found!';
            $sHref = $oMessageLink->getAttribute('href');
            $aParsedUrl = parse_url($sHref);
            $aQuery = array();
            parse_str($aParsedUrl['query'], $aQuery);

            $sDownloadUrl = $aCredentials['smg']['downloadpage']
                . '?id=' . $aQuery['id'] . '&type=eml';

            $sDownloadFile = tempnam('/tmp', $aQuery['id'] . '.eml');

            curl_setopt($ch, CURLOPT_URL, $sDownloadUrl);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $fp = fopen($sDownloadFile, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_exec($ch);
            fclose($fp);

            $message_content = file_get_contents($sDownloadFile);

            imap_append(
                $imap_conn,
                '{' . $aCredentials['imap']['server']
                . ':' . $aCredentials['imap']['port'] . '}'
                . $aCredentials['imap']['folder'],
                $message_content
            );

            unlink($sDownloadFile);
        } else {
            echo 'No new messages found!';
        }
    }
}

curl_close($ch);
unlink($cookies);
imap_close($imap_conn);
echo "</pre>";

exec(
    "/usr/bin/python imapdedup.py -s "
    . $aCredentials['imap']['server']
    . " -p " . $aCredentials['imap']['port']
    . " -u " . $aCredentials['imap']['user']
    . " -x -w " . $aCredentials['imap']['password']
    . " " . $aCredentials['imap']['folder'] . "  2>&1",
    $out, $result
);

echo "Returncode: " . $result . "<br>";
echo "Ausgabe des Scripts: " . "<br>";
echo "<pre>";
print_r($out);
echo "</pre>";

echo "\n--- THE END ---\n";
echo '</body></html>';