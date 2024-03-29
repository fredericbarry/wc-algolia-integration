<?php
/**
 * Plugin Name:     Algolia integration for WooCommerce
 * Plugin URI:      https://github.com/fredericbarry/wc-algolia-integration
 * Description:     Syncs WooCommerce products with an Algolia index.
 * Author:          Frederic Barry
 * Author URI:      https://fredericbarry.com
 * Version:         1.0.6
 */

namespace FredericBarry\WordPress\Plugin\AlgoliaWooCommerce;

if (!defined('ABSPATH')) {
    return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/wp-cli.php';

global $algolia;
$algolia = \Algolia\AlgoliaSearch\SearchClient::create(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

function product_to_index(\WP_Post $post)
{
    return [
        'objectID' => implode('-', [$post->post_type, $post->ID]),
        'name' => $post->post_title,
        'slug' => $post->post_name,
        'description' => strip_tags($post->post_content),
        'image' => \get_the_post_thumbnail_url($post, 'thumbnail'),
        'categories' => \wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'names')),
    ];
}

function update_product_post($post_id, \WP_Post $post, $update)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (\wp_is_post_autosave($post_id) || \wp_is_post_revision($post_id)) {
        return;
    }

    if ($post->post_status != 'publish' && !$update) {
        return;
    }

    global $algolia;

    $record = product_to_index($post);

    if (!isset($record['objectID'])) {
        $record['objectID'] = implode('-', [$post->post_type, $post->ID]);
    }

    $index = $algolia->initIndex(ALGOLIA_INDEX_NAME);

    if ($post->post_status == 'publish') {
        $index->saveObject($record);
    } else {
        $index->deleteObject($record['objectID']);
    }
}

\add_filter('product_to_index', __NAMESPACE__ . '\product_to_index');
\add_action('save_post_product', __NAMESPACE__ . '\update_product_post', 10, 3);
