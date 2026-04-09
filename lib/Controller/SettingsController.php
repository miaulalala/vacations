<?php
/**
 * @copyright Copyright (c) 2017  Joas Schilling <coding@schilljs.com>
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Vacation\Controller;

use OCA\Vacation\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;


class SettingsController extends Controller {
	/** @var IConfig */
	private $config;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $config
	 */
	public function __construct($appName,
								IRequest $request,
								IConfig $config) {
		parent::__construct($appName, $request);
		$this->config = $config;
	}

	/**
	 * Saves admin settings
	 */
	public function update(
		string $email = '',
		int $minStartDays = 0,
		int $maxEndDays = 0,
	): DataResponse {
		$this->config->setAppValue(Application::APP_ID, 'vacation_email', $email);
		$this->config->setAppValue(Application::APP_ID, 'min_start_days', (string)$minStartDays);
		$this->config->setAppValue(Application::APP_ID, 'max_end_days', (string)$maxEndDays);
		return new DataResponse([
			'email' => $email,
			'minStartDays' => $minStartDays,
			'maxEndDays' => $maxEndDays,
		]);
	}
}
