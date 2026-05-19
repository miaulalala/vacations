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
 * @method string getStart()
 * @method void setStart(string $start)
 * @method string getEnd()
 * @method void setEnd(string $end)
 * @method int getDayCount()
 * @method void setDayCount(int $dayCount)
 * @method string getSignature()
 * @method void setSignature(string $signature)
 * @method bool getSignatureVerified()
 * @method void setSignatureVerified(bool $signatureVerified)
 * @method ?string getSignoffUserId()
 * @method void setSignoffUserId(?string $signoffUserId)
 * @method int getStatus()
 * @method void setStatus(int $status)
 * @method ?string getStatusMessage()
 * @method void setStatusMessage(?string $statusMessage)
 * @method ?string getToken()
 * @method void setToken(?string $token)
 * @method ?string getReplacementUserId()
 * @method void setReplacementUserId(?string $replacementUserId)
 * @method ?string getManagerUserId()
 * @method void setManagerUserId(?string $managerUserId)
 * @method ?string getRequestDate()
 * @method void setRequestDate(?string $requestDate)
 * @method ?string getMessage()
 * @method void setMessage(?string $message)
 */
class Vacation extends Entity implements JsonSerializable {

	public const VACATION_PENDING = 0;
	public const VACATION_ACCEPTED = 1;
	public const VACATION_DECLINED = 2;

	protected string $userId = '';
	protected string $start = '';
	protected string $end = '';
	protected int $dayCount = 0;
	protected string $signature = '';
	protected bool $signatureVerified = false;
	protected ?string $signoffUserId = null;
	protected int $status = 0;
	protected ?string $statusMessage = null;
	protected ?string $token = null;
	protected ?string $replacementUserId = null;
	protected ?string $managerUserId = null;
	protected ?string $requestDate = null;
	protected ?string $message = null;

	public function __construct() {
		$this->addType('user_id', 'string');
		$this->addType('start', 'string');
		$this->addType('end', 'string');
		$this->addType('day_count', 'integer');
		$this->addType('signature', 'string');
		$this->addType('signature_verified', 'boolean');
		$this->addType('signoff_user_id', 'string');
		$this->addType('status', 'integer');
		$this->addType('status_message', 'string');
		$this->addType('token', 'string');
		$this->addType('replacement_user_id', 'string');
		$this->addType('manager_user_id', 'string');
		$this->addType('request_date', 'string');
		$this->addType('message', 'string');
	}

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
			'replacementUserId' => $this->replacementUserId,
			'managerUserId' => $this->managerUserId,
			'requestDate' => $this->requestDate,
			'message' => $this->message,
		];
	}
}
