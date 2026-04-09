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

class Version000002Date20260409000000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('vacation');

		if (!$table->hasColumn('replacement_user_id')) {
			$table->addColumn('replacement_user_id', Types::STRING, [
				'notnull' => false,
				'length' => 255,
				'default' => null,
			]);
		}

		if (!$table->hasColumn('manager_user_id')) {
			$table->addColumn('manager_user_id', Types::STRING, [
				'notnull' => false,
				'length' => 255,
				'default' => null,
			]);
		}

		if (!$table->hasColumn('request_date')) {
			$table->addColumn('request_date', Types::STRING, [
				'notnull' => false,
				'length' => 10,
				'default' => null,
			]);
		}

		if (!$table->hasColumn('message')) {
			$table->addColumn('message', Types::TEXT, [
				'notnull' => false,
				'default' => null,
			]);
		}

		if (!$table->hasIndex('vacation_manager_user_id_index')) {
			$table->addIndex(['manager_user_id'], 'vacation_manager_user_id_index');
		}

		return $schema;
	}
}
