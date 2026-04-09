<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

return [
	'ocs' => [
		['name' => 'Vacation#index', 'url' => '/api/v1/vacation', 'verb' => 'GET'],
		['name' => 'Vacation#create', 'url' => '/api/v1/vacation', 'verb' => 'POST'],
		['name' => 'Vacation#pendingApprovals', 'url' => '/api/v1/vacation/pending', 'verb' => 'GET'],
		['name' => 'Vacation#approve', 'url' => '/api/v1/vacation/{id}/approve', 'verb' => 'POST'],
		['name' => 'Vacation#decline', 'url' => '/api/v1/vacation/{id}/decline', 'verb' => 'POST'],
		['name' => 'Vacation#update', 'url' => '/api/v1/vacation/{id}', 'verb' => 'PUT'],
		['name' => 'Vacation#destroy', 'url' => '/api/v1/vacation/{id}', 'verb' => 'DELETE'],
		['name' => 'Vacation#users', 'url' => '/api/v1/users', 'verb' => 'GET'],
		['name' => 'Vacation#currentUserManager', 'url' => '/api/v1/manager', 'verb' => 'GET'],
		['name' => 'Vacation#config', 'url' => '/api/v1/config', 'verb' => 'GET'],
	],
	'routes' => [
		['name' => 'Page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'Settings#update', 'url' => '/api/v1/admin-settings', 'verb' => 'POST'],
	],
];
