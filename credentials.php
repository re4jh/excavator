<?php

/**
 * Filename: credentials.php
 * Feature: credentials for connecting to imap and smg
 *
 * @author  Jonas Hess <mail@jonas-hess.de>
 * @license GNU GPL v3.0
 * @link    https://github.com/re4jh/excavator
 */

$aCredentials = array();

$aCredentials['imap']['password']    = '123456';
$aCredentials['imap']['server']      = 'imap.mymailserver.org';
$aCredentials['imap']['port']        = '993';
$aCredentials['imap']['user']        = 'george.orwell@mymailserver.org';
$aCredentials['imap']['folder']      = 'SmgImport';
$aCredentials['smg']['host']         = 'smg.domain.com';
$aCredentials['smg']['downloadpage'] = 'https://' . $aCredentials['smg']['host'] . '/message-download.xhtml';
$aCredentials['smg']['inboxpage']    = 'https://' . $aCredentials['smg']['host'] . '/inbox.xhtml';
$aCredentials['smg']['loginpage']    = 'https://' . $aCredentials['smg']['host'] . '/login.xhtml';
$aCredentials['smg']['pass']         = 'hallo123';
$aCredentials['smg']['user']         = 'george.orwell@mymailserver.org';

