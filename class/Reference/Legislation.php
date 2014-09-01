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
 * Législation.
 *
 * Décrit un texte législatif ou réglementaire : loi, projet de loi, proposition
 * de loi, ordonnance, décret, arrêté, circulaire, convention, décision, code
 * législatif, code réglementaire, note de service, etc.
 *
 * @see http://www.snphar.com/data/A_la_une/phar27/legislation27.pdf
 *
 * Principales caractéristiques :
 * - l'auteur est un député, un sénateur, ou le premier ministre
 * - a une date de dépôt
 * - est publié ou nom au bo, au jo, etc.
 */
class Legislation extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'legislation',
            'label' => __('Législation', 'docalist-biblio'),
            'description' => __('Un texte législatif ou réglementaire.', 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Nature du document'],
                $fields['genre'],
                $fields['media'],

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                $fields['title'],
//                 $fields['othertitle'],
//                 $fields['translation'],

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Auteurs'],
                $fields['author'],
                $fields['organisation'],

                // Journal, Number, Date, Edition
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Journal / Périodique'],
                $fields['journal'],
                $fields['number'],
                $fields['edition'],

                // Date / Language / Pagination / Format
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                $fields['date'],
                $fields['language'],
                $fields['extent'],
                $fields['format'],

                // Topic / Abstract / Note
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // Liens et relations
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group8' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],
            ]
        ];
        // @formatter:on
    }
}