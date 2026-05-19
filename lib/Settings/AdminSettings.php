<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Settings;

use OCA\Vacation\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {

	public function __construct(
		private IInitialState $initialStateService,
		private IConfig $config,
	) {
	}

	public function getForm(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'vacation-admin-settings');
		Util::addStyle(Application::APP_ID, 'vacation-admin-settings');
		$this->initialStateService->provideInitialState(
			'vacation_email',
			$this->config->getAppValue(Application::APP_ID, 'vacation_email', '')
		);
		$this->initialStateService->provideInitialState(
			'min_start_days',
			(int)$this->config->getAppValue(Application::APP_ID, 'min_start_days', '0')
		);
		$this->initialStateService->provideInitialState(
			'max_end_days',
			(int)$this->config->getAppValue(Application::APP_ID, 'max_end_days', '0')
		);
		return new TemplateResponse(Application::APP_ID, 'admin-settings', [], TemplateResponse::RENDER_AS_BLANK);
	}

	public function getSection(): string {
		return 'vacation';
	}

	public function getPriority(): int {
		return 90;
	}
}
