// SPDX-FileCopyrightText: Anna Larch <anna.larch@gmx.net>
// SPDX-License-Identifier: AGPL-3.0-or-later

const path = require('path')

const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry.admin_settings = path.join(__dirname, 'src', 'admin-settings.js')

module.exports = webpackConfig
