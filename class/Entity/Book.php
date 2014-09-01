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
 * Livre.
 *
 * Décrit un livre.
 *
 * - Un livre est un document écrit formant une unité et conçu comme tel
 *   composé de pages en papier ou en carton reliées les unes aux autres.
 *   @see http://fr.wikipedia.org/wiki/Livre_(document)
 *
 * - A book is a set of written, printed, illustrated, or blank sheets, made
 *   of ink, paper, parchment, or other materials, usually fastened together
 *   to hinge at one side
 *   @see http://en.wikipedia.org/wiki/Book
 *
 * Principales caractéristiques :
 * - a un éditeur (un diffuseur, etc.)
 * - a un isbn
 * - a un ou plusieurs auteurs physiques
 * - a un ou plusieurs auteurs moraux
 * - pagination de type "nombre de pages"
 */
class Book extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'book',
            'label' => __('Livre', 'docalist-biblio'),
            'description' => __('Un livre publié par un éditeur.', 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Nature du document'],
                $fields['genre'],
                $fields['media'],

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Titres'],
                $fields['title'],
                $fields['othertitle'],
                $fields['translation'],

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

                // Editor / Collection / Edition
                'group5' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations éditeur'],
                $fields['editor'],
                $fields['collection'],
                $fields['edition'],

                // Event
                'group6' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Congrès et diplômes'],
                $fields['event'],

                // Topic / Abstract / Note
                'group7' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // // Liens et relations
                'group8' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Liens et relations'],
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