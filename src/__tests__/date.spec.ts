// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import { describe, expect, it } from 'vitest'
import { formatDate } from '../date.js'

describe('formatDate', () => {
	it('formats a date as YYYY-MM-DD', () => {
		expect(formatDate(new Date(2025, 6, 1))).toBe('2025-07-01')
	})

	it('zero-pads single-digit months', () => {
		expect(formatDate(new Date(2025, 0, 15))).toBe('2025-01-15')
	})

	it('zero-pads single-digit days', () => {
		expect(formatDate(new Date(2025, 11, 5))).toBe('2025-12-05')
	})

	it('handles the last day of a month', () => {
		expect(formatDate(new Date(2025, 1, 28))).toBe('2025-02-28')
	})

	it('correctly uses getDate not getDay', () => {
		// getDay() returns 0–6 (weekday); getDate() returns 1–31 (day of month)
		// This test would catch the original bug where getDay() was used instead
		const date = new Date(2025, 6, 31) // July 31st, a Thursday → getDay() = 4
		expect(formatDate(date)).toBe('2025-07-31')
	})
})
