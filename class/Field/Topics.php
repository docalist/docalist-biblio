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

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\TopicsInput;
use Docalist\Table\TableManager;
use Docalist\Table\TableInterface;

/**
 * Une collection de topics d'indexation.
 */
class Topics extends Repeatable {
    static protected $type = 'Docalist\Biblio\Field\Topic';

    public function editForm() {
        return new TopicsInput($this->schema->name(), $this->schema->table());
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'topics', __('Table des vocabulaires', 'docalist-biblio'), true);
    }

    /*
     * La fonction map qui suit ne devrait pas être là. Cela devrait être dans
     * la classe Topic et on devrait utiliser les formats pour générer les
     * libellés des mots-clés.
     * Le problème, c'est que les formatters sont des méthodes statiques et que
     * une fois dans Topic, on n'a plus accès au parent du Topic.
     * A revoir quand les formatters ne seront plus des statics.
     */
    public function map(array & $document) {
        $tables = docalist('table-manager'); /* @var $tables TableManager */

        foreach($this->value as $topic) { /* @var $topic Topic */

            // Récupère la liste des termes
            $terms = $topic->term();

            // Récupère la table qui contient la liste des vocabulaires
            $tableName = explode(':', $this->schema->table())[1];
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
                // Sinon, on indexe les codes
            }

            $document['topic.' . $topic->type()][] = $terms;
        }
    }
}