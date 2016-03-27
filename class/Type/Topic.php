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
namespace Docalist\Biblio\Type;

use Docalist\Type\MultiField;
use Docalist\Table\TableManager;
use Docalist\Table\TableInterface;
use Docalist\Forms\TopicsInput;
use InvalidArgumentException;
use Docalist\Type\TableEntry;
use Docalist\Type\Text;

/**
 * Une liste de mots-clés d'un certain type.
 *
 * @property TableEntry $type
 * @property Text[] $term
 */
class Topic extends MultiField
{
    /**
     * Collection parent (pour avoir accès à la table des topics)
     *
     * @var Topics
     */
    protected $parent;

    public static function loadSchema()
    {
        return [
            'label' => __('Indexation', 'docalist-biblio'),
            'description' => __('Tags, mots-clés, catégories, domaines, étiquettes, classification...', 'docalist-biblio'),
            'table' => 'table:topics', // non, dans la collection
            'editor' => 'table',
            'key' => 'type',
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:topics',
                    'label' => __('Vocabulaire', 'docalist-biblio'),
                ],
                'term' => [
                    'type' => 'Docalist\Type\Text*',
                    'label' => __('Termes', 'docalist-biblio'),
                ],
            ],
        ];
    }

    /**
     * @return Topics|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Topics $parent)
    {
        $this->parent = $parent;
    }

    public function getAvailableEditors()
    {
        return [];
    }

    public function getEditorForm($options = null)
    {
        $editor = new TopicsInput($this->schema->name(), $this->schema->table());

        $editor
            ->setName($this->schema->name())
            ->setLabel($this->getOption('label', $options))
            ->setDescription($this->getOption('description', $options));

        return $editor;
    }

    public function getAvailableFormats()
    {
        return [
            'v'     => 'Mots-clés',
            'V'     => 'Code des mots-clés (i.e. mots-clés en majuscules)',
            't : v' => 'Nom du vocabulaire : Mots-clés',
            't: v'  => 'Nom du vocabulaire: Mots-clés',
            'v (t)' => 'Mots-clés (Nom du vocabulaire)'
        ];
    }

    public function getTermsLabel()
    {
        // Récupère la liste des termes
        $terms = $this->term();
        $terms = array_combine($terms, $terms);

        // Récupère la table des topics utilisée (dans le schéma de la collection Topics parent)
        if (is_null($this->parent) || is_null($schema = $this->parent->schema()) || is_null($table = $schema->table())) {
            return $this->term();
        }

        // Récupère le table-manager
        $tables = docalist('table-manager'); /* @var $tables TableManager */

        // Récupère la table qui contient la liste des vocabulaires
        $tableName = explode(':', $table)[1];
        $table = $tables->get($tableName); /* @var $table TableInterface */

        // Détermine la source qui correspond au type du topic
        $source = $table->find('source', 'code='. $table->quote($this->type()));
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

        // Ok
        return $terms;
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());

        switch ($format) {
            case 'v':
                return implode(', ', $this->getTermsLabel());
            case 'V':
                return implode(', ', $this->term());
            case 't : v':
                $format = '%s : %s'; // espace insécable avant le ':'
                break;
            case 't: v':
                $format = '%s: %s';
                break;
            case 'v (t)':
                $format = '%2$s (%1$s)';
                break;
            default:
                throw new InvalidArgumentException("Invalid Topic format '$format'");
        }

        return sprintf(
            $format,
            $this->type->getEntryLabel(),
            implode(', ', $this->getTermsLabel())
        );
    }

//     protected static function initFormats() {
//         self::registerFormat('v', 'Mots-clés', function(Topic $topic, Topics $parent) {
//             // Récupère la liste des termes
//             $terms = $topic->term();

//             $tables = docalist('table-manager'); /* @var $tables TableManager */

//             // Récupère la table qui contient la liste des vocabulaires
//             $tableName = explode(':', $parent->schema->table())[1];
//             $table = $tables->get($tableName); /* @var $table TableInterface */

//             // Détermine la source qui correspond au type du topic
//             $source = $table->find('source', 'code='. $table->quote($topic->type()));
//             if ($source !== false) { // type qu'on n'a pas dans la table topics
//                 list($type, $tableName) = explode(':', $source);

//                 // Si la source est une table, on traduit les termes
//                 if ($type === 'table' || $type === 'thesaurus') {
//                     $table = $tables->get($tableName); /* @var $table TableInterface */
//                     foreach ($terms as & $term) {
//                         $result = $table->find('label', 'code=' . $table->quote($term));
//                         $result !== false && $term = $result;
//                     }
//                 }
//             }

//             // Sinon, on les retourne tels quels

//             return implode(', ', $terms);
//         });

//         self::registerFormat('V', 'Code des mots-clés (i.e. mots-clés en majuscules)', function(Topic $topic, Topics $parent) {
//             return implode(', ', $topic->term());
//         });

//         self::registerFormat('t : v', 'Nom du vocabulaire : Mots-clés', function(Topic $topic, Topics $parent) {
//             $terms = self::callFormat('v', $topic, $parent);
//             return $parent->lookup($topic->type()) . ' : ' . $terms;
//             // espace insécable avant le ':'
//         });

//         self::registerFormat('t: v', 'Nom du vocabulaire: Mots-clés', function(Topic $topic, Topics $parent) {
//             $terms = self::callFormat('v', $topic, $parent);
//             return $parent->lookup($topic->type()) . ': ' . $terms;
//         });

//         self::registerFormat('v (t)', 'Mots-clés (Nom du vocabulaire)', function(Topic $topic, Topics $parent) {
//             $terms = self::callFormat('v', $topic, $parent);
//             return $terms . ' (' . $parent->lookup($topic->type()) . ')';
//             // espace insécable avant '('
//         });
//     }

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
