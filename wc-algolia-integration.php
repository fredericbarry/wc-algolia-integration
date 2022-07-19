<?php

/**
 * Plugin Name:     Algolia Integration for WooCommerce
 * Plugin URI:      https://www.algolia.com/doc/integration/wordpress/getting-started/quick-start/?client=php
 * Description:     Syncs WooCommerce products with an Algolia index.
 * Author:          Frederic Barry
 * Author URI:      https://fredericbarry.com
 * Version:         1.0.1
 */

namespace AlgoliaForWooCommerce;

if (!defined('ABSPATH')) {
    return;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/wp-cli.php';

global $algolia;
$algolia = \Algolia\AlgoliaSearch\SearchClient::create(ALGOLIA_APPLICATION_ID, ALGOLIA_ADMIN_API_KEY);

function product_to_index(WP_Post $post) {
    return [
        'objectID' => implode('-', [$post->post_type, $post->ID]),
        'name' => $post->post_title,
        'slug' => $post->post_name,
        'description' => strip_tags($post->post_content),
        'image' => \get_the_post_thumbnail_url($post, 'thumbnail'),
        'categories' => \wp_get_post_terms($post->ID, 'product_cat', array('fields' => 'names')),
    ];
}

function update_post($id, WP_Post $post, $update) {
    if (\wp_is_post_revision($id) || \wp_is_post_autosave($id)) {
        return $post;
    }

    global $algolia;

    $record = product_to_index($post);

    if (!isset($record['objectID'])) {
        $record['objectID'] = implode('-', [$post->post_type, $post->ID]);
    }

    $index = $algolia->initIndex(ALGOLIA_INDEX_NAME);

    if ('trash' == $post->post_status) {
        $index->deleteObject($record['objectID']);
    } else {
        $index->saveObject($record);
    }

    return $post;
}

\add_filter('product_to_index', 'product_to_index');
\add_action('save_post', 'update_post', 10, 3);
