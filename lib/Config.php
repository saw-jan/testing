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

namespace OCA\Testing;

use OCP\IConfig;
use OCP\IRequest;
use OC\OCS\Result;
use OC\Files\Type\Detection;

class Config {

	/** @var IConfig */
	private $config;

	/** @var IRequest */
	private $request;

	/**
	 * @param IConfig $config
	 * @param IRequest $request
	 */
	public function __construct(IConfig $config, IRequest $request) {
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 * @param array $parameters
	 * @return \OC\OCS\Result
	 */
	public function setAppValue($parameters) {
		$app = $parameters['appid'];
		$configKey = $parameters['configkey'];

		$value = $this->request->getParam('value');
		$this->config->setAppValue($app, $configKey, $value);

		return new \OC\OCS\Result();
	}

	/**
	 * @param array $parameters
	 * @return \OC\OCS\Result
	 */
	public function deleteAppValue($parameters) {
		$app = $parameters['appid'];
		$configKey = $parameters['configkey'];

		$this->config->deleteAppValue($app, $configKey);

		return new \OC\OCS\Result();
	}

	/**
	 * @param array $parameters
	 * @return Result
	 */
	public function getAppValue($parameters) {
		$appid = $parameters['appid'];
		$configKey = $parameters['configkey'];
		
		$value = $this->config->getAppValue($appid, $configKey);
		$result[] = [
			'configkey' => $configKey,
			'value' => $value,
			'appid' => $appid
		];
		return new Result($result);
	}

	/**
	 * @return \OC\OCS\Result
	 */
	public function setAppValues() {
		$values = $this->request->getParam('values');

		if (\is_array($values)) {
			foreach ($values as $appEntry) {
				if (\is_array($appEntry)) {
					$this->config->setAppValue(
						$appEntry['appid'],
						$appEntry['configkey'],
						$appEntry['value']);
				}
			}
		}

		return new \OC\OCS\Result();
	}

	/**
	 * @return \OC\OCS\Result
	 */
	public function deleteAppValues() {
		$values = $this->request->getParam('values');
		if (\is_array($values)) {
			foreach ($values as $appEntry) {
				if (\is_array($appEntry)) {
					$this->config->deleteAppValue(
						$appEntry['appid'],
						$appEntry['configkey']
					);
				}
			}
		}

		return new \OC\OCS\Result();
	}

	/**
	 * @param array $parameters
	 *
	 * @return Result
	 */
	public function getAppValues($parameters) {
		$appName = $parameters['appid'];
		$result = [];

		$appKeys = $this->config->getAppKeys($appName);
		
		foreach ($appKeys as $appKey) {
			$value = $this->config->getAppValue(
				$appName,
				$appKey
			);
			$result[] = [
				'configkey' => $appKey,
				'value' => $value,
				'appid' => $appName
			];
		}

		return new Result($result);
	}

	/**
	 * @param array $parameters
	 *
	 * @return Result
	 */
	public function getExtensionForMimeType($parameters) {
		$extensions = [];
		$mimeTypeList = $this->getAllMimeList();
		$mimeTypeNeedle = $parameters['type'];
		foreach ($mimeTypeList as $extension => $mimeType) {
			if (\explode("/", $mimeType[0])[0] === $mimeTypeNeedle) {
				$extensions[] = $extension;
			}
		}
		return new Result($extensions);
	}

	/**
	 * @param array $parameters
	 *
	 * @return Result
	 */
	public function getExtensionForMimeTypeSubType($parameters) {
		$extensions = [];
		$mimeTypeList = $this->getAllMimeList();
		$mimeTypeNeedle = $parameters['type'] . '/' . $parameters['subtype'];
		foreach ($mimeTypeList as $extension => $mimeType) {
			if ($mimeType[0] === $mimeTypeNeedle) {
				$extensions[] = $extension;
			}
		}
		return new Result($extensions);
	}

	/**
	 * get all mime list
	 *
	 * @return array
	 */
	public function getAllMimeList() {
		$configDir = \OC::$SERVERROOT . '/config/';
		$config = new \OC\Config($configDir);
		$server = new \OC\Server(\OC::$WEBROOT, $config);
		$detection = new Detection(
			$server->getURLGenerator(),
			$configDir,
			\OC::$SERVERROOT . '/resources/config/'
		);
		return $detection->getAllMappings();
	}
}
