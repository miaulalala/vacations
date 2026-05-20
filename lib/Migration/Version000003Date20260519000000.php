<?php

declare(strict_types=1);

// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000003Date20260519000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Leave type code on existing vacation requests
		$vacation = $schema->getTable('vacation');
		if (!$vacation->hasColumn('leave_type_code')) {
			$vacation->addColumn('leave_type_code', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'VACATION',
			]);
		}

		// Leave type definitions (per organisation)
		if (!$schema->hasTable('vac_leave_types')) {
			$table = $schema->createTable('vac_leave_types');
			$table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
			$table->addColumn('code', Types::STRING, ['notnull' => true, 'length' => 32]);
			$table->addColumn('name', Types::STRING, ['notnull' => true, 'length' => 255]);
			$table->addColumn('description', Types::TEXT, ['notnull' => false, 'default' => null]);
			$table->addColumn('counts_against_balance', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('requires_approval', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('allow_negative', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
			$table->addColumn('is_active', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->addColumn('is_system', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['code'], 'vac_leave_types_code_idx');
		}

		// Custom policy overrides (bundled country policies live in JSON files)
		if (!$schema->hasTable('vac_policies')) {
			$table = $schema->createTable('vac_policies');
			$table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
			$table->addColumn('country_code', Types::STRING, ['notnull' => true, 'length' => 2]);
			$table->addColumn('name', Types::STRING, ['notnull' => true, 'length' => 255]);
			// 'bundled' | 'custom'
			$table->addColumn('source', Types::STRING, ['notnull' => true, 'length' => 16, 'default' => 'custom']);
			$table->addColumn('definition', Types::TEXT, ['notnull' => true]);
			$table->addColumn('is_active', Types::BOOLEAN, ['notnull' => true, 'default' => true]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['country_code'], 'vac_policies_country_idx');
		}

		// Per-user settings: hire date, policy override
		if (!$schema->hasTable('vac_user_settings')) {
			$table = $schema->createTable('vac_user_settings');
			$table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
			$table->addColumn('user_id', Types::STRING, ['notnull' => true, 'length' => 64]);
			// YYYY-MM-DD
			$table->addColumn('hire_date', Types::STRING, ['notnull' => false, 'length' => 10, 'default' => null]);
			// 'backend' | 'account' | 'manual'
			$table->addColumn('hire_date_source', Types::STRING, ['notnull' => false, 'length' => 16, 'default' => null]);
			// ISO 3166-1 alpha-2 country code override; null = use org default
			$table->addColumn('policy_override', Types::STRING, ['notnull' => false, 'length' => 2, 'default' => null]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id'], 'vac_user_settings_uid_idx');
		}

		// Per-user entitlement balance per leave year and leave type
		if (!$schema->hasTable('vac_entitlements')) {
			$table = $schema->createTable('vac_entitlements');
			$table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
			$table->addColumn('user_id', Types::STRING, ['notnull' => true, 'length' => 64]);
			$table->addColumn('leave_type_code', Types::STRING, ['notnull' => true, 'length' => 32]);
			// Start / end of the leave year this record covers (YYYY-MM-DD)
			$table->addColumn('year_start', Types::STRING, ['notnull' => true, 'length' => 10]);
			$table->addColumn('year_end', Types::STRING, ['notnull' => true, 'length' => 10]);
			// Allocated days for this period (from policy)
			$table->addColumn('total_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// Days accrued so far (for progressive policies; equals total_days for lump-sum)
			$table->addColumn('accrued_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// Days carried over from the previous year
			$table->addColumn('carried_over_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// Manual HR adjustments (signed)
			$table->addColumn('adjustment_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// Days deducted by approved requests
			$table->addColumn('taken_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// Days locked by pending (not yet approved) requests
			$table->addColumn('pending_days', Types::FLOAT, ['notnull' => true, 'default' => 0]);
			// When carried-over days from the previous year expire (YYYY-MM-DD, nullable)
			$table->addColumn('carryover_expiry_date', Types::STRING, ['notnull' => false, 'length' => 10, 'default' => null]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id', 'leave_type_code', 'year_start'], 'vac_entitlements_unique_idx');
			$table->addIndex(['user_id'], 'vac_entitlements_uid_idx');
			$table->addIndex(['carryover_expiry_date'], 'vac_entitlements_expiry_idx');
		}

		// Immutable audit log of every balance change
		if (!$schema->hasTable('vac_entitlement_tx')) {
			$table = $schema->createTable('vac_entitlement_tx');
			$table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
			$table->addColumn('user_id', Types::STRING, ['notnull' => true, 'length' => 64]);
			$table->addColumn('entitlement_id', Types::INTEGER, ['notnull' => true, 'unsigned' => true]);
			// accrual | deduction | adjustment | carryover | expiry | reversal
			$table->addColumn('type', Types::STRING, ['notnull' => true, 'length' => 32]);
			// Signed: positive = credit, negative = debit
			$table->addColumn('days', Types::FLOAT, ['notnull' => true]);
			$table->addColumn('date', Types::STRING, ['notnull' => true, 'length' => 10]);
			// Linked vacation request, if any
			$table->addColumn('vacation_id', Types::INTEGER, ['notnull' => false, 'default' => null]);
			// Who performed the action (null = system)
			$table->addColumn('actor_user_id', Types::STRING, ['notnull' => false, 'length' => 64, 'default' => null]);
			$table->addColumn('note', Types::TEXT, ['notnull' => false, 'default' => null]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'vac_entitlement_tx_uid_idx');
			$table->addIndex(['entitlement_id'], 'vac_entitlement_tx_eid_idx');
			$table->addIndex(['vacation_id'], 'vac_entitlement_tx_vid_idx');
		}

		return $schema;
	}
}
