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

use Docalist\Type\Collection;
use Docalist\Forms\TopicsInput;
use Docalist\Biblio\Type\Topic;

/**
 * Une collection de topics d'indexation.
 */
class Topics extends Collection
{
    protected static $type = 'Docalist\Biblio\Type\Topic';

// inutile : seul le schéma des éléments de la collection est pris en compte
//     public static function loadSchema()
//     {
//         return [
//             'key' => 'type',
//             'table' => 'table:topics',
//             'editor' => 'table',
//         ];
//     }

    public function offsetSet($offset, $value)
    {
        // Si value n'est pas du bon type, on l'instancie
        if (! $value instanceof Topic) { /** @var Topic $value */
            $value = new Topic($value, $this->schema);
        }
        $value->setParent($this);

        parent::offsetSet($offset, $value);
    }

    public function getEditorForm($options = null)
    {
        return new TopicsInput($this->schema->name(), $this->schema->table());
    }

// TODO : à porter vers nouveau système + choix de la table dans les settings
//     public function map(array & $document) {
//         $tables = docalist('table-manager'); /** @var TableManager $tables */

//         foreach($this->value as $topic) { /** @var Topic $topic */

//             // Récupère la liste des termes
//             $terms = $topic->term();

//             // Récupère la table qui contient la liste des vocabulaires
//             $tableName = explode(':', $this->schema->table())[1];
//             $table = $tables->get($tableName); /** @var TableInterface $table */

//             // Détermine la source qui correspond au type du topic
//             $source = $table->find('source', 'code='. $table->quote($topic->type()));
//             if ($source !== false) { // type qu'on n'a pas dans la table topics
//                 list($type, $tableName) = explode(':', $source);

//                 // Si la source est une table, on traduit les termes
//                 if ($type === 'table' || $type === 'thesaurus') {
//                     $table = $tables->get($tableName); /** @var TableInterface $table */
//                     foreach ($terms as & $term) {
//                         $result = $table->find('label', 'code=' . $table->quote($term));
//                         $result !== false && $term = $result;
//                     }
//                 }
//                 // Sinon, on indexe les codes
//             }

//             $document['topic.' . $topic->type()][] = $terms;
//         }

}
