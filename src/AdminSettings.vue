<!--
	SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
	SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="content" class="app-vacation">
		<h1>It works!</h1>
		<NcTextField :value.sync="email" label="Fallback email address" />
	</div>
</template>

<script>
import {
	NcActionButton as ActionButton,
	NcAppContent as AppContent,
	NcAppNavigation as AppNavigation,
	NcAppNavigationItem as AppNavigationItem,
	NcAppNavigationNew as AppNavigationNew,
	NcTextField,
	NcDatetimePicker,
	NcDateTimePickerNative,
	NcCheckboxRadioSwitch,
	NcButton,
} from '@nextcloud/vue'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { formatDate } from './date.js'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		NcDateTimePickerNative,
		NcCheckboxRadioSwitch,
		NcTextField,
		NcButton,
	},
	data() {
		return {
			email: '',
		}
	},
	computed: {
	},
	async mounted() {
	},

	methods: {
		async createVacation() {
			const url = generateOcsUrl('/apps/vacation/api/v1/admin-settings')
			await axios.post(url, {
				email: this.email,
			})
		},
	},
}
</script>

<style scoped>
	#app-content > div {
		width: 100%;
		height: 100%;
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
