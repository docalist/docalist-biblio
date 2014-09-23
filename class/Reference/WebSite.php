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
 * Décrit un site web.
 *
 * - Un site web est un ensemble de pages web reliées entre elles et
 *   accessible à une adresse web.
 *   @see http://fr.wikipedia.org/wiki/Site_web
 *
 * - A website is a set of related web pages served from a single web domain.
 *   A website is hosted on at least one web server, accessible via a network
 *   such as the Internet or a private local area network through an URL.
 *   @see http://en.wikipedia.org/wiki/Web_site
 *
 * Principales caractéristiques :
 * - a une URL
 * - a un organisme ou une personne auteur
 */
class WebSite extends Reference {
    static protected function loadSchema() {
        // Récupère les champs d'une référence standard
        $fields = parent::loadSchema()['fields'];

        // Supprime les champs qu'on n'utilise pas
        unset($fields['media']);

        // Personnalise les tables, les libellés, les description, etc.
        // todo

        // Contruit notre schéma
        return [
            'name' => 'website',
            'label' => __('Site web', 'docalist-biblio'),
            'description' => __('Un site web.', 'docalist-biblio'),
            'fields' => $fields,
        ];
    }

    static public function editGrid() {
        return new Schema([
            'label' => __('Formulaire de saisie', 'docalist-biblio'),
            'description' => __("Grille de saisie d'un site web.", 'docalist-biblio'),
            'fields' => [
                // Type, Genre, Media
                'group1' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Nature du document'],
                'genre',

                // Title, OtherTitle, Translation
                'group2' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Titres'],
                'title',
                'othertitle',
                'translation',

                // Author, Organisation
                'group3' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Auteurs'],
                'author',
                'organisation',

                // Journal, Number
                'group4' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Journal / Périodique'],
                'journal',
                'number',

                // Date / Language / Pagination / Format
                'group5' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations bibliographiques'],
                'date',
                'language',
                'extent',
                'format',

                // Editor / Collection / Edition
                'group6' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations éditeur'],
                'editor',
                'collection',
                'edition',

                // Event
                'group7' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Congrès et diplômes'],
                'event',

                // Topic / Abstract / Note
                'group8' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Indexation et résumé'],
                'topic',
                'content',

                // // Liens et relations
                'group9' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Liens et relations'],
                'link',
                'relation',

                // Ref / Owner / Creation / Lastupdate
                'group10' => ['type' => 'Docalist\Biblio\Type\Group', 'label' => 'Informations de gestion'],
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