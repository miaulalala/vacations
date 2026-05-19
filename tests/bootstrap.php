<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

require_once __DIR__ . '/../../../tests/bootstrap.php';

spl_autoload_register(function (string $className): void {
	$prefix = 'OCA\\Vacation\\';
	$baseDir = __DIR__ . '/../lib/';
	if (str_starts_with($className, $prefix)) {
		$file = $baseDir . str_replace('\\', '/', substr($className, strlen($prefix))) . '.php';
		if (file_exists($file)) {
			require_once $file;
		}
	}
});
