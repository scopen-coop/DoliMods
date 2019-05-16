#!/usr/bin/php
<?php
/**
  This script is a sendmail wrapper for php to log calls of the php mail() function.
  Author: Till Brehm, www.ispconfig.org
  (Hopefully) secured by David Goodwin <david @ _palepurple_.co.uk>

  Modify your php.ini file to add:
  sendmail_path = /usr/local/bin/phpsendmail.php
*/

//setlocale(LC_CTYPE, "en_US.UTF-8");

$sendmail_bin = '/usr/sbin/sendmail';
$logfile = '/var/log/phpsendmail.log';

//* Get the email content
$mail='';
$toline = ''; $ccline = ''; $bccline = '';
$nbto = 0; $nbcc = 0; $nbbcc = 0;
$fromline = '';
$referenceline = '';
$emailfrom = '';

$pointer = fopen('php://stdin', 'r');

while ($line = fgets($pointer)) {
        if(preg_match('/^to:/i', $line) ) {
		$toline .= trim($line)."\n";
		$linetmp = preg_replace('/^to:\s*/i','',trim($line));
		$tmpto=preg_split("/[\s,]+/", $linetmp);
		$nbto+=count($tmpto);
        }
	if(preg_match('/^cc:/i', $line) ) {
                $ccline .= trim($line)."\n";
                $linetmp = preg_replace('/^cc:\s*/i','',trim($line));
		$tmpcc=preg_split("/[\s,]+/", $linetmp);
                $nbcc+=count($tmpcc);
        }
	if(preg_match('/^bcc:/i', $line) ) {
                $bccline .= trim($line)."\n";
                $linetmp = preg_replace('/^bcc:\s*/i','',trim($line));
		$tmpbcc=preg_split("/[\s,]+/", $linetmp);
                $nbbcc+=count($tmpbcc);
        }
        if(preg_match('/^from:.*<(.*)>/i', $line, $reg) ) {
                $fromline .= trim($line)."\n";
		$emailfrom = $reg[1];
        }
        if(preg_match('/^references:/i', $line) ) {
                $referenceline .= trim($line)."\n";
        }
        $mail .= $line;
}

$tmpfile='/tmp/phpsendmail-'.posix_getuid().'-'.getmypid().'.tmp';
@unlink($tmpfile);
file_put_contents($tmpfile, $mail);
chmod ($tmpfile, 0660);

//* compose the sendmail command
#$command = 'echo ' . escapeshellarg($mail) . ' | '.$sendmail_bin.' -t -i ';
$command = 'cat '.$tmpfile.' | '.$sendmail_bin.' -t -i ';
$optionffound=0;
for ($i = 1; $i < $_SERVER['argc']; $i++) {
	if (preg_match('/-f/', $_SERVER['argv'][$i])) $optionffound++;
        $command .= escapeshellarg($_SERVER['argv'][$i]).' ';
}

if (! $optionffound)
{
	file_put_contents($logfile, date('Y-m-d H:i:s') . ' option -f not found. Args are '.join(' ',$_SERVER['argv']).'. We get if from the header'."\n", FILE_APPEND);
	$command .= "'-f".$emailfrom."'";
}

$ip=$_SERVER["REMOTE_ADDR"];
if (empty($ip))
{
        file_put_contents($logfile, date('Y-m-d H:i:s') . ' ip unknown. See tmp file '.$tmpfile."\n", FILE_APPEND);
#        exit(1);
}

// Rules
$MAXOK = 10;


//* Write the log
//file_put_contents($logfile, var_export($_SERVER, true)."\n", FILE_APPEND);
file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $toline, FILE_APPEND);
if ($ccline)  file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ccline, FILE_APPEND);
if ($bccline) file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $bccline, FILE_APPEND);
file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $fromline, FILE_APPEND);
file_put_contents($logfile, date('Y-m-d H:i:s') . ' Email detected into From: '. $emailfrom."\n", FILE_APPEND);
file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $referenceline, FILE_APPEND);
file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . (empty($_ENV['PWD'])?(empty($_SERVER["PWD"])?'':$_SERVER["PWD"]):$_ENV['PWD'])." - ".(empty($_SERVER["REQUEST_URI"])?'':$_SERVER["REQUEST_URI"])."\n", FILE_APPEND);


$blacklistofips = @file_get_contents('/tmp/blacklistip');
if (! empty($ip) && $blacklistofips)
{
    $blacklistofipsarray = explode("\n", $blacklistofips);
    if (is_array($blacklistofipsarray) && in_array($ip, $blacklistofipsarray))
    {
        file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' dolicloud rules ko blacklist - exit 2. Blacklisted ip '.$ip." found into file blacklistip\n", FILE_APPEND);
        exit(3);
    }
}

$blacklistoffroms = @file_get_contents('/tmp/blacklistfrom');
if (! empty($emailfrom) && $blacklistoffroms)
{
    $blacklistoffromsarray = explode("\n", $blacklistoffroms);
    if (is_array($blacklistoffromsarray) && in_array($emailfrom, $blacklistoffromsarray))
    {
        file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' dolicloud rules ko blacklist - exit 3. Blacklisted from '.$emailfrom." found into file blacklistfrom\n", FILE_APPEND);
        exit(4);
    }
}

$blacklistofcontents = @file_get_contents('/tmp/blacklistcontent');
if (! empty($mail) && $blacklistofcontents)
{
    $blacklistofcontentsarray = explode("\n", $blacklistofcontents);
    foreach($blacklistofcontentsarray as $blackcontent)
    {
        if (preg_match('/'.preg_quote($blackcontent,'/').'/ims', $mail))
        {
            file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' dolicloud rules ko blacklist - exit 4. Blacklisted content has the key '.$blackcontent." found into file blacklistcontent\n", FILE_APPEND);
            // Save spam mail content and ip
            file_put_contents('/tmp/blacklistmail', $mail."\n", FILE_APPEND);
            chmod("/tmp/blacklistmail", 0666);
            if (! empty($ip))
            {
                file_put_contents('/tmp/blacklistip', $ip."\n", FILE_APPEND);
                chmod("/tmp/blacklistip", 0666);
            }
            exit(5);
        }
    }
}

if (empty($fromline) && empty($emailfrom))
{
	file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' cant send email - exit 1. From not provided. See tmp file '.$tmpfile."\n", FILE_APPEND);
	exit(1);
}
elseif (($nbto + $nbcc + $nbbcc) > $MAXOK)
{
    file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' dolicloud rules ko toomanyrecipient - exit 2. ( >'.$MAXOK.' : ' . $nbto . ' ' . $nbcc . ' ' . $nbbcc . ' ) ' . (empty($_ENV['PWD'])?'':$_ENV['PWD'])."\n", FILE_APPEND);
    exit(2);
}
else
{
    file_put_contents($logfile, date('Y-m-d H:i:s') . ' ' . $ip . ' dolicloud rules ok ( <'.$MAXOK.' : ' . $nbto . ' ' . $nbcc . ' ' . $nbbcc . ' ) ' . (empty($_ENV['PWD'])?'':$_ENV['PWD'])."\n", FILE_APPEND);
}



file_put_contents($logfile, $command."\n", FILE_APPEND);

//* Execute the command
$resexec =  shell_exec($command);

if (empty($ip)) file_put_contents($logfile, "--- no ip detected ---", FILE_APPEND);
if (empty($ip)) file_put_contents($logfile, var_export($_SERVER, true), FILE_APPEND);
if (empty($ip)) file_put_contents($logfile, var_export($_ENV, true), FILE_APPEND);

return $resexec;
