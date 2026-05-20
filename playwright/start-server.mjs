// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

import {
	configureNextcloud,
	runExec,
	runOcc,
	startNextcloud,
	stopNextcloud,
	waitOnNextcloud,
} from '@nextcloud/e2e-test-server/docker'

const branch = process.env.BRANCH ?? 'master'

const ip = await startNextcloud(branch, undefined, { exposePort: 8081 })
await waitOnNextcloud(ip)

await configureNextcloud(['vacation'])

await runOcc(['config:system:set', 'no_unsupported_browser_warning', '--value', 'true', '--type', 'boolean'])
await runOcc(['config:system:set', 'appstoreenabled', '--value', 'false', '--type', 'boolean'])

// Enable WAL mode for SQLite so concurrent reads don't block writes during tests
await runExec([
	'php', '-r',
	'$db = new SQLite3("data/owncloud.db"); $db->busyTimeout(5000); $db->exec("PRAGMA journal_mode = wal;");',
])

await runExec(['php', 'cron.php'])

process.stdout.write('Nextcloud ready at http://localhost:8081\n')

// Keep the server alive until the test runner exits
process.on('SIGTERM', async () => {
	if (process.env.CI) {
		await stopNextcloud()
	}
	process.exit(0)
})
