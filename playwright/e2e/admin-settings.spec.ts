// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { login } from '@nextcloud/e2e-test-server/playwright'
import { expect, test } from '@playwright/test'

const admin = { userId: 'admin', password: 'admin' }

test.describe('Admin settings', () => {
	test('admin settings page loads', async ({ page, request }) => {
		await login(request, admin)

		await page.goto('settings/admin/vacation')
		await expect(page.getByRole('heading', { name: /Vacation/ })).toBeVisible()
	})

	test('notification email field is visible', async ({ page, request }) => {
		await login(request, admin)

		await page.goto('settings/admin/vacation')
		await expect(page.getByLabel(/Fallback notification email/i)).toBeVisible()
	})

	test('min lead time field is visible', async ({ page, request }) => {
		await login(request, admin)

		await page.goto('settings/admin/vacation')
		await expect(page.getByLabel(/minimum.*days/i)).toBeVisible()
	})

	test('saving settings persists across reload', async ({ page, request }) => {
		await login(request, admin)

		await page.goto('settings/admin/vacation')

		const minDaysInput = page.getByLabel(/minimum.*days/i)
		await minDaysInput.fill('3')

		await page.waitForResponse((r) => r.url().includes('/api/v1/settings') && r.request().method() === 'POST')

		await page.reload()
		await expect(page.getByLabel(/minimum.*days/i)).toHaveValue('3')
	})
})
