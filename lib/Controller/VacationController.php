<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Controller;

use OC\AppFramework\Http;
use OC\Core\Controller\OCSController;
use OCA\Vacation\AppInfo\Application;
use OCA\Vacation\Http\JsonResponse;
use OCA\Vacation\Service\VacationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class VacationController extends OCSController {
	public function __construct(IRequest $request, private string $userId, private VacationService $service) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): DataResponse {
		$vacations = $this->service->findAll($this->userId);
		return new DataResponse([$vacations], Http::STATUS_OK);
	}

	public function create(string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified): DataResponse {
		$vacation = $this->service->create($this->userId, $start, $end, $dayCount, $signature, $signatureVerified);
		return new DataResponse([$vacation], Http::STATUS_OK);
	}

	public function update(int $id,
		string $start,
		string $end,
		int $dayCount,
		string $signature,
		bool $signatureVerified): DataResponse {
		$vacation = $this->service->update($id, $this->userId, $start, $end, $dayCount, $signature, $signatureVerified);
		return new DataResponse([$vacation], Http::STATUS_OK);
	}

	public function destroy(int $id): DataResponse {
		$this->service->delete($id, $this->userId);
		return new DataResponse([], Http::STATUS_OK);
	}
}
