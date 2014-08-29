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

/**
 * Article.
 *
 * Décrit un article de presse publié dans un numéro particulier d'un
 * périodique.
 *
 * - Un article est un texte qui relate un événement, présente des faits ou
 *   expose un point de vue. Il s'appuie pour cela sur différentes sources
 *   d'information orales ou écrites.
 *   @see http://fr.wikipedia.org/wiki/Article_de_presse
 *
 * - An article is a written work published in a print or electronic medium.
 *   It may be for the purpose of propagating the news, research results,
 *   academic analysis or debate.
 *   @see http://en.wikipedia.org/wiki/Article_(publishing)
 *
 * Principales caractéristiques :
 * - a un parent de type Issue
 * - écrit par un ou plusieurs auteurs physiques
 * - pas d'auteur organisme
 * - pagination de type "page de début - page de fin"
 */
class Article extends Reference {
    static protected function loadSchema() {
        $fields = parent::loadSchema()['fields'];

        // @formatter:off
        return [
            'name' => 'article',
            'label' => __('Article de périodique', 'docalist-biblio'),
            'description' => __('Un article de presse publié dans un numéro de périodique.', 'docalist-biblio'),
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

                // Journal, Number, Date, Edition
                'group4' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Journal / Périodique'],
                $fields['journal'],
                $fields['number'],
                $fields['date'],
                $fields['edition'],

                // Date / Language / Pagination / Format
                'group5' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations bibliographiques'],
                $fields['language'],
                $fields['extent'],
                $fields['format'],

                // Topic / Abstract / Note
                'group6' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Indexation et résumé'],
                $fields['topic'],
                $fields['content'],

                // Liens et relations
                'group7' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Liens et relations'],
                $fields['link'],
                $fields['relation'],

                // Ref / Owner / Creation / Lastupdate
                'group8' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'Informations de gestion'],
                $fields['type'],
                $fields['ref'],
                $fields['owner'],

                // ---------- enlever ce qui suit
                'group9' => ['type' => 'Docalist\Biblio\Entity\Reference\Group', 'label' => 'CHAMPS POUR TESTS, PAS DANS LA GRILLE ARTICLE'],
                $fields['editor'],
                $fields['collection'],
                $fields['event'],
            ]
        ];
        // @formatter:on
    }
}