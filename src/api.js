// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import axios from '@nextcloud/axios'
import { generateOcsUrl } from '@nextcloud/router'

const baseUrl = '/apps/vacation/api/v1'

/**
 *
 */
export async function fetchMyVacations() {
	const response = await axios.get(generateOcsUrl(baseUrl + '/vacation'))
	return response.data.ocs.data
}

/**
 *
 * @param data
 */
export async function createVacation(data) {
	const response = await axios.post(generateOcsUrl(baseUrl + '/vacation'), data)
	return response.data.ocs.data
}

/**
 *
 * @param id
 * @param data
 */
export async function updateVacation(id, data) {
	const response = await axios.put(generateOcsUrl(baseUrl + '/vacation/{id}', { id }), data)
	return response.data.ocs.data
}

/**
 *
 * @param id
 */
export async function deleteVacation(id) {
	await axios.delete(generateOcsUrl(baseUrl + '/vacation/{id}', { id }))
}

/**
 *
 */
export async function fetchPendingApprovals() {
	const response = await axios.get(generateOcsUrl(baseUrl + '/vacation/pending'))
	return response.data.ocs.data
}

/**
 *
 * @param id
 * @param statusMessage
 */
export async function approveVacation(id, statusMessage = '') {
	const response = await axios.post(generateOcsUrl(baseUrl + '/vacation/{id}/approve', { id }), { statusMessage })
	return response.data.ocs.data
}

/**
 *
 * @param id
 * @param statusMessage
 */
export async function declineVacation(id, statusMessage = '') {
	const response = await axios.post(generateOcsUrl(baseUrl + '/vacation/{id}/decline', { id }), { statusMessage })
	return response.data.ocs.data
}

/**
 *
 * @param search
 */
export async function searchUsers(search) {
	const response = await axios.get(generateOcsUrl(baseUrl + '/users'), { params: { search } })
	return response.data.ocs.data
}

/**
 *
 */
export async function fetchCurrentUserManager() {
	const response = await axios.get(generateOcsUrl(baseUrl + '/manager'))
	return response.data.ocs.data
}

/**
 *
 */
export async function fetchConfig() {
	const response = await axios.get(generateOcsUrl(baseUrl + '/config'))
	return response.data.ocs.data
}
