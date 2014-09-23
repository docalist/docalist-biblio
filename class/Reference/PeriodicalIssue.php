<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Reference;

use Docalist\Biblio\Reference;
use Docalist\Schema\Schema;

/**
 * Numéro de périodique.
 *
 * Décrit un numéro particulier d'un périodique.
 *
 * - A single instance of a periodically published journal, magazine, or
 *   newspaper.
 *   @see http://en.wikipedia.org/wiki/Issue
 *
 * Principales caractéristiques :
 * - a un parent de type Periodical
 */
class PeriodicalIssue extends Reference {
    static protected function loadSchema() {
        // Récupère les champs d'une référence standard
        $fields = parent::loadSchema()['fields'];

        // Supprime les champs qu'on n'utilise pas
        unset($fields['genre']);
        unset($fields['media']);
        unset($fields['othertitle']);
        unset($fields['translation']);
        unset($fields['author']);
        unset($fields['organisation']);
        unset($fields['journal']);
        unset($fields['language']);
        unset($fields['editor']);
        unset($fields['collection']);
        unset($fields['event']);

        // Personnalise les tables, les libellés, les description, etc.
        // todo

        // Contruit notre schéma
        return [
            'name' => 'periodical-issue',
            'label' => __('Numéro de périodique', 'docalist-biblio'),
            'description' => __('Une parution d\'un périodique.', 'docalist-biblio'),
            'fields' => $fields,
        ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un numéro de périodique.", 'docalist-biblio'),
            'fields' => [
                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                'number',
                'title',

                // Date / Language / Pagination / Format
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                'date',
                'edition',
                'extent',
                'format',

                // Topic / Abstract / Note
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                'topic',
                'content',

                // // Liens et relations
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                'link',
                'relation',

                // Ref / Owner / Creation / Lastupdate
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                'type',
                'ref',
                'owner',
            ]
        ]);
    }
/*
 posttype
 creation
 lastupdate
 password
 parent
 slug
 imported
 errors
 */

}