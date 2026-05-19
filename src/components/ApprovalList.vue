<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="approval-list">
		<p v-if="vacations.length === 0" class="approval-list__empty">
			{{ t('vacation', 'No pending requests to review.') }}
		</p>

		<div v-for="vacation in vacations" :key="vacation.id" class="approval-card">
			<div class="approval-card__header">
				<strong>{{ vacation.userId }}</strong>
			</div>
			<div class="approval-card__details">
				<span>{{ t('vacation', 'From {start} to {end}', { start: vacation.start, end: vacation.end }) }}</span>
				<span>{{ t('vacation', '{count} days', { count: vacation.dayCount }) }}</span>
				<span v-if="vacation.replacementUserId">
					{{ t('vacation', 'Replacement: {user}', { user: vacation.replacementUserId }) }}
				</span>
				<span>{{ t('vacation', 'Signature: {signature}', { signature: vacation.signature }) }}</span>
				<span>{{ t('vacation', 'Requested: {date}', { date: vacation.requestDate }) }}</span>
				<span v-if="vacation.message">
					{{ t('vacation', 'Message: {message}', { message: vacation.message }) }}
				</span>
			</div>

			<div v-if="actionId === vacation.id" class="approval-card__message">
				<NcTextField
					v-model="statusMessage"
					:label="t('vacation', 'Message (optional)')" />
				<div class="approval-card__message-actions">
					<NcButton :variant="actionType === 'approve' ? 'success' : 'error'" @click="confirmAction(vacation.id)">
						{{ actionType === 'approve' ? t('vacation', 'Approve') : t('vacation', 'Decline') }}
					</NcButton>
					<NcButton variant="tertiary" @click="cancelAction">
						{{ t('vacation', 'Cancel') }}
					</NcButton>
				</div>
			</div>

			<div v-else class="approval-card__actions">
				<NcButton variant="success" @click="startApprove(vacation.id)">
					{{ t('vacation', 'Approve') }}
				</NcButton>
				<NcButton variant="error" @click="startDecline(vacation.id)">
					{{ t('vacation', 'Decline') }}
				</NcButton>
			</div>
		</div>
	</div>
</template>

<script setup>
import { showError, showSuccess, showWarning } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { NcButton, NcTextField } from '@nextcloud/vue'
import { ref } from 'vue'
import { approveVacation, declineVacation } from '../api.js'

const warningMessages = {
	notification: t('vacation', 'The requester could not be notified'),
	calendar: t('vacation', 'The calendar event could not be created'),
	absence: t('vacation', 'The out-of-office status could not be set'),
}

defineProps({
	vacations: {
		type: Array,
		default: () => [],
	},
})

const emit = defineEmits(['updated'])

const actionId = ref(null)
const actionType = ref(null)
const statusMessage = ref('')

/**
 *
 * @param id
 */
function startApprove(id) {
	actionId.value = id
	actionType.value = 'approve'
	statusMessage.value = ''
}

/**
 *
 * @param id
 */
function startDecline(id) {
	actionId.value = id
	actionType.value = 'decline'
	statusMessage.value = ''
}

/**
 *
 */
function cancelAction() {
	actionId.value = null
	actionType.value = null
	statusMessage.value = ''
}

/**
 *
 * @param id
 */
async function confirmAction(id) {
	try {
		const result = actionType.value === 'approve'
			? await approveVacation(id, statusMessage.value)
			: await declineVacation(id, statusMessage.value)
		const successMessage = actionType.value === 'approve'
			? t('vacation', 'Vacation request approved')
			: t('vacation', 'Vacation request declined')
		showSuccess(successMessage)

		for (const key of result?.warnings ?? []) {
			showWarning(warningMessages[key] ?? key)
		}

		cancelAction()
		emit('updated')
	} catch (e) {
		console.error(e)
		showError(t('vacation', 'Could not process the request'))
	}
}
</script>

<style lang="scss" scoped>
.approval-list {
	padding: 20px;
	padding-top: 50px;

	&__empty {
		color: var(--color-text-maxcontrast);
		padding: 20px 0;
	}
}

.approval-card {
	border: 1px solid var(--color-border);
	border-radius: var(--border-radius-large);
	padding: 16px;
	margin-bottom: 12px;

	&__header {
		font-size: 1.1em;
		margin-bottom: 8px;
	}

	&__details {
		display: flex;
		flex-direction: column;
		gap: 4px;
		margin-bottom: 12px;
		color: var(--color-text-maxcontrast);
	}

	&__actions {
		display: flex;
		gap: 8px;
	}

	&__message {
		margin-top: 8px;
	}

	&__message-actions {
		display: flex;
		gap: 8px;
		margin-top: 8px;
	}
}
</style>
