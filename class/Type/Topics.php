<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
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

    public function getEditorForm($options = null)
    {
        return new TopicsInput($this->schema->name(), $this->schema->getField('type')->table());
    }

    /**
     * Recherche le code de tous les types de topics qui sont associés à une table de type 'thesaurus'.
     *
     * La méthode regarde la table des topics indiquées dans le schéma du sous-champ `type` et retourne tous les
     * codes de topic qui sont associés à une table de lookup de type thesaurus.
     *
     * @return string[] Un tableau de la forme table => topic (les clés indiquent la table utilisée).
     */
    public function getThesaurusTopics()
    {
        // Ouvre la table des topics indiquée dans le schéma du champ 'type'
        list(, $name) = explode(':', $this->schema->getField('type')->table());
        $table = docalist('table-manager')->get($name);

        // Recherche toutes les entrées qui sont associées à une table de type 'thesaurus'
        $topics = [];
        foreach ($table->search('code,source', 'source LIKE "thesaurus:%"') as $code => $source) {
            $topics[substr($source, 10)] = $code; // supprime le préfixe 'thesaurus:'
        }

        // Ok
        return $topics;
    }
}
