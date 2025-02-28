<?php
/**
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Testing\AppInfo;

use OCA\Testing\ApacheModules;
use OCA\Testing\BigFileID;
use OCA\Testing\Config;
use OCA\Testing\DavSlowdown;
use OCA\Testing\LastLoginDate;
use OCA\Testing\Locking\Provisioning;
use OCA\Testing\Logfile;
use OCA\Testing\Notifications;
use OCA\Testing\Occ;
use OCA\Testing\Opcache;
use OCA\Testing\ServerFiles;
use OCA\Testing\SysInfo;
use OCP\API;
use OCA\Testing\TestingSkeletonDirectory;
use OCA\Testing\TrustedServersHandler;
use OCA\Testing\FilesProperties;

$config = new Config(
	\OC::$server->getConfig(),
	\OC::$server->getRequest()
);

API::register(
	'post',
	'/apps/testing/api/v1/app/{appid}/{configkey}',
	[$config, 'setAppValue'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/app/{appid}/{configkey}',
	[$config, 'deleteAppValue'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/app/{appid}/{configkey}',
	[$config, 'getAppValue'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/apps',
	[$config, 'setAppValues'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/apps',
	[$config, 'deleteAppValues'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/app/{appid}',
	[$config, 'getAppValues'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/getextension/{type}',
	[$config, 'getExtensionForMimeType'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/getextension/{type}/{subtype}',
	[$config, 'getExtensionForMimeTypeSubType'],
	'testing',
	API::ADMIN_AUTH
);

$locking = new Provisioning(
	\OC::$server->getLockingProvider(),
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getConfig(),
	\OC::$server->getRequest()
);
API::register('get', '/apps/testing/api/v1/lockprovisioning', [$locking, 'isLockingEnabled'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('get', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'isLocked'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('post', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'acquireLock'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('put', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'changeLock'], 'files_lockprovisioning', API::ADMIN_AUTH);
API::register('delete', '/apps/testing/api/v1/lockprovisioning/{type}/{user}', [$locking, 'releaseLock'], 'files_lockprovisioning', API::ADMIN_AUTH);

//release all locks of the given type that were set by the testing app
API::register(
	'delete',
	'/apps/testing/api/v1/lockprovisioning/{type}',
	[$locking, 'releaseAll'],
	'files_lockprovisioning',
	API::ADMIN_AUTH
);
//release all locks that were set by the testing app
//if global=true in the requests also locks that were not set by the testing app get cleared
API::register(
	'delete',
	'/apps/testing/api/v1/lockprovisioning',
	[$locking, 'releaseAll'],
	'files_lockprovisioning',
	API::ADMIN_AUTH
);

$bigFileID = new BigFileID(
	\OC::$server->getDatabaseConnection(),
	\OC::$server->getLogger()
);

API::register(
	'post',
	'/apps/testing/api/v1/increasefileid',
	[$bigFileID, 'increaseFileIDsBeyondMax32bits'],
	'testing',
	API::ADMIN_AUTH
);

$occ = new Occ(\OC::$server->getRequest());

API::register(
	'post',
	'/apps/testing/api/v1/occ',
	[$occ, 'execute'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/occ/bulk',
	[$occ, 'bulkOccExecute'],
	'testing',
	API::ADMIN_AUTH
);

$apacheMod = new ApacheModules((\OC::$server->getRequest()));

API::register(
	'get',
	'/apps/testing/api/v1/apache_modules/{module}',
	[$apacheMod, 'getModule'],
	'testing',
	API::ADMIN_AUTH
);

$opcache = new Opcache();

API::register(
	'delete',
	'/apps/testing/api/v1/opcache',
	[$opcache, 'execute'],
	'testing',
	API::ADMIN_AUTH
);

$notifications = new Notifications(
	'notificationsacceptancetesting',
	\OC::$server->getRequest(),
	\OC::$server->getNotificationManager()
);
API::register(
	'delete',
	'/apps/testing/api/v1/notifications',
	[$notifications, 'deleteNotifications'],
	'notifications'
);
API::register(
	'post',
	'/apps/testing/api/v1/notifications',
	[$notifications, 'addNotification'],
	'notifications'
);

$logFile = new Logfile();

API::register(
	'get',
	'/apps/testing/api/v1/logfile',
	[$logFile, 'read'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/logfile/{lines}',
	[$logFile, 'read'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/logfile',
	[$logFile, 'clear'],
	'testing',
	API::ADMIN_AUTH
);

$sysInfo = new SysInfo();

API::register(
	'get',
	'/apps/testing/api/v1/sysinfo',
	[$sysInfo, 'read'],
	'testing',
	API::ADMIN_AUTH
);

$serverFiles = new ServerFiles(
	\OC::$server->getRequest()
);

API::register(
	'post',
	'/apps/testing/api/v1/dir',
	[$serverFiles, 'mkDir'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/dir',
	[$serverFiles, 'rmDir'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/file',
	[$serverFiles, 'createFile'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'delete',
	'/apps/testing/api/v1/file',
	[$serverFiles, 'deleteFile'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/dir',
	[$serverFiles, 'listFiles'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/file',
	[$serverFiles, 'readFile'],
	'testing',
	API::ADMIN_AUTH
);

$davSlowDown = new DavSlowdown();

API::register(
	'put',
	'/apps/testing/api/v1/davslowdown/{method}/{seconds}',
	[$davSlowDown, 'setSlowdown'],
	'testing',
	API::ADMIN_AUTH
);

$skeletonDirectory = new TestingSkeletonDirectory(\OC::$server->getRequest());

API::register(
	'get',
	'/apps/testing/api/v1/testingskeletondirectory',
	[$skeletonDirectory, 'get'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'post',
	'/apps/testing/api/v1/testingskeletondirectory',
	[$skeletonDirectory, 'set'],
	'testing',
	API::ADMIN_AUTH
);

$trustedServers = new TrustedServersHandler(\OC::$server->getRequest());

API::register(
	'get',
	'/apps/testing/api/v1/trustedservers',
	[$trustedServers, 'defaultHandler'],
	'testing',
	API::ADMIN_AUTH,
	['getTrustedServers']
);

API::register(
	'delete',
	'/apps/testing/api/v1/trustedservers',
	[$trustedServers, 'defaultHandler'],
	'testing',
	API::ADMIN_AUTH,
	['removeTrustedServer']
);

API::register(
	'delete',
	'/apps/testing/api/v1/trustedservers/all',
	[$trustedServers, 'defaultHandler'],
	'testing',
	API::ADMIN_AUTH,
	['removeAllTrustedServers']
);

API::register(
	'post',
	'/apps/testing/api/v1/trustedservers',
	[$trustedServers, 'defaultHandler'],
	'testing',
	API::ADMIN_AUTH,
	['addTrustedServer']
);

// files properties (working with the *properties table)
$filesProperties = new FilesProperties(
	\OC::$server->getDatabaseConnection(), \OC::$server->getRequest()
);

API::register(
	'PUT',
	'/apps/testing/api/v1/files_properties',
	[$filesProperties, 'upsertProperty'],
	'testing',
	API::ADMIN_AUTH
);

$date = new LastLoginDate(\OC::$server->getAccountMapper(), \OC::$server->getRequest());

API::register(
	'post',
	'/apps/testing/api/v1/lastlogindate/{user}',
	[$date, 'setLastLoginDate'],
	'testing',
	API::ADMIN_AUTH
);

API::register(
	'get',
	'/apps/testing/api/v1/lastlogindate/{user}',
	[$date, 'getLastLoginDate'],
	'testing',
	API::ADMIN_AUTH
);
