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
 * @version     $Id$
 */
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\MultiField;
use Docalist\Table\TableManager;
use Docalist\Table\TableInfo;
use Docalist\Table\TableInterface;
use Docalist\Search\MappingBuilder;

/**
 * Une liste de mots-clés d'un certain type.
 *
 * @property String $type
 * @property String[] $term
 */
class Topic extends MultiField {
    static protected $groupkey = 'type';

    static protected function loadSchema() {
        // @formatter:off
        return [
            'fields' => [
                'type' => [
                    'label' => __('Type', 'docalist-biblio'),
    //                 'description' => __('Type des mots-clés (nom du thesaurus ou de la liste)', 'docalist-biblio'),
                ],
                'term' => [ // @todo : au pluriel ?
                    'repeatable' => true,
                    'label' => __('Termes', 'docalist-biblio'),
    //                 'description' => __('Liste des mots-clés.', 'docalist-biblio'),
                ]
            ]
        ];
        // @formatter:on
    }

    public function __toString() {
        return $this->type() . ' : ' . implode(', ', $this->term());
    }

    public function mapping(MappingBuilder $mapping) {
        $mapping->field('topic')->text()->filter()->suggest();
        $mapping->template('topic.*')->idem('topic')->copyTo('topic');
    }

    /*
     * Non utilisé, c'est Topics::map() qui fait le boulot
     */
//     public function map(array & $document) {
//         $document['topic.' . $this->type()][] = $this->__get('term')->value();
//     }

    protected static function initFormats() {
        self::registerFormat('v', 'Mots-clés', function(Topic $topic, Topics $parent) {
            // Récupère la liste des termes
            $terms = $topic->term();

            $tables = docalist('table-manager'); /* @var $tables TableManager */

            // Récupère la table qui contient la liste des vocabulaires
            $tableName = explode(':', $parent->schema->table())[1];
            $table = $tables->get($tableName); /* @var $table TableInterface */

            // Détermine la source qui correspond au type du topic
            $source = $table->find('source', 'code='. $table->quote($topic->type()));
            if ($source !== false) { // type qu'on n'a pas dans la table topics
                list($type, $tableName) = explode(':', $source);

                // Si la source est une table, on traduit les termes
                if ($type === 'table' || $type === 'thesaurus') {
                    $table = $tables->get($tableName); /* @var $table TableInterface */
                    foreach ($terms as & $term) {
                        $result = $table->find('label', 'code=' . $table->quote($term));
                        $result !== false && $term = $result;
                    }
                }
            }

            // Sinon, on les retourne tels quels

            return implode(', ', $terms);
        });

        self::registerFormat('V', 'Code des mots-clés (i.e. mots-clés en majuscules)', function(Topic $topic, Topics $parent) {
            return implode(', ', $topic->term());
        });

        self::registerFormat('t : v', 'Nom du vocabulaire : Mots-clés', function(Topic $topic, Topics $parent) {
            $terms = self::callFormat('v', $topic, $parent);
            return $parent->lookup($topic->type()) . ' : ' . $terms;
            // espace insécable avant le ':'
        });

        self::registerFormat('t: v', 'Nom du vocabulaire: Mots-clés', function(Topic $topic, Topics $parent) {
            $terms = self::callFormat('v', $topic, $parent);
            return $parent->lookup($topic->type()) . ': ' . $terms;
        });

        self::registerFormat('v (t)', 'Mots-clés (Nom du vocabulaire)', function(Topic $topic, Topics $parent) {
            $terms = self::callFormat('v', $topic, $parent);
            return $terms . ' (' . $parent->lookup($topic->type()) . ')';
            // espace insécable avant '('
        });
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas de mots-clés
        return $this->filterEmptyProperty('term');
    }
}