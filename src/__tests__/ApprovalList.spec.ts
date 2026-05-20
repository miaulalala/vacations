// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { mount } from '@vue/test-utils'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import ApprovalList from '../components/ApprovalList.vue'

vi.mock('../api.js', () => ({
	approveVacation: vi.fn(),
	declineVacation: vi.fn(),
}))

vi.mock('@nextcloud/dialogs', () => ({
	showError: vi.fn(),
	showSuccess: vi.fn(),
	showWarning: vi.fn(),
}))

vi.mock('@nextcloud/l10n', async (importOriginal) => {
	const actual = await importOriginal<typeof import('@nextcloud/l10n')>()
	return {
		...actual,
		t: (app: string, text: string, params?: Record<string, unknown>) => {
			if (params) {
				return Object.entries(params).reduce(
					(acc, [k, v]) => acc.replace(`{${k}}`, String(v)),
					text,
				)
			}
			return text
		},
	}
})

const sampleVacation = {
	id: 1,
	userId: 'alice',
	start: '2025-07-01',
	end: '2025-07-14',
	dayCount: 10,
	status: 0,
	requestDate: '2025-06-01',
	signature: 'Alice Example',
	signatureVerified: true,
	replacementUserId: 'bob',
	managerUserId: 'carol',
	message: 'Summer holiday',
}

describe('ApprovalList', () => {
	it('shows empty state when no vacations', () => {
		const wrapper = mount(ApprovalList, { props: { vacations: [] } })
		expect(wrapper.text()).toContain('No pending requests to review.')
	})

	it('renders a vacation card for each pending request', () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		expect(wrapper.findAll('.approval-card')).toHaveLength(1)
		expect(wrapper.text()).toContain('alice')
	})

	it('shows approve and decline buttons for each card', () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		const buttons = wrapper.findAll('button')
		const labels = buttons.map((b) => b.text())
		expect(labels).toContain('Approve')
		expect(labels).toContain('Decline')
	})

	it('shows the message input after clicking approve', async () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		const approveBtn = wrapper.findAll('button').find((b) => b.text() === 'Approve')
		await approveBtn!.trigger('click')

		expect(wrapper.find('.approval-card__message').exists()).toBe(true)
	})

	it('hides the action buttons and shows message input after clicking decline', async () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		const declineBtn = wrapper.findAll('button').find((b) => b.text() === 'Decline')
		await declineBtn!.trigger('click')

		expect(wrapper.find('.approval-card__message').exists()).toBe(true)
		expect(wrapper.find('.approval-card__actions').exists()).toBe(false)
	})

	it('restores action buttons after clicking cancel', async () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		await wrapper.findAll('button').find((b) => b.text() === 'Approve')!.trigger('click')
		await wrapper.findAll('button').find((b) => b.text() === 'Cancel')!.trigger('click')

		expect(wrapper.find('.approval-card__actions').exists()).toBe(true)
		expect(wrapper.find('.approval-card__message').exists()).toBe(false)
	})

	it('shows replacement user when set', () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		expect(wrapper.text()).toContain('bob')
	})

	it('shows date range', () => {
		const wrapper = mount(ApprovalList, {
			props: { vacations: [sampleVacation] },
		})
		expect(wrapper.text()).toContain('2025-07-01')
		expect(wrapper.text()).toContain('2025-07-14')
	})
})
