<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Vacation\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20181013124731 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('vacation')) {
			$table = $schema->createTable('vacation');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('start', Types::STRING, [
				'notnull' => true,
				'length' => 10,
			]);
			$table->addColumn('end', Types::STRING, [
				'notnull' => true,
				'length' => 10,
			]);
			$table->addColumn('day_count', TYPES::INTEGER, [
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('signature', TYPES::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('signature_verified', Types::BOOLEAN, [
				'notnull' => false,
				'default' => false,
			]);
			$table->addColumn('signoff_user_id', Types::STRING, [
				'notnull' => false,
				'length' => 255,
				'default' => null,
			]);
			$table->addColumn('status', Types::SMALLINT, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('status_message', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('token', Types::STRING, [
				'notnull' => false,
				'length' => 64
			]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'vacation_user_id_index');
		}
		return $schema;
	}
}
