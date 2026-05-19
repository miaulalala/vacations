<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Tests\Unit\Service;

use OCA\Vacation\Db\Vacation;
use OCA\Vacation\Db\VacationMapper;
use OCA\Vacation\Service\VacationNotAuthorized;
use OCA\Vacation\Service\VacationNotFound;
use OCA\Vacation\Service\VacationNotPending;
use OCA\Vacation\Service\VacationService;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VacationServiceTest extends TestCase {

	private VacationMapper&MockObject $mapper;
	private VacationService $service;

	protected function setUp(): void {
		$this->mapper = $this->createMock(VacationMapper::class);
		$this->service = new VacationService($this->mapper);
	}

	// ---- helpers ----

	private function pendingVacation(string $userId = 'alice', string $managerId = 'bob'): Vacation {
		$v = new Vacation();
		$v->setUserId($userId);
		$v->setManagerUserId($managerId);
		$v->setStatus(Vacation::VACATION_PENDING);
		$v->setStart('2025-07-01');
		$v->setEnd('2025-07-14');
		$v->setDayCount(10);
		$v->setSignature('Alice Example');
		$v->setSignatureVerified(true);
		return $v;
	}

	// ---- find ----

	public function testFindThrowsVacationNotFoundWhenMissing(): void {
		$this->mapper->method('find')
			->willThrowException(new DoesNotExistException('not found'));

		$this->expectException(VacationNotFound::class);
		$this->service->find(99, 'alice');
	}

	// ---- update ----

	public function testUpdateThrowsVacationNotFoundWhenMissing(): void {
		$this->mapper->method('find')
			->willThrowException(new DoesNotExistException('not found'));

		$this->expectException(VacationNotFound::class);
		$this->service->update(99, 'alice', '2025-07-01', '2025-07-14', 10, 'sig', true, '', '');
	}

	public function testUpdateThrowsVacationNotPendingWhenAlreadyApproved(): void {
		$vacation = $this->pendingVacation();
		$vacation->setStatus(Vacation::VACATION_ACCEPTED);

		$this->mapper->method('find')->willReturn($vacation);

		$this->expectException(VacationNotPending::class);
		$this->service->update(1, 'alice', '2025-07-01', '2025-07-14', 10, 'sig', true, '', '');
	}

	public function testUpdateThrowsVacationNotPendingWhenDeclined(): void {
		$vacation = $this->pendingVacation();
		$vacation->setStatus(Vacation::VACATION_DECLINED);

		$this->mapper->method('find')->willReturn($vacation);

		$this->expectException(VacationNotPending::class);
		$this->service->update(1, 'alice', '2025-07-01', '2025-07-14', 10, 'sig', true, '', '');
	}

	public function testUpdateSucceedsWhenPending(): void {
		$vacation = $this->pendingVacation();

		$this->mapper->method('find')->willReturn($vacation);
		$this->mapper->method('update')->willReturnArgument(0);

		$result = $this->service->update(1, 'alice', '2025-08-01', '2025-08-14', 10, 'sig', true, '', 'bob');

		$this->assertSame('2025-08-01', $result->getStart());
	}

	// ---- approve ----

	public function testApproveThrowsVacationNotFoundWhenMissing(): void {
		$this->mapper->method('findById')
			->willThrowException(new DoesNotExistException('not found'));

		$this->expectException(VacationNotFound::class);
		$this->service->approve(99, 'bob');
	}

	public function testApproveThrowsVacationNotAuthorizedForWrongManager(): void {
		$vacation = $this->pendingVacation(managerId: 'bob');
		$this->mapper->method('findById')->willReturn($vacation);

		$this->expectException(VacationNotAuthorized::class);
		$this->service->approve(1, 'mallory');
	}

	public function testApproveThrowsVacationNotPendingWhenAlreadyApproved(): void {
		$vacation = $this->pendingVacation();
		$vacation->setStatus(Vacation::VACATION_ACCEPTED);
		$this->mapper->method('findById')->willReturn($vacation);

		$this->expectException(VacationNotPending::class);
		$this->service->approve(1, 'bob');
	}

	public function testApproveThrowsVacationNotPendingWhenDeclined(): void {
		$vacation = $this->pendingVacation();
		$vacation->setStatus(Vacation::VACATION_DECLINED);
		$this->mapper->method('findById')->willReturn($vacation);

		$this->expectException(VacationNotPending::class);
		$this->service->approve(1, 'bob');
	}

	public function testApproveSucceedsSetsStatusAndSignoff(): void {
		$vacation = $this->pendingVacation(managerId: 'bob');
		$this->mapper->method('findById')->willReturn($vacation);
		$this->mapper->method('update')->willReturnArgument(0);

		$result = $this->service->approve(1, 'bob', 'Approved!');

		$this->assertSame(Vacation::VACATION_ACCEPTED, $result->getStatus());
		$this->assertSame('bob', $result->getSignoffUserId());
		$this->assertSame('Approved!', $result->getStatusMessage());
	}

	// ---- decline ----

	public function testDeclineThrowsVacationNotFoundWhenMissing(): void {
		$this->mapper->method('findById')
			->willThrowException(new DoesNotExistException('not found'));

		$this->expectException(VacationNotFound::class);
		$this->service->decline(99, 'bob');
	}

	public function testDeclineThrowsVacationNotAuthorizedForWrongManager(): void {
		$vacation = $this->pendingVacation(managerId: 'bob');
		$this->mapper->method('findById')->willReturn($vacation);

		$this->expectException(VacationNotAuthorized::class);
		$this->service->decline(1, 'mallory');
	}

	public function testDeclineThrowsVacationNotPendingWhenAlreadyDeclined(): void {
		$vacation = $this->pendingVacation();
		$vacation->setStatus(Vacation::VACATION_DECLINED);
		$this->mapper->method('findById')->willReturn($vacation);

		$this->expectException(VacationNotPending::class);
		$this->service->decline(1, 'bob');
	}

	public function testDeclineSucceedsSetsStatusAndSignoff(): void {
		$vacation = $this->pendingVacation(managerId: 'bob');
		$this->mapper->method('findById')->willReturn($vacation);
		$this->mapper->method('update')->willReturnArgument(0);

		$result = $this->service->decline(1, 'bob', 'Too late in the year');

		$this->assertSame(Vacation::VACATION_DECLINED, $result->getStatus());
		$this->assertSame('bob', $result->getSignoffUserId());
		$this->assertSame('Too late in the year', $result->getStatusMessage());
	}

	// ---- delete ----

	public function testDeleteThrowsVacationNotFoundWhenMissing(): void {
		$this->mapper->method('find')
			->willThrowException(new DoesNotExistException('not found'));

		$this->expectException(VacationNotFound::class);
		$this->service->delete(99, 'alice');
	}

	public function testDeleteSucceedsAndReturnsVacation(): void {
		$vacation = $this->pendingVacation();
		$this->mapper->method('find')->willReturn($vacation);
		$this->mapper->expects($this->once())->method('delete')->with($vacation);

		$result = $this->service->delete(1, 'alice');
		$this->assertSame($vacation, $result);
	}
}
