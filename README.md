# Algolia integration for WooCommerce

Based on the WordPress integration with Algolia [documentation](https://www.algolia.com/doc/integration/wordpress/getting-started/quick-start/?client=php)

## Installation

- Run the `composer install` command from the plugin's root directory.
- Add the following lines to the `wp-config.php` file and fill with the appropriate values:

```php
define( 'ALGOLIA_APPLICATION_ID', '' );
define( 'ALGOLIA_ADMIN_API_KEY', '' );
define( 'ALGOLIA_INDEX_NAME', '' );
```

## Development

- Increment the version number in the plugin header
- Create and push a tag corresponding to the version number
- Create a release and document the changes

## Reindex all products using WP-CLI

Run the following command from the plugin's root directory:

```shell
wp algolia reindex --verbose
```
