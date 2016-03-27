<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\MultiField;

/**
 * Relation
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Integer[] $ref
 */
class Relation extends MultiField {
    static public function loadSchema() {
        return [
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:relations',
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de relation', 'docalist-biblio'),
                ],
                'ref' => [
                    'type' => 'Docalist\Type\Integer*',
                    'label' => __('Notices liées', 'docalist-biblio'),
                    'description' => __('Numéro de référence des notices (Ref)', 'docalist-biblio'),
                ]
            ]
        ];
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('relation')->integer();
        $mapping->addTemplate('relation.*')->copyFrom('relation')->copyDataTo('relation');
    }

    public function mapData(array & $document) {
        $document['relation.' . $this->type()][] = $this->ref();
    }
*/
    private static function getRef($ref) {
        // cette fonction suppose que post_name === ref, attention si ça change
        // on suppose également que la ref en cours dans $post est celle
        // qu'on formatte (pour avoir le post_type)

        global $post; // post en cours, pour avoir le post_type

        $args = [
            'name' => $ref,
            'post_type' => $post->post_type,
            'numberposts' => 1
        ];
        $posts = get_posts($args);
        return $posts ? $posts[0] : null;
    }

    protected static function initFormats() {
        self::registerFormat('ref', 'Numéro de la notice liée', function(Relation $relation) {
            return implode(', ', $relation->ref());
        });

        self::registerFormat('type-ref', 'Type de relation et numéro de la notice liée', function(Relation $relation, Relations $parent) {
            $type = $parent->lookup($relation->type());
            $refs = self::callFormat('refs', $relation, $parent);
            return $type . ' : ' . $refs;
        });

        self::registerFormat('title', 'Titre de la notice liée', function(Relation $relation) {
            $t = [];
            foreach($relation->ref() as $ref) {
                $post = self::getRef($ref);
                $t[] = $post ? get_the_title($post): "non trouvé $ref";
            }
            return implode(', ', $t);
        });

        self::registerFormat('type-title', 'Type de relation et titre de la notice liée', function(Relation $relation, Relations $parent) {
            $type = $parent->lookup($relation->type());
            $refs = self::callFormat('title', $relation, $parent);
            return $type . ' : ' . $refs;
        });

        self::registerFormat('ref-link', 'Numéro de notice cliquable', function(Relation $relation) {
            $t = [];
            foreach($relation->ref() as $ref) {
                $post = self::getRef($ref);
                if ($post) {
                    $format = '<a href="%s" title="%s">%s</a>';
                    $url = get_post_permalink($post);
                    $title = get_the_title($post);

                    $t[] = sprintf($format, esc_attr($url), esc_attr($title), esc_html($ref));
                } else {
                    $t[] = $ref;
                }
            }
            return implode(', ', $t);
        });

        self::registerFormat('type-ref-link', 'Type de relation et numéro de notice cliquable', function(Relation $relation, Relations $parent) {
            $type = $parent->lookup($relation->type());
            $refs = self::callFormat('ref-link', $relation, $parent);
            return $type . ' : ' . $refs;
        });

        self::registerFormat('title-link', 'Titres des notices cliquables', function(Relation $relation) {
            $t = [];
            foreach($relation->ref() as $ref) {
                $post = self::getRef($ref);
                if ($post) {
                    $format = '<a href="%s" title="%s">%s</a>';
                    $url = get_post_permalink($post);
                    $title = get_the_title($post);

                    $t[] = sprintf($format, esc_attr($url), 'ref ' . esc_html($ref), esc_attr($title));
                } else {
                    $t[] = $ref;
                }
            }
            return implode(', ', $t);
        });

        self::registerFormat('type-title-link', 'Type de relation et titre de notice cliquable', function(Relation $relation, Relations $parent) {
            $type = $parent->lookup($relation->type());
            $refs = self::callFormat('title-link', $relation, $parent);
            return $type . ' : ' . $refs;
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de numéros de ref
        return $this->filterEmptyProperty('ref');
    }
}