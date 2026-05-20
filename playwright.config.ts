// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { defineConfig } from '@playwright/test'

export default defineConfig({
	testDir: 'playwright/e2e',
	fullyParallel: true,
	// Single worker: SQLite doesn't support concurrent writes
	workers: 1,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [['blob'], ['dot'], ['github']]
		: 'html',

	use: {
		baseURL: process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:8081/index.php/',
		trace: 'on-first-retry',
		video: 'on-first-retry',
	},

	webServer: {
		command: 'node playwright/start-server.mjs',
		url: 'http://localhost:8081/index.php/login',
		reuseExistingServer: !process.env.CI,
		timeout: 5 * 60 * 1000,
	},
})
