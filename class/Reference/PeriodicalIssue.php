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
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'periodical-issue',
            'label' => __('Numéro de périodique', 'docalist-biblio'),
            'description' => __('Une parution d\'un périodique.', 'docalist-biblio'),
            'fields' => [

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                $fields['number'],
                $fields['title'],

                // Date / Language / Pagination / Format
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                $fields['date'],
                $fields['edition'],
                $fields['extent'],
                $fields['format'],

                // Topic / Abstract / Note
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // // Liens et relations
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],
            ]
        ];
        // @formatter:on
    }
}