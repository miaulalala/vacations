<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Tests\Unit\Db;

use OCA\Vacation\Db\Entitlement;
use PHPUnit\Framework\TestCase;

class EntitlementTest extends TestCase {

	private function makeEntitlement(
		float $accrued = 0,
		float $carriedOver = 0,
		float $adjustment = 0,
		float $taken = 0,
		float $pending = 0,
	): Entitlement {
		$e = new Entitlement();
		$e->setAccruedDays($accrued);
		$e->setCarriedOverDays($carriedOver);
		$e->setAdjustmentDays($adjustment);
		$e->setTakenDays($taken);
		$e->setPendingDays($pending);
		return $e;
	}

	public function testAllZeroIsZero(): void {
		$this->assertSame(0.0, $this->makeEntitlement()->getAvailableDays());
	}

	public function testAccruedOnlyReturnsAccrued(): void {
		$this->assertSame(20.0, $this->makeEntitlement(accrued: 20.0)->getAvailableDays());
	}

	public function testCarriedOverIsAdded(): void {
		$this->assertSame(25.0, $this->makeEntitlement(accrued: 20.0, carriedOver: 5.0)->getAvailableDays());
	}

	public function testPositiveAdjustmentIsAdded(): void {
		$this->assertSame(22.0, $this->makeEntitlement(accrued: 20.0, adjustment: 2.0)->getAvailableDays());
	}

	public function testNegativeAdjustmentIsSubtracted(): void {
		$this->assertSame(18.0, $this->makeEntitlement(accrued: 20.0, adjustment: -2.0)->getAvailableDays());
	}

	public function testTakenDaysAreSubtracted(): void {
		$this->assertSame(15.0, $this->makeEntitlement(accrued: 20.0, taken: 5.0)->getAvailableDays());
	}

	public function testPendingDaysAreSubtracted(): void {
		$this->assertSame(17.0, $this->makeEntitlement(accrued: 20.0, pending: 3.0)->getAvailableDays());
	}

	public function testAllComponentsCombined(): void {
		// 20 accrued + 5 carried + 2 adjustment - 8 taken - 3 pending = 16
		$this->assertSame(16.0, $this->makeEntitlement(
			accrued: 20.0,
			carriedOver: 5.0,
			adjustment: 2.0,
			taken: 8.0,
			pending: 3.0,
		)->getAvailableDays());
	}

	public function testNegativeBalanceIsPossible(): void {
		// taken > accrued — balance goes negative
		$this->assertSame(-2.0, $this->makeEntitlement(accrued: 3.0, taken: 5.0)->getAvailableDays());
	}

	public function testHalfDayPrecision(): void {
		$this->assertSame(0.5, $this->makeEntitlement(accrued: 1.0, taken: 0.5)->getAvailableDays());
	}

	public function testJsonSerializeIncludesAvailableDays(): void {
		$e = $this->makeEntitlement(accrued: 20.0, taken: 5.0);
		$json = $e->jsonSerialize();

		$this->assertArrayHasKey('availableDays', $json);
		$this->assertSame(15.0, $json['availableDays']);
	}

	public function testJsonSerializeIncludesAllFields(): void {
		$e = $this->makeEntitlement(accrued: 10.0, carriedOver: 2.0, adjustment: 1.0, taken: 3.0, pending: 1.0);
		$e->setUserId('alice');
		$e->setLeaveTypeCode('VACATION');
		$e->setYearStart('2025-01-01');
		$e->setYearEnd('2025-12-31');
		$e->setTotalDays(20.0);

		$json = $e->jsonSerialize();

		$this->assertSame('alice', $json['userId']);
		$this->assertSame('VACATION', $json['leaveTypeCode']);
		$this->assertSame('2025-01-01', $json['yearStart']);
		$this->assertSame('2025-12-31', $json['yearEnd']);
		$this->assertSame(20.0, $json['totalDays']);
		$this->assertSame(10.0, $json['accruedDays']);
		$this->assertSame(2.0, $json['carriedOverDays']);
		$this->assertSame(1.0, $json['adjustmentDays']);
		$this->assertSame(3.0, $json['takenDays']);
		$this->assertSame(1.0, $json['pendingDays']);
		$this->assertSame(9.0, $json['availableDays']);
	}
}
