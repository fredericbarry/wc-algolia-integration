# Algolia Integration for WooCommerce

## Installation

- Run the `composer install` command from the plugin's root directory.
- Add the following lines to the `wp-config.php` file and fill with the appropriate values:

```php
define( 'ALGOLIA_APPLICATION_ID', '' );
define( 'ALGOLIA_ADMIN_API_KEY', '' );
define( 'ALGOLIA_INDEX_NAME', '' );
```

## Reindex all products using WP-CLI

Run the following command from the plugin's root directory:

```shell
wp algolia reindex --verbose
```
