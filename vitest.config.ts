// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
	plugins: [vue()],
	test: {
		environment: 'happy-dom',
		globals: true,
		setupFiles: ['src/__tests__/setup.ts'],
		exclude: ['playwright/**', 'node_modules/**'],
		server: {
			deps: {
				// Process @nextcloud/vue through Vite so its CSS imports are handled
				inline: ['@nextcloud/vue'],
			},
		},
		coverage: {
			provider: 'v8',
			reporter: ['text', 'lcov'],
			include: ['src/**/*.{js,ts,vue}'],
			exclude: ['src/__tests__/**'],
		},
	},
})
