<!--
SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="vacation-form">
		<h2>{{ t('vacation', 'New vacation request') }}</h2>

		<div class="vacation-form__field">
			<p v-if="minStartDays > 0" class="vacation-form__info">
				{{ t('vacation', 'Start date must be at least {days} days in the future', { days: minStartDays }) }}
			</p>
			<NcDateTimePickerNative
				id="start-date"
				v-model="start"
				:label="t('vacation', 'Start date')"
				:min="minStartDate"
				type="date" />
		</div>

		<div class="vacation-form__field">
			<NcDateTimePickerNative
				id="end-date"
				v-model="end"
				:label="t('vacation', 'End date')"
				:min="minEndDate"
				:max="maxEndDate"
				type="date" />
			<p v-if="!endDateValid" class="vacation-form__hint">
				<template v-if="end < start">
					{{ t('vacation', 'End date cannot be before start date') }}
				</template>
				<template v-else-if="maxEndDays > 0">
					{{ t('vacation', 'End date must be at most {days} days in the future', { days: maxEndDays }) }}
				</template>
			</p>
		</div>

		<div class="vacation-form__field">
			<NcTextField
				v-model="days"
				:label="t('vacation', 'Number of vacation days')"
				type="number" />
		</div>

		<div class="vacation-form__field">
			<label>{{ t('vacation', 'Replacement') }}</label>
			<NcSelectUsers
				v-model="replacement"
				:options="replacementOptions"
				:placeholder="t('vacation', 'Search for a replacement…')"
				@search="searchReplacement">
				<template #no-options>
					{{ t('vacation', 'Type to search for users') }}
				</template>
			</NcSelectUsers>
		</div>

		<div class="vacation-form__field">
			<label>{{ t('vacation', 'Manager') }}</label>
			<NcSelectUsers
				v-model="manager"
				:options="managerOptions"
				:placeholder="t('vacation', 'Search for your manager…')"
				@search="searchManager">
				<template #no-options>
					{{ t('vacation', 'Type to search for users') }}
				</template>
			</NcSelectUsers>
		</div>

		<div class="vacation-form__field">
			<NcTextArea
				v-model="message"
				:label="t('vacation', 'Message (optional)')"
				resize="vertical" />
		</div>

		<div class="vacation-form__field">
			<NcTextField
				v-model="signature"
				:label="t('vacation', 'Signature (type your full name)')" />
		</div>

		<div class="vacation-form__field">
			<NcCheckboxRadioSwitch
				:model-value="signed"
				type="switch"
				@update:model-value="signed = $event">
				{{ t('vacation', 'I confirm that by typing my name I have signed this vacation request') }}
			</NcCheckboxRadioSwitch>
		</div>

		<NcButton
			variant="primary"
			:disabled="!canSubmit || submitting"
			@click="submit">
			{{ submitting ? t('vacation', 'Submitting…') : t('vacation', 'Submit request') }}
		</NcButton>
	</div>
</template>

<script setup>
import { getCurrentUser } from '@nextcloud/auth'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import {
	NcButton,
	NcCheckboxRadioSwitch,
	NcDateTimePickerNative,
	NcTextArea,
	NcTextField,
	NcSelectUsers,
} from '@nextcloud/vue'
import { computed, ref, watch } from 'vue'
import { createVacation, searchUsers } from '../api.js'
import { formatDate } from '../date.js'

const props = defineProps({
	presetManager: {
		type: Object,
		default: null,
	},
	minStartDays: {
		type: Number,
		default: 0,
	},
	maxEndDays: {
		type: Number,
		default: 0,
	},
})

const emit = defineEmits(['created'])

/**
 *
 * @param date
 * @param numDays
 */
function addDays(date, numDays) {
	const d = new Date(date)
	d.setHours(0, 0, 0, 0)
	d.setDate(d.getDate() + numDays)
	return d
}

/**
 *
 */
function firstValidStart() {
	if (props.minStartDays > 0) {
		return addDays(new Date(), props.minStartDays)
	}
	const d = new Date()
	d.setHours(0, 0, 0, 0)
	return d
}

const start = ref(firstValidStart())
const end = ref(firstValidStart())
const days = ref('0')
const message = ref('')
const signature = ref('')
const signed = ref(false)
const submitting = ref(false)

/**
 *
 * @param user
 */
function toSelectOption(user) {
	return {
		id: user.id,
		displayName: user.displayName,
		user: user.id,
	}
}

