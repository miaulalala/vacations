<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'vacation';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
