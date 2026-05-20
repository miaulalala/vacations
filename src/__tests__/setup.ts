// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

// Stub globals that @nextcloud/* packages expect in a browser context
globalThis.OC = {
	requestToken: 'test-token',
	webroot: '',
	coreApps: [],
	config: { modRewriteWorking: false },
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
} as any

globalThis.OCP = {} as any
