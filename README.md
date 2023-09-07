# Advanced Plugin Dependencies

* Contributors: afragen, costdev, pbiron
* Description: Add plugin install dependencies tab, support for non dot org plugins, and information about dependencies.
* License: MIT
* Network: true
* Requires at least: 6.4
* Requires PHP: 7.2
* Stable release: main

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
