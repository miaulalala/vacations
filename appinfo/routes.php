<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Vacation\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'ocs' => [
		['name' => 'Vacation#index', 'url' => '/api/v1/vacation', 'verb' => 'GET'],
		['name' => 'Vacation#create', 'url' => '/api/v1/vacation', 'verb' => 'POST'],
		['name' => 'Vacation#update', 'url' => '/api/v1/vacation', 'verb' => 'PUT'],
		['name' => 'Vacation#destroy', 'url' => '/api/v1/vacation', 'verb' => 'DELETE']
	],
	'routes' => [
		[
			'name' => 'Page#index',
			'url' => '/',
			'verb' => 'GET'
		],
		['name' => 'AdminSettings#update', 'url' => '/api/v1/admin-settings', 'verb' => 'POST'],

	]
];
