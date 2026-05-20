<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Controller;

use OCA\Vacation\AppInfo\Application;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends OCSController {

	public function __construct(
		IRequest $request,
		private IConfig $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		return new DataResponse([
			'minStartDays' => (int)$this->config->getAppValue(Application::APP_ID, 'min_start_days', '0'),
			'maxEndDays' => (int)$this->config->getAppValue(Application::APP_ID, 'max_end_days', '0'),
		]);
	}

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
