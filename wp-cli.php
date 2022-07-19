<?php

namespace FredericBarry\WordPress\Plugin\AlgoliaWooCommerce\CLI;
use FredericBarry\WordPress\Plugin\AlgoliaWooCommerce\product_to_index;

if (!defined('ABSPATH')) {
    return;
}

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class Algolia_Command {
    public function reindex($args, $assoc_args) {
        global $algolia;
        $index = $algolia->initIndex(ALGOLIA_INDEX_NAME);

        $index->clearObjects()->wait();

        $paged = 1;
        $count = 0;

        do {
            $posts = new \WP_Query([
                'posts_per_page' => 20,
                'paged' => $paged,
                'post_type' => 'product',
                'post_status' => 'publish',
            ]);

            if (!$posts->have_posts()) {
                break;
            }

            $records = [];

            foreach ($posts->posts as $post) {
                if (!empty($assoc_args['verbose'])) {
                    \WP_CLI::log('Serializing ['.$post->post_title.']');
                }

                $record = (array) \apply_filters('product_to_index', $post);

                if (!isset($record['objectID'])) {
                    $record['objectID'] = implode('-', [$post->post_type, $post->ID]);
                }

                $records[] = $record;
                $count++;
            }

            if (!empty($assoc_args['verbose'])) {
                \WP_CLI::log('Sending batch');
            }

            $index->saveObjects($records);

            $paged++;

        } while (true);

        \WP_CLI::success("$count posts indexed in Algolia");
    }
}

\WP_CLI::add_command('algolia', __NAMESPACE__ . '\Algolia_Command');
