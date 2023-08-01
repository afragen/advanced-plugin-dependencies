# Advanced Plugin Dependencies

* Contributors: afragen, costdev, pbiron
* Description: Add plugin install dependencies tab, support for non dot org plugins, and information about dependencies.
* License: MIT
* Network: true
* Requires at least: 6.0
* Requires PHP: 5.6

## Description

Adds a Dependencies tab in the plugin install page. If a requiring plugin does not have all its dependencies installed and active, it will not activate. An add-on the the Plugin Dependencies feature. Adds support for non dot org plugins.

* Plugins not in dot org may use the format `<slug>|<URI>` in the **Requires Plugins** header. `URI` should return a JSON compatible with the `plugins_api()` response or be a JSON file at the plugin root, `<slug>|<slug>.json`.
* Displays a single admin notice with link to **Plugins > Add New > Dependencies** if not all plugin dependencies have been installed.
* Adds a new view/tab to plugins install page ( **Plugins > Add New** ) titled **Dependencies** that contains plugin cards for all plugin dependencies.
* This view also lists which plugins require which plugin dependencies in the plugin card.
* If the dependency API data is not available a generic plugin card will be displayed in the Dependencies tab.
* Circular dependencies cannot be activated and an admin notice noting the circular dependencies is displayed.

There are several single file plugins that may be used for testing in `test-plugins/`.

## Pull Requests

PRs should be made against the `develop` branch.

## Phase 1 - Architecture & Design Discussion

Discussion of architecture and design elements and features. This will be done via [issues](https://github.com/WordPress/wp-plugin-dependencies/issues).

Please review them, comment on them, and create new issues.

## Phase 2 - Actual code review

If you are more comfortable looking at actual code, below are the 2 PRs currently available for [#22316](https://core.trac.wordpress.org/ticket/22316). Please comment on the [trac ticket](https://core.trac.wordpress.org/ticket/22316).

Implement WP admin UI for plugin dependencies
[PR #1547](https://github.com/WordPress/wordpress-develop/pull/1547)

Plugin Dependencies Tab
[PR #1724](https://github.com/WordPress/wordpress-develop/pull/1724)

Currently the above PRs have been closed.
