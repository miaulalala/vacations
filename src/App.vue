<!--
	SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
	SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="content" class="app-vacation">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="t('vacation', 'New note')"
				:disabled="false"
				button-id="new-vacation-button"
				button-class="icon-add"
				@click="newNote" />
			<ul>
				<AppNavigationItem v-for="note in notes"
					:key="note.id"
					:title="note.title ? note.title : t('vacation', 'New note')"
					:class="{active: currentNoteId === note.id}"
					@click="openNote(note)">
					<template slot="actions">
						<ActionButton v-if="note.id === -1"
							icon="icon-close"
							@click="cancelNewNote(note)">
							{{
								t('vacation', 'Cancel note creation') }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteNote(note)">
							{{
								t('vacation', 'Delete note') }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent>
			<form>
				<NcDatetimePicker v-model="start"
					type="date" />
				<NcDatetimePicker v-model="end"
					type="date" />
				<NcTextField :value.sync="days" label="Number of days" />
				<NcTextField :value.sync="signature" label="Signature" />
				<NcCheckboxRadioSwitch :checked.sync="signatureAccepted" />
				<span>Signature accepted</span>
				<NcButton @click="createVacation" />
			</form>
		</AppContent>
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
		NcDatetimePicker,
		NcCheckboxRadioSwitch,
		NcTextField,
		NcButton,
	},
	data() {
		return {
			notes: [],
			currentNoteId: null,
			updating: false,
			loading: true,

			start: new Date(),
			end: null,
			days: '0',
			signature: '',
			signatureAccepted: false,
		}
	},
	computed: {
		/**
		 * Return the currently selected note object
		 *
		 * @return {object | null}
		 */
		currentNote() {
			if (this.currentNoteId === null) {
				return null
			}
			return this.notes.find((note) => note.id === this.currentNoteId)
		},

		/**
		 * Returns true if a note is selected and its title is not empty
		 *
		 * @return {boolean}
		 */
		savePossible() {
			return this.currentNote && this.currentNote.title !== ''
		},
	},
	/**
	 * Fetch list of notes when the component is loaded
	 */
	async mounted() {
		try {
			const response = await axios.get(generateUrl('/apps/vacation/notes'))
			this.notes = response.data
		} catch (e) {
			console.error(e)
			showError(t('notestutorial', 'Could not fetch notes'))
		}
		this.loading = false
	},

	methods: {
		async createVacation() {
			const url = generateOcsUrl('/apps/vacation/api/v1/vacation')
			await axios.post(url, {
				start: formatDate(this.start),
				end: formatDate(this.end),
				dayCount: parseInt(this.days),
				signature: this.signature,
				signatureVerified: this.signatureAccepted,
			})
		},
		/**
		 * Create a new note and focus the note content field automatically
		 *
		 * @param {object} note Note object
		 */
		openNote(note) {
			if (this.updating) {
				return
			}
			this.currentNoteId = note.id
			this.$nextTick(() => {
				this.$refs.content.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new note or save
		 */
		saveNote() {
			if (this.currentNoteId === -1) {
				this.createNote(this.currentNote)
			} else {
				this.updateNote(this.currentNote)
			}
		},
		/**
		 * Create a new note and focus the note content field automatically
		 * The note is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newNote() {
			if (this.currentNoteId !== -1) {
				this.currentNoteId = -1
				this.notes.push({
					id: -1,
					title: '',
					content: '',
				})
				this.$nextTick(() => {
					this.$refs.title.focus()
				})
			}
		},
		/**
		 * Abort creating a new note
		 */
		cancelNewNote() {
			this.notes.splice(this.notes.findIndex((note) => note.id === -1), 1)
			this.currentNoteId = null
		},
		/**
		 * Create a new note by sending the information to the server
		 *
		 * @param {object} note Note object
		 */
		async createNote(note) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/vacation/notes'), note)
				const index = this.notes.findIndex((match) => match.id === this.currentNoteId)
				this.$set(this.notes, index, response.data)
				this.currentNoteId = response.data.id
			} catch (e) {
				console.error(e)
				showError(t('notestutorial', 'Could not create the note'))
			}
			this.updating = false
		},
		/**
		 * Update an existing note on the server
		 *
		 * @param {object} note Note object
		 */
		async updateNote(note) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/vacation/notes/${note.id}`), note)
			} catch (e) {
				console.error(e)
				showError(t('notestutorial', 'Could not update the note'))
			}
			this.updating = false
		},
		/**
		 * Delete a note, remove it from the frontend and show a hint
		 *
		 * @param {object} note Note object
		 */
		async deleteNote(note) {
			try {
				await axios.delete(generateUrl(`/apps/vacation/notes/${note.id}`))
				this.notes.splice(this.notes.indexOf(note), 1)
				if (this.currentNoteId === note.id) {
					this.currentNoteId = null
				}
				showSuccess(t('vacation', 'Note deleted'))
			} catch (e) {
				console.error(e)
				showError(t('vacation', 'Could not delete the note'))
			}
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
</stylel>
