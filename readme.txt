# Advanced Plugin Dependencies

Contributors: afragen, costdev, pbiron
Description: Add plugin install dependencies tab and information about dependencies.
License: MIT
Network: true
Requires at least: 6.0
Requires PHP: 7.0
Tested up to: 6.3
Stable tag: 1.14.3

## Description

Adds a Dependencies tab in the plugin install page. If a requiring plugin does not have all its dependencies installed and active, it will not activate. An add-on the the Plugin Dependencies feature.

* Plugins not in dot org may use the format `<slug>|<URI>` in the **Requires Plugins** header. `URI` should return a JSON compatible with the `plugins_api()` response or be a JSON file at the plugin root, `<slug>|<slug>.json`.
* For a plugin in dot org, the JSON response value of `download_link` must be empty.
* Adds a new view/tab to plugins install page ( **Plugins > Add New** ) titled **Dependencies** that contains plugin cards for all plugin dependencies.
* This view also lists which plugins require which plugin dependencies in the plugin card.
* Displays a single admin notice with link to **Plugins > Add New > Dependencies** if not all plugin dependencies have been installed.
* If the dependency API data is not available a generic plugin card will be displayed in the Dependencies tab.
* Circular dependencies cannot be activated and an admin notice noting the circular dependencies is displayed.

There are several single file plugins that may be used for testing in `test-plugins/`.

## Pull Requests

PRs should be made against the `develop` branch.

## Screenshots

1. Plugin is a Dependency and Plugin needing Dependencies
2. Plugin with Dependencies
3. Plugin Dependencies tab
4. Search page with dependencies
