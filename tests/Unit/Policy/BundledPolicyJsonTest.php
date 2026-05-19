<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Tests\Unit\Policy;

use PHPUnit\Framework\TestCase;

class BundledPolicyJsonTest extends TestCase {

	private const POLICIES_DIR = __DIR__ . '/../../../policies';

	private const VALID_ACCRUAL_POLICIES = ['lump_sum', 'monthly', 'custom_interval'];

	/** @return array<string, array{string}> */
	public static function policyFileProvider(): array {
		$files = glob(self::POLICIES_DIR . '/*.json') ?: [];
		$cases = [];
		foreach ($files as $file) {
			$cases[basename($file)] = [$file];
		}
		return $cases;
	}

	public function testPoliciesDirExists(): void {
		$this->assertDirectoryExists(self::POLICIES_DIR, 'policies/ directory must exist');
	}

	public function testAtLeastOnePolicyFileExists(): void {
		$files = glob(self::POLICIES_DIR . '/*.json') ?: [];
		$this->assertGreaterThan(0, count($files), 'At least one policy JSON file must exist');
	}

	/** @dataProvider policyFileProvider */
	public function testFileIsValidJson(string $path): void {
		$contents = file_get_contents($path);
		$this->assertNotFalse($contents, "Could not read $path");

		$decoded = json_decode($contents, true);
		$this->assertSame(JSON_ERROR_NONE, json_last_error(), "Invalid JSON in $path: " . json_last_error_msg());
		$this->assertIsArray($decoded);
	}

	/** @dataProvider policyFileProvider */
	public function testTopLevelRequiredFields(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		$this->assertArrayHasKey('country', $data, "Missing 'country' in $path");
		$this->assertArrayHasKey('name', $data, "Missing 'name' in $path");
		$this->assertArrayHasKey('source', $data, "Missing 'source' in $path");
		$this->assertArrayHasKey('leaveTypes', $data, "Missing 'leaveTypes' in $path");
	}

	/** @dataProvider policyFileProvider */
	public function testCountryCodeMatchesFilename(string $path): void {
		$data = json_decode(file_get_contents($path), true);
		$expectedCode = strtoupper(basename($path, '.json'));

		$this->assertSame(
			$expectedCode,
			$data['country'],
			"Country code '{$data['country']}' does not match filename in $path"
		);
	}

	/** @dataProvider policyFileProvider */
	public function testSourceIsBundled(string $path): void {
		$data = json_decode(file_get_contents($path), true);
		$this->assertSame('bundled', $data['source'], "source must be 'bundled' in $path");
	}

	/** @dataProvider policyFileProvider */
	public function testLeaveTypesIsNonEmptyArray(string $path): void {
		$data = json_decode(file_get_contents($path), true);
		$this->assertIsArray($data['leaveTypes'], "leaveTypes must be an array in $path");
		$this->assertNotEmpty($data['leaveTypes'], "leaveTypes must not be empty in $path");
	}

	/** @dataProvider policyFileProvider */
	public function testEachLeaveTypeHasRequiredFields(string $path): void {
		$data = json_decode(file_get_contents($path), true);
		$required = [
			'entitlementDays',
			'accrualPolicy',
			'accrualInterval',
			'accrualDaysPerInterval',
			'workingYearStart',
			'carryoverDays',
			'carryoverExpiryDays',
			'allowNegative',
		];

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			foreach ($required as $field) {
				$this->assertArrayHasKey(
					$field,
					$leaveType,
					"Leave type '$code' in $path is missing field '$field'"
				);
			}
		}
	}

	/** @dataProvider policyFileProvider */
	public function testAccrualPolicyIsValidValue(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			$this->assertContains(
				$leaveType['accrualPolicy'],
				self::VALID_ACCRUAL_POLICIES,
				"Leave type '$code' in $path has invalid accrualPolicy '{$leaveType['accrualPolicy']}'"
			);
		}
	}

	/** @dataProvider policyFileProvider */
	public function testCustomIntervalPoliciesHaveInterval(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			if ($leaveType['accrualPolicy'] === 'custom_interval') {
				$this->assertNotNull(
					$leaveType['accrualInterval'],
					"Leave type '$code' in $path uses custom_interval but accrualInterval is null"
				);
				$this->assertGreaterThan(
					0,
					$leaveType['accrualInterval'],
					"Leave type '$code' in $path has non-positive accrualInterval"
				);
			}
		}
	}

	/** @dataProvider policyFileProvider */
	public function testEntitlementDaysIsNonNegative(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			$this->assertGreaterThanOrEqual(
				0.0,
				$leaveType['entitlementDays'],
				"Leave type '$code' in $path has negative entitlementDays"
			);
		}
	}

	/** @dataProvider policyFileProvider */
	public function testCarryoverDaysIsNonNegative(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			$this->assertGreaterThanOrEqual(
				0.0,
				$leaveType['carryoverDays'],
				"Leave type '$code' in $path has negative carryoverDays"
			);
		}
	}

	/** @dataProvider policyFileProvider */
	public function testWorkingYearStartFormat(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			$value = $leaveType['workingYearStart'];
			$isAnniversary = $value === 'anniversary';
			$isMonthDay = (bool)preg_match('/^\d{2}-\d{2}$/', $value);

			$this->assertTrue(
				$isAnniversary || $isMonthDay,
				"Leave type '$code' in $path has invalid workingYearStart '$value' (expected 'anniversary' or 'mm-dd')"
			);
		}
	}

	/** @dataProvider policyFileProvider */
	public function testAllowNegativeIsBoolean(string $path): void {
		$data = json_decode(file_get_contents($path), true);

		foreach ($data['leaveTypes'] as $code => $leaveType) {
			$this->assertIsBool(
				$leaveType['allowNegative'],
				"Leave type '$code' in $path: allowNegative must be a boolean"
			);
		}
	}
}
