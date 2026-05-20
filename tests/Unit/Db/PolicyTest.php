<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Tests\Unit\Db;

use OCA\Vacation\Db\Policy;
use PHPUnit\Framework\TestCase;

class PolicyTest extends TestCase {

	private function makeSampleDefinition(): array {
		return [
			'leaveTypes' => [
				'VACATION' => [
					'entitlementDays' => 25.0,
					'accrualPolicy' => 'monthly',
					'accrualInterval' => null,
					'accrualDaysPerInterval' => 2.0833,
					'workingYearStart' => '01-01',
					'carryoverDays' => 5.0,
					'carryoverExpiryDays' => 90,
					'allowNegative' => false,
				],
			],
		];
	}

	public function testSetAndGetDefinitionArray(): void {
		$policy = new Policy();
		$policy->setDefinitionArray($this->makeSampleDefinition());

		$result = $policy->getDefinitionArray();

		$this->assertSame(25.0, $result['leaveTypes']['VACATION']['entitlementDays']);
		$this->assertSame('monthly', $result['leaveTypes']['VACATION']['accrualPolicy']);
		$this->assertSame(90, $result['leaveTypes']['VACATION']['carryoverExpiryDays']);
	}

	public function testDefinitionRoundTrip(): void {
		$policy = new Policy();
		$definition = $this->makeSampleDefinition();
		$policy->setDefinitionArray($definition);

		$this->assertSame($definition, $policy->getDefinitionArray());
	}

	public function testEmptyDefinitionReturnsEmptyArray(): void {
		$policy = new Policy();
		$policy->setDefinition('{}');

		$this->assertSame([], $policy->getDefinitionArray());
	}

	public function testInvalidJsonReturnsEmptyArray(): void {
		$policy = new Policy();
		$policy->setDefinition('not-valid-json');

		$this->assertSame([], $policy->getDefinitionArray());
	}

	public function testJsonSerializeExpandsDefinition(): void {
		$policy = new Policy();
		$policy->setCountryCode('FR');
		$policy->setName('France');
		$policy->setSource(Policy::SOURCE_BUNDLED);
		$policy->setIsActive(true);
		$policy->setDefinitionArray($this->makeSampleDefinition());

		$json = $policy->jsonSerialize();

		$this->assertSame('FR', $json['countryCode']);
		$this->assertSame('France', $json['name']);
		$this->assertSame(Policy::SOURCE_BUNDLED, $json['source']);
		$this->assertTrue($json['isActive']);
		// definition is expanded, not a raw string
		$this->assertIsArray($json['definition']);
		$this->assertArrayHasKey('leaveTypes', $json['definition']);
	}

	public function testSourceConstants(): void {
		$this->assertSame('bundled', Policy::SOURCE_BUNDLED);
		$this->assertSame('custom', Policy::SOURCE_CUSTOM);
	}
}
