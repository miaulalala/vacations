<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use JsonSerializable;

use OCA\Vacation\Service\UnknownVacationStatus;
use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getStart(): string
 * @method setStart(string $start): void
 * @method getEnd(): string
 * @method setEnd(string $end): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 * @method getDayCount(): int
 * @method setDayCount(int $dayCount):
 * @method getSignature(): string
 * @method setSignature(string $signature): void
 * @method isSignatureVerified(): bool
 * @method setSignatureVerified(bool $signatureVerified): void
 * @method getSignoffUserId(): string;
 * @method setSignoffUserId(string $signoffUserId): void;
 * @method getStatus(): int;
 * @method getStatusMessage(): string;
 * @method setStatusMessage(string $statusMessage): void;
 * @method getToken(): string;
 * @method setToken(string $token): void;
 *
 */
class Vacation extends Entity implements JsonSerializable {
	public CONST VACATION_PENDING = 0;
	public CONST VACATION_ACCEPTED = 1;
	public CONST VACATION_DECLINED = 2;
	protected string $userId;
	protected string $start;
	protected string $end;
	protected int $dayCount;
	protected string $signature;
	protected bool $signatureVerified;
	protected string $signoffUserId;
	protected int $status;
	protected string $statusMessage;
	protected string $token;

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'start' => $this->start,
			'end' => $this->end,
			'dayCount' => $this->dayCount,
			'signature' => $this->signature,
			'signatureVerified' => $this->signatureVerified,
			'signoffUserId' => $this->signoffUserId,
			'status' => $this->status,
			'statusMessage' => $this->statusMessage,
			'token' => $this->token,
		];
	}

	public function setStatus(int $status): void {
		if(in_array($status, [self::VACATION_ACCEPTED, self::VACATION_DECLINED, self::VACATION_PENDING])) {
			$this->status = $status;
		}
	}
}
