<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<NcContent app-name="vacation">
		<NcAppNavigation>
			<NcAppNavigationItem
				class="navigation-item"
				:name="t('vacation', 'New Request')"
				:active="activeTab === 'new'"
				@click="activeTab = 'new'">
				<template #icon>
					<NcIconSvgWrapper :path="mdiUmbrellaBeachOutline" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				class="navigation-item"
				:name="t('vacation', 'My Requests')"
				:active="activeTab === 'requests'"
				@click="activeTab = 'requests'">
				<template #icon>
					<NcIconSvgWrapper :path="mdiFormatListBulleted" />
				</template>
			</NcAppNavigationItem>
			<NcAppNavigationItem
				v-if="pendingApprovals.length > 0"
				class="navigation-item"
				:name="t('vacation', 'Pending Approvals')"
				:active="activeTab === 'approvals'"
				@click="activeTab = 'approvals'">
				<template #icon>
					<NcIconSvgWrapper :path="mdiClipboardCheckOutline" />
				</template>
				<template #counter>
					<NcCounterBubble :count="pendingApprovals.length" />
				</template>
			</NcAppNavigationItem>
		</NcAppNavigation>

		<NcAppContent>
			<div v-if="activeTab === 'new'" class="vacation-tab">
				<VacationForm
					v-if="managerLoaded"
					:preset-manager="currentManager"
					:min-start-days="appConfig.minStartDays"
					:max-end-days="appConfig.maxEndDays"
					@created="onVacationCreated" />
			</div>

			<div v-if="activeTab === 'requests'" class="vacation-tab">
				<VacationList
					:vacations="myVacations"
					@delete="onDeleteVacation" />
			</div>

			<div v-if="activeTab === 'approvals'" class="vacation-tab">
				<ApprovalList
					:vacations="pendingApprovals"
					@updated="loadPendingApprovals" />
			</div>
		</NcAppContent>
	</NcContent>
</template>

<script setup>
import { mdiClipboardCheckOutline, mdiFormatListBulleted, mdiUmbrellaBeachOutline } from '@mdi/js'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import {
	NcAppContent,
	NcAppNavigation,
	NcAppNavigationItem,
	NcContent,
	NcCounterBubble,
} from '@nextcloud/vue'
import { onMounted, ref } from 'vue'
import NcIconSvgWrapper from '@nextcloud/vue/components/NcIconSvgWrapper'
import ApprovalList from './components/ApprovalList.vue'
import VacationForm from './components/VacationForm.vue'
import VacationList from './components/VacationList.vue'
import {
	deleteVacation,
	fetchConfig,
	fetchCurrentUserManager,
	fetchMyVacations,
	fetchPendingApprovals as fetchPending,
} from './api.js'

const activeTab = ref('new')
const myVacations = ref([])
const pendingApprovals = ref([])
const currentManager = ref(null)
const managerLoaded = ref(false)
const appConfig = ref({ minStartDays: 0, maxEndDays: 0 })

/**
 *
 */
async function loadMyVacations() {
	try {
		myVacations.value = await fetchMyVacations()
	} catch (e) {
		console.error(e)
		showError(t('vacation', 'Could not load vacation requests'))
	}
}

/**
 *
 */
async function loadPendingApprovals() {
	try {
		pendingApprovals.value = await fetchPending()
	} catch (e) {
		console.error(e)
	}
}

/**
 *
 */
async function loadCurrentManager() {
	try {
		currentManager.value = await fetchCurrentUserManager()
	} catch (e) {
		console.error(e)
	}
	managerLoaded.value = true
}

/**
 *
 */
async function loadConfig() {
	try {
		appConfig.value = await fetchConfig()
	} catch (e) {
		console.error(e)
	}
}

/**
 *
 */
async function onVacationCreated() {
	await loadMyVacations()
}

/**
 *
 * @param id
 */
async function onDeleteVacation(id) {
	try {
		await deleteVacation(id)
		showSuccess(t('vacation', 'Vacation request deleted'))
		await loadMyVacations()
	} catch (e) {
		console.error(e)
		showError(t('vacation', 'Could not delete vacation request'))
	}
}

onMounted(async () => {
	await Promise.all([
		loadMyVacations(),
		loadPendingApprovals(),
		loadCurrentManager(),
		loadConfig(),
	])
})
</script>

<style scoped>
.vacation-tab {
	height: 100%;
	overflow-y: auto;
}

.navigation-item {
	padding-inline: calc(var(--default-grid-baseline) * 2);
	margin-block: var(--default-grid-baseline);
}

.navigation-item :deep(.app-navigation-entry-link) {
	padding-inline-start: var(--default-grid-baseline);
}

.navigation-item :deep(.app-navigation-entry__name) {
	padding-inline-start: calc(2 * var(--default-grid-baseline));
	font-weight: 500;
}
</style>
