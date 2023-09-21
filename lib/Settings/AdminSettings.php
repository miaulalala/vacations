<?php

declare(strict_types=1);

/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Vacation\Settings;

use OCA\Mail\AppInfo\Application;
use OCA\Mail\Integration\GoogleIntegration;
use OCA\Mail\Integration\MicrosoftIntegration;
use OCA\Mail\Service\AiIntegrations\AiIntegrationsService;
use OCA\Mail\Service\AntiSpamService;
use OCA\Mail\Service\Provisioning\Manager as ProvisioningManager;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\LDAP\ILDAPProvider;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

	public function __construct(private IInitialState $initialStateService, private IConfig $config) {
	}

	public function getForm() {
		$this->initialStateService->provideInitialState(
			'vacation_email',
			$this->config->getAppValue(Application::APP_ID, 'vacation_email')
		);
		return new TemplateResponse(Application::APP_ID, 'settings-admin');
	}

	public function getSection() {
		return 'vacation';
	}

	public function getPriority() {
		return 100;
	}
}