const replacement = ref(null)
const manager = ref(props.presetManager ? toSelectOption(props.presetManager) : null)
const replacementOptions = ref([])
const managerOptions = ref([])

let replacementSearchTimeout = null
let managerSearchTimeout = null

// Pre-select manager when prop arrives asynchronously
watch(() => props.presetManager, (newManager) => {
	if (newManager && !manager.value) {
		manager.value = toSelectOption(newManager)
	}
})

// Update dates when minStartDays prop changes (e.g. async config load)
watch(() => props.minStartDays, () => {
	const valid = firstValidStart()
	if (start.value < valid) {
		start.value = valid
	}
	if (end.value < valid) {
		end.value = valid
	}
})

const minStartDate = computed(() => {
	if (props.minStartDays > 0) {
		return addDays(new Date(), props.minStartDays)
	}
	return null
})

const maxEndDate = computed(() => {
	if (props.maxEndDays > 0) {
		return addDays(new Date(), props.maxEndDays)
	}
	return null
})

const minEndDate = computed(() => {
	const candidates = []
	if (minStartDate.value) {
		candidates.push(minStartDate.value)
	}
	candidates.push(start.value)
	return new Date(Math.max(...candidates.map((d) => d.getTime())))
})

watch(start, (newStart) => {
	if (end.value < newStart) {
		end.value = new Date(newStart)
	}
})

const startDateValid = computed(() => {
	if (!minStartDate.value) { return true }
	return start.value >= minStartDate.value
})

const endDateValid = computed(() => {
	if (end.value < start.value) { return false }
	if (!maxEndDate.value) { return true }
	return end.value <= maxEndDate.value
})

const canSubmit = computed(() => {
	return signed.value
		&& signature.value !== ''
		&& !isNaN(parseInt(days.value))
		&& parseInt(days.value) > 0
		&& manager.value !== null
		&& startDateValid.value
		&& endDateValid.value
})

/**
 *
 * @param query
 */
async function searchReplacement(query) {
	clearTimeout(replacementSearchTimeout)
	if (query.length < 2) {
		replacementOptions.value = []
		return
	}
	replacementSearchTimeout = setTimeout(async () => {
		try {
			const users = await searchUsers(query)
			const currentUid = getCurrentUser()?.uid
			replacementOptions.value = users.filter((u) => u.id !== currentUid).map(toSelectOption)
		} catch (e) {
			console.error(e)
		}
	}, 300)
}

/**
 *
 * @param query
 */
async function searchManager(query) {
	clearTimeout(managerSearchTimeout)
	if (query.length < 2) {
		managerOptions.value = []
		return
	}
	managerSearchTimeout = setTimeout(async () => {
		try {
			const users = await searchUsers(query)
			managerOptions.value = users.map(toSelectOption)
		} catch (e) {
			console.error(e)
		}
	}, 300)
}

/**
 *
 */
async function submit() {
	submitting.value = true
	try {
		const managerUserId = manager.value?.id ?? ''
		const replacementUserId = replacement.value?.id ?? ''
		const vacation = await createVacation({
			start: formatDate(start.value),
			end: formatDate(end.value),
			dayCount: parseInt(days.value),
			signature: signature.value,
			signatureVerified: signed.value,
			replacementUserId,
			managerUserId,
			message: message.value,
		})
		showSuccess(t('vacation', 'Vacation request submitted'))

		// Reset form
		start.value = firstValidStart()
		end.value = firstValidStart()
		days.value = '0'
		message.value = ''
		signature.value = ''
		signed.value = false
		replacement.value = null
		manager.value = props.presetManager ? toSelectOption(props.presetManager) : null

		emit('created', vacation)
	} catch (e) {
		console.error(e)
		const errorMessage = e.response?.data?.ocs?.meta?.message || t('vacation', 'Could not submit vacation request')
		showError(errorMessage)
	}
	submitting.value = false
}
</script>

<style lang="scss" scoped>
.vacation-form {
	max-width: 600px;
	padding: 20px;
	padding-top: 50px;

	&__field {
		margin-bottom: 16px;

		label {
			display: block;
			margin-bottom: 4px;
			font-weight: bold;
		}
	}

	&__hint {
		color: var(--color-error-text);
		font-size: 0.9em;
		margin-top: 4px;
	}

	&__info {
		color: var(--color-text-maxcontrast);
		font-size: 0.9em;
		margin-bottom: 4px;
	}

	&__user-chip {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 8px 0;
	}
}
</style>
