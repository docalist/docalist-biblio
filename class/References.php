<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     SVN: $Id$
 */

namespace Docalist\Biblio;
use Docalist\PostType;
use WP_Post;

/**
 * Gère le type de contenu "références bibliographiques"
 */
class References extends PostType {
    /**
     * @inheritdoc
     */
    protected $copyFields = array(
        'post_name' => 'ref',
        'post_title' => 'title',
    );

    /**
     * @inheritdoc
     */
    protected $id = 'dclref';

    protected function getMappings() {
        return include dirname(__DIR__) . '/mappings/dclref.php';
    }

    protected function getFields() {
        $settings = require dirname(__DIR__) . '/mappings/dclref.php';
        return $settings['mappings']['dclref']['properties'];
    }

    public function register() {
        parent::register();

        add_action("dclsearch_{$this->id}_mapping", function() {
            return $this->getMappings();
        });

        add_action("dclsearch_{$this->id}_index", function($post) {
            // TODO: c'est à PostType de faire le boulot, pas de get_post_meta ici

            $data = get_post_meta($post->ID, self::META, true);
            if (isset($data['author'])) {
                foreach($data['author'] as & $author) {
                    $key = (isset($author['name']) ? $author['name'] : '')
                         . (isset($author['firstname']) ? (' ' . $author['firstname']) : '');
                    $author['keyword'] = $key;
                }
            }
            return $data;
        });

        add_filter('the_content', function($content) {
            global $post;

            // Regarde si on est concerné
            if ($content || ! \in_the_loop() || $post->post_type !== $this->id) {
                return $content;
            }

            // Affichage d'une notice en pleine page
            if (is_single()) {
                return $this->getContent($post);
            }

            // Affichage de résultats de recherche
            if (is_search()) {
                return $this->getContent($post);
//                return $this->getExcerpt($post);
            }

            // Affichage liste de notices
            return $this->getExcerpt($post); // TODO: faire option
        }, 11);
        // 11 ci-dessus : pour court-circuiter wp_autop qui est installé
        // avec la priorité par défaut qui est de 10. On veut s'exécuter
        // juste après

        \add_filter('get_the_excerpt', function($content) {
            global $post;

            if ($content || ! \in_the_loop() || $post->post_type !== $this->id) {
                return $content;
            }

            return $this->getExcerpt($post);
        }, 11);


    }

    /**
     * @inheritdoc
     */
    protected function registerOptions() {
        return array(
            'labels' => $this->setting('ref.labels'),
            'public' => true,
            'rewrite' => array(
                'slug' => $this->setting('ref.slug'),
                'with_front' => false,
            ),
            'capability_type' => 'post',
            'supports' => array(
                'title',
                'editor',
//                'thumbnail',
            ),
            'supports' => false,
            'has_archive' => true,
        );
    }

    /**
     * @inheritdoc
     */
    protected function registerMetaboxes() {
        remove_meta_box('slugdiv', $this->id(), 'normal');
        $this->add(new Metabox\Type);
        $this->add(new Metabox\Title);
        $this->add(new Metabox\Authors);
        $this->add(new Metabox\Journal);
        $this->add(new Metabox\Biblio);
        $this->add(new Metabox\Editor);
        $this->add(new Metabox\Event);
        $this->add(new Metabox\Topics);
        $this->add(new Metabox\Management);
    }
/*
    protected function asContent(array $data) {

    }
 */

    protected function normalizeRecord(array &$record, array $mappings) {
/*
        $default = array(
            'string' => '',
            'long' => 0,
        );
        $record[$name] = $default[$field[$type]];
 */
        foreach($mappings as $name => $field) {
            if (! isset($record[$name])) {
                if (isset($field['repeatable']) && $field['repeatable']) {
                    $record[$name] = array();
                } else {
                    $record[$name] = null;
                }
            } else {
                if ($field['type'] === 'object' || $field['type'] === 'nested') {
                    // Tableau d'objets
                    if (isset($field['repeatable']) && $field['repeatable']) {
                        foreach($record[$name] as & $value) {
                            $this->normalizeRecord($value, $field['properties']);
                        }
                    }

                    // Objet simple
                    else {
                        $this->normalizeRecord($record[$name], $field['properties']);
                    }
                }
            }
        }
    }

    protected function template($template, array $data) {
        $save = $GLOBALS;
        $GLOBALS = & $data;
        extract($data);
        unset($data);

        ob_start();
        require $this->plugin()->directory() . $template;
        $GLOBALS = $save;
        return ob_get_clean();
    }

    protected function getContent(WP_POST $post) {
        //TODO : pas ici
        $data = \get_post_meta($post->ID, self::META, true);
        $this->normalizeRecord($data, $this->getFields());

        return $this->template('/templates/fullref.php', $data, $this);
    }

    protected function getExcerpt(WP_POST $post) {
        //TODO : pas ici
        $data = \get_post_meta($post->ID, self::META, true);
        $this->normalizeRecord($data, $this->getFields());

        return $this->template('/templates/shortref.php', $data);
    }
}
