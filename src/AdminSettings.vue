<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="vacation-admin-settings">
		<NcSettingsSection
			:name="t('vacation', 'Notifications')"
			:description="t('vacation', 'Email address used for vacation notifications when a user has no email configured.')">
			<NcTextField
				v-model="email"
				class="vacation-admin-settings__email"
				:label="t('vacation', 'Fallback email address')" />
			<div class="vacation-admin-settings__section-actions">
				<NcButton
					variant="primary"
					:disabled="savingEmail"
					@click="saveEmail">
					{{ savingEmail ? t('vacation', 'Saving…') : t('vacation', 'Save') }}
				</NcButton>
			</div>
		</NcSettingsSection>

		<NcSettingsSection
			:name="t('vacation', 'Date restrictions')"
			:description="t('vacation', 'Limit the date range for vacation requests. Set the minimum number of days before a request can start and the maximum number of days into the future a request can end. Use 0 for no limit.')">
			<div class="vacation-admin-settings__field">
				<NcTextField
					v-model="minStartDays"
					:label="t('vacation', 'Minimum lead time (days)')"
					type="number" />
			</div>

			<div class="vacation-admin-settings__field">
				<NcTextField
					v-model="maxEndDays"
					:label="t('vacation', 'Maximum end date (days)')"
					type="number" />
			</div>

			<div class="vacation-admin-settings__section-actions">
				<NcButton
					variant="primary"
					:disabled="savingDates"
					@click="saveDates">
					{{ savingDates ? t('vacation', 'Saving…') : t('vacation', 'Save') }}
				</NcButton>
			</div>
		</NcSettingsSection>
	</div>
</template>

<script setup>
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { NcButton, NcTextField } from '@nextcloud/vue'
import { ref } from 'vue'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'

const email = ref(loadState('vacation', 'vacation_email', ''))
const minStartDays = ref(String(loadState('vacation', 'min_start_days', 0)))
const maxEndDays = ref(String(loadState('vacation', 'max_end_days', 0)))
const savingEmail = ref(false)
const savingDates = ref(false)

/**
 *
 */
async function saveEmail() {
	savingEmail.value = true
	try {
		await axios.post(generateUrl('/apps/vacation/api/v1/admin-settings'), {
			email: email.value,
			minStartDays: parseInt(minStartDays.value) || 0,
			maxEndDays: parseInt(maxEndDays.value) || 0,
		})
		showSuccess(t('vacation', 'Settings saved'))
	} catch (e) {
		console.error(e)
		showError(t('vacation', 'Could not save settings'))
	}
	savingEmail.value = false
}

/**
 *
 */
async function saveDates() {
	savingDates.value = true
	try {
		await axios.post(generateUrl('/apps/vacation/api/v1/admin-settings'), {
			email: email.value,
			minStartDays: parseInt(minStartDays.value) || 0,
			maxEndDays: parseInt(maxEndDays.value) || 0,
		})
		showSuccess(t('vacation', 'Settings saved'))
	} catch (e) {
		console.error(e)
		showError(t('vacation', 'Could not save settings'))
	}
	savingDates.value = false
}
</script>

<style>
.vacation-admin-settings .settings-section:first-child {
	margin-top: 50px !important;
}

.vacation-admin-settings .input-field {
	max-width: 200px !important;
}

.vacation-admin-settings .vacation-admin-settings__email.input-field {
	max-width: 400px !important;
}

.vacation-admin-settings .vacation-admin-settings__field {
	margin-top: 16px;
}

.vacation-admin-settings .vacation-admin-settings__section-actions {
	margin-top: 16px;
}
</style>
