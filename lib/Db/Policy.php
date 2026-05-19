<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method string getCountryCode()
 * @method void setCountryCode(string $countryCode)
 * @method string getName()
 * @method void setName(string $name)
 * @method string getSource()
 * @method void setSource(string $source)
 * @method string getDefinition()
 * @method void setDefinition(string $definition)
 * @method bool getIsActive()
 * @method void setIsActive(bool $isActive)
 */
class Policy extends Entity implements JsonSerializable {

	public const SOURCE_BUNDLED = 'bundled';
	public const SOURCE_CUSTOM = 'custom';

	protected string $countryCode = '';
	protected string $name = '';
	protected string $source = self::SOURCE_CUSTOM;
	protected string $definition = '{}';
	protected bool $isActive = true;

	public function __construct() {
		$this->addType('country_code', 'string');
		$this->addType('name', 'string');
		$this->addType('source', 'string');
		$this->addType('definition', 'string');
		$this->addType('is_active', 'boolean');
	}

	public function getDefinitionArray(): array {
		return json_decode($this->definition, true) ?? [];
	}

	public function setDefinitionArray(array $definition): void {
		$this->setDefinition(json_encode($definition, JSON_THROW_ON_ERROR));
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'countryCode' => $this->countryCode,
			'name' => $this->name,
			'source' => $this->source,
			'definition' => $this->getDefinitionArray(),
			'isActive' => $this->isActive,
		];
	}
}
