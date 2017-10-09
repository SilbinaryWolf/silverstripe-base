<?php

use SilverStripe\Control\Director;
use SilverStripe\ORM\Connect\MySQLDatabase;
use SilverStripe\SQLite\SQLite3Database;

if (PHP_SAPI != 'cli') {
	header("HTTP/1.0 404 Not Found");
	exit;
}

$_SERVER['SCRIPT_FILENAME'] = __FILE__;
chdir(dirname(dirname(dirname(__FILE__))).'/framework');
require_once 'core/Core.php';

// Support Silverstripe 4.x or Silverstripe 3.x
$baseFolder = class_exists(Director::class) ? Director::baseFolder() : \Director::baseFolder();
$outfile = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : $outfile = $baseFolder.'/mysite/scripts/backup-'.date('Y-m-d-H-i-s').'.sql.gz';

global $databaseConfig;

switch ($databaseConfig['type']) {
	case 'MySQLDatabase': // Backwards compat, Silverstripe 3.x support
	case MySQLDatabase::class:
		$u = $databaseConfig['username'];
		$p = $databaseConfig['password'];
		$h = $databaseConfig['server'];
		$d = $databaseConfig['database'];

		$cmd = "mysqldump --user=".escapeshellarg($u)." --password=".escapeshellarg($p)." --ignore-table=$d.details --host=".escapeshellarg($h)." ".escapeshellarg($d)." | gzip > ".escapeshellarg($outfile);
		exec($cmd);
		break;
	case 'SQLiteDatabase': // Backwards compat, Silverstripe 3.x support
	case SQLite3Database::class:
		$d = $databaseConfig['database'];
		$path = realpath(dirname(__FILE__).'/../../assets/.sqlitedb/'.$d);
		$cmd = "sqlite3 ".escapeshellarg($path)." .dump | gzip > ".escapeshellarg($outfile);
		exec($cmd);
		break;
	default: break;
}


