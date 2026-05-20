<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="vacation-list">
		<p v-if="vacations.length === 0" class="vacation-list__empty">
			{{ t('vacation', 'No vacation requests yet.') }}
		</p>

		<table v-else class="vacation-list__table">
			<thead>
				<tr>
					<th>{{ t('vacation', 'Start') }}</th>
					<th>{{ t('vacation', 'End') }}</th>
					<th>{{ t('vacation', 'Days') }}</th>
					<th>{{ t('vacation', 'Status') }}</th>
					<th>{{ t('vacation', 'Manager') }}</th>
					<th>{{ t('vacation', 'Message') }}</th>
					<th>{{ t('vacation', 'Reply') }}</th>
					<th>{{ t('vacation', 'Actions') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="vacation in vacations" :key="vacation.id">
					<td>{{ vacation.start }}</td>
					<td>{{ vacation.end }}</td>
					<td>{{ vacation.dayCount }}</td>
					<td>
						<span :class="statusClass(vacation.status)">
							{{ statusLabel(vacation.status) }}
						</span>
					</td>
					<td>{{ vacation.managerUserId }}</td>
					<td>{{ vacation.message }}</td>
					<td>{{ vacation.statusMessage }}</td>
					<td>
						<NcButton
							v-if="vacation.status === VACATION_PENDING"
							variant="tertiary-no-background"
							@click="$emit('delete', vacation.id)">
							{{ t('vacation', 'Delete') }}
						</NcButton>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
import { t } from '@nextcloud/l10n'
import { NcButton } from '@nextcloud/vue'
import { VACATION_ACCEPTED, VACATION_DECLINED, VACATION_PENDING } from '../constants.js'

defineProps({
	vacations: {
		type: Array,
		default: () => [],
	},
})

defineEmits(['delete'])

/**
 *
 * @param status
 */
function statusLabel(status) {
	switch (status) {
		case VACATION_PENDING:
			return t('vacation', 'Pending')
		case VACATION_ACCEPTED:
			return t('vacation', 'Approved')
		case VACATION_DECLINED:
			return t('vacation', 'Declined')
		default:
			return t('vacation', 'Unknown')
	}
}

/**
 *
 * @param status
 */
function statusClass(status) {
	switch (status) {
		case VACATION_PENDING:
			return 'status--pending'
		case VACATION_ACCEPTED:
			return 'status--approved'
		case VACATION_DECLINED:
			return 'status--declined'
		default:
			return ''
	}
}
</script>

<style lang="scss" scoped>
.vacation-list {
	padding: 20px;
	padding-top: 50px;

	&__empty {
		color: var(--color-text-maxcontrast);
		padding: 20px 0;
	}

	&__table {
		width: 100%;
		border-collapse: collapse;

		th,
		td {
			padding: 8px 12px;
			text-align: start;
			border-bottom: 1px solid var(--color-border);
		}

		th {
			font-weight: bold;
			color: var(--color-text-maxcontrast);
		}
	}
}

.status {
	&--pending {
		color: var(--color-warning-text);
		font-weight: bold;
	}

	&--approved {
		color: var(--color-success-text);
		font-weight: bold;
	}

	&--declined {
		color: var(--color-error-text);
		font-weight: bold;
	}
}
</style>
