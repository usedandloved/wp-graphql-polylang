<?php

namespace WPGraphQL\Extensions\Polylang;

use GraphQLRelay\Relay;

class LanguageRootQueries
{
    function init()
    {
        add_action(
            'graphql_register_types',
            [$this, '__action_graphql_register_types'],
            10,
            0
        );
    }

    function __action_graphql_register_types()
    {
        register_graphql_field('RootQuery', 'languages', [
            'type' => ['list_of' => 'Language'],
            'description' => __(
                'List available languages',
                'wp-graphql-polylang'
            ),
            'resolve' => function ($source, $args, $context, $info) {
                $fields = $info->getFieldSelection();
                
                global $q_config;
                $langs = qtranxf_getSortedLanguages();

                $languages = array_map(function ($code) {
                    return [
                        'id' => Relay::toGlobalId('Language', $code),
                        'code' => $code,
                        'slug' => $code,
                    ];
                }, $langs);

                if (isset($fields['name']) || isset($fields['locale'])) {
                    foreach (
                        $langs as $index => $lang
                    ) {
                        if (isset($fields['name'])) {
                            $languages[$index]['name'] = $q_config['language_name'][$lang];
                        }
                        if (isset($fields['locale'])) {
                            $languages[$index]['locale'] = $q_config['locale'][$lang];
                        }
                    }
                }

                if (isset($fields['homeUrl'])) {
                    foreach ($languages as &$language) {
                        $language['homeUrl'] = qtranxf_convertURL( home_url(), $language['slug'] );
                    }
                }

                return $languages;
            },
        ]);

        register_graphql_field('RootQuery', 'defaultLanguage', [
            'type' => 'Language',
            'description' => __('Get language list', 'wp-graphql-polylang'),
            'resolve' => function ($source, $args, $context, $info) {
                $fields = $info->getFieldSelection();
                $language = [];

                global $q_config;
                $lang = $q_config['default_language'];

                // All these fields are build from the same data...
                if (Helpers::uses_slug_based_field($fields)) {
                    $language['code'] = $lang;
                    $language['id'] = Relay::toGlobalId(
                        'Language',
                        $lang
                    );
                    $language['slug'] = $lang;
                }

                if (isset($fields['name'])) {
                    $language['name'] = $q_config['language_name'][$lang];
                }

                if (isset($fields['locale'])) {
                    $language['locale'] = $q_config['locale'][$lang];
                }
                if (isset($fields['homeUrl'])) {
                    $language['homeUrl'] = qtranxf_convertURL( home_url(), $lang );
                }

                return $language;
            },
        ]);
    }
}
