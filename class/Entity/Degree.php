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
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Reference;

/**
 * Diplôme.
 *
 * Décrit un document élaboré en vue de l'obtention d'un diplôme : thèse,
 * mémoire, dissertation, etc.
 *
 * - Dans le milieu universitaire, une thèse est un mémoire résumant un
 *   travail de recherche universitaire, soutenu devant un jury par un
 *   étudiant afin d'obtenir un diplôme ou un grade universitaire.
 *   @see http://fr.wikipedia.org/wiki/Th%C3%A8se
 *
 * - A thesis or dissertation is a document submitted in support of
 *   candidature for an academic degree or professional qualification
 *   presenting the author's research and findings
 *   @see http://en.wikipedia.org/wiki/Thesis
 *
 * Principales caractéristiques :
 * - écrit en vue d'obtenir un diplôme
 * - relié à une école, une fac, une université, etc.
 * - a un seul auteur physique
 * - peut avoir un ou plusieurs maitre de stage, directeur de thèse, etc.
 */
class Degree extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'degree',
            'label' => __('Mémoire ou thèse', 'docalist-biblio'),
            'description' => __('Un document élaboré en vue de l\'obtention d\'un diplôme.', 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Nature du document'],
                $fields['genre'],
                $fields['media'],

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Titres'],
                $fields['title'],
                $fields['othertitle'],
                // $fields['translation'],

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Auteurs'],
                $fields['author'],
                $fields['organisation'],

                // Date / Language / Pagination / Format
                'group4' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations bibliographiques'],
                $fields['date'],
                $fields['language'],
                $fields['number'],
                $fields['extent'],
                $fields['format'],

                // Topic / Abstract / Note
                'group5' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // // Liens et relations
                'group6' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group9' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],
            ]
        ];
        // @formatter:on
    }
}