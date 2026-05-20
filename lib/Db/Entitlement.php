<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method string getLeaveTypeCode()
 * @method void setLeaveTypeCode(string $leaveTypeCode)
 * @method string getYearStart()
 * @method void setYearStart(string $yearStart)
 * @method string getYearEnd()
 * @method void setYearEnd(string $yearEnd)
 * @method float getTotalDays()
 * @method void setTotalDays(float $totalDays)
 * @method float getAccruedDays()
 * @method void setAccruedDays(float $accruedDays)
 * @method float getCarriedOverDays()
 * @method void setCarriedOverDays(float $carriedOverDays)
 * @method float getAdjustmentDays()
 * @method void setAdjustmentDays(float $adjustmentDays)
 * @method float getTakenDays()
 * @method void setTakenDays(float $takenDays)
 * @method float getPendingDays()
 * @method void setPendingDays(float $pendingDays)
 * @method ?string getCarryoverExpiryDate()
 * @method void setCarryoverExpiryDate(?string $carryoverExpiryDate)
 */
class Entitlement extends Entity implements JsonSerializable {

	protected string $userId = '';
	protected string $leaveTypeCode = '';
	protected string $yearStart = '';
	protected string $yearEnd = '';
	protected float $totalDays = 0.0;
	protected float $accruedDays = 0.0;
	protected float $carriedOverDays = 0.0;
	protected float $adjustmentDays = 0.0;
	protected float $takenDays = 0.0;
	protected float $pendingDays = 0.0;
	protected ?string $carryoverExpiryDate = null;

	public function __construct() {
		$this->addType('user_id', 'string');
		$this->addType('leave_type_code', 'string');
		$this->addType('year_start', 'string');
		$this->addType('year_end', 'string');
		$this->addType('total_days', 'float');
		$this->addType('accrued_days', 'float');
		$this->addType('carried_over_days', 'float');
		$this->addType('adjustment_days', 'float');
		$this->addType('taken_days', 'float');
		$this->addType('pending_days', 'float');
		$this->addType('carryover_expiry_date', 'string');
	}

	/**
	 * Balance available to book: accrued + carried over + adjustments - taken - pending.
	 */
	public function getAvailableDays(): float {
		return $this->accruedDays + $this->carriedOverDays + $this->adjustmentDays
			- $this->takenDays - $this->pendingDays;
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'leaveTypeCode' => $this->leaveTypeCode,
			'yearStart' => $this->yearStart,
			'yearEnd' => $this->yearEnd,
			'totalDays' => $this->totalDays,
			'accruedDays' => $this->accruedDays,
			'carriedOverDays' => $this->carriedOverDays,
			'adjustmentDays' => $this->adjustmentDays,
			'takenDays' => $this->takenDays,
			'pendingDays' => $this->pendingDays,
			'carryoverExpiryDate' => $this->carryoverExpiryDate,
			'availableDays' => $this->getAvailableDays(),
		];
	}
}
