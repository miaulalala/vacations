// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { createRandomUser, login } from '@nextcloud/e2e-test-server/playwright'
import { expect, test } from '@playwright/test'

test.describe('Vacation request workflow', () => {
	test('employee can open the vacation app', async ({ page, request }) => {
		const user = await createRandomUser()
		await login(request, user)

		await page.goto('apps/vacation')
		await expect(page).toHaveTitle(/Vacation/)
	})

	test('vacation form is visible on the request tab', async ({ page, request }) => {
		const user = await createRandomUser()
		await login(request, user)

		await page.goto('apps/vacation')

		await expect(page.getByRole('heading', { name: 'New vacation request' })).toBeVisible()
		await expect(page.getByLabel('Start date')).toBeVisible()
		await expect(page.getByLabel('End date')).toBeVisible()
		await expect(page.getByLabel('Number of vacation days')).toBeVisible()
	})

	test('submit button is disabled until form is signed', async ({ page, request }) => {
		const user = await createRandomUser()
		await login(request, user)

		await page.goto('apps/vacation')

		const submitBtn = page.getByRole('button', { name: 'Submit request' })
		await expect(submitBtn).toBeDisabled()

		// Fill in required fields
		await page.getByLabel('Number of vacation days').fill('5')
		await page.getByLabel('Signature (type your full name)').fill('Test User')
		await page.getByRole('checkbox', { name: /I confirm/ }).check()

		// Still disabled — no manager selected
		await expect(submitBtn).toBeDisabled()
	})

	test('approval tab shows empty state for new user', async ({ page, request }) => {
		const user = await createRandomUser()
		await login(request, user)

		await page.goto('apps/vacation')

		await page.getByRole('tab', { name: /Approvals|Pending/ }).click()
		await expect(page.getByText('No pending requests to review.')).toBeVisible()
	})

	test('my requests tab shows empty state for new user', async ({ page, request }) => {
		const user = await createRandomUser()
		await login(request, user)

		await page.goto('apps/vacation')

		await page.getByRole('tab', { name: /My requests/ }).click()
		await expect(page.getByText(/No vacation requests/)).toBeVisible()
	})
})
