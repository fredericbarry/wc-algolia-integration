<?php

namespace AlgoliaForWooCommerce\CLI;

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
                    \WP_CLI::line('Serializing ['.$post->post_title.']');
                }

                $record = (array) \apply_filters('post_to_record', $post);

                if (!isset($record['objectID'])) {
                    $record['objectID'] = implode('-', [$post->post_type, $post->ID]);
                }

                $records[] = $record;
                $count++;
            }

            if (!empty($assoc_args['verbose'])) {
                \WP_CLI::line('Sending batch');
            }

            $index->saveObjects($records);

            $paged++;

        } while (true);

        \WP_CLI::success("$count posts indexed in Algolia");
    }
}

\WP_CLI::add_command('algolia', 'AlgoliaForWooCommerce\CLI\Algolia_Command');
