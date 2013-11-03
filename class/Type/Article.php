<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

/**
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
class Article extends AbstractType {
    public function __construct() {
        // @formatter:off
        parent::__construct([
            'name' => 'article',
            'label' => __('Article de périodique', 'docalist-biblio'),
            'description' => __('Un article de presse publié dans un numéro de périodique.', 'docalist-biblio'),
            'fields' => [

                // Type, Genre, Media
                ['name' => 'group', 'label' => 'Nature du document'],
//                ['name' => 'type', 'table' => ['dclreftype']],
                ['name' => 'genre', 'table' => ['genres-article']], // interview, reportage, enquête
                ['name' => 'media', 'table' => ['medias']],

                // Title, OtherTitle, Translation
                ['name' => 'group', 'label' => 'Titres'],
                ['name' => 'title'],
                ['name' => 'othertitle', 'table' => ['dclreftitle'], 'split' => true], // à voir, pas de titre ens si type dossier
                ['name' => 'translation', 'table' => ['languages']],

                // Author, Organisation
                ['name' => 'group', 'label' => 'Auteurs'],
                ['name' => 'author', 'table' => ['dclrefrole'], 'format' => 'fmt1'],// dégraissée
                ['name' => 'organisation', 'table' => ['countries', 'dclrefrole']],

                // Journal, Issn, Volume, Issue
                ['name' => 'group', 'label' => 'Journal / Périodique'],
                ['name' => 'journal'],
                ['name' => 'issn'],
                ['name' => 'volume'],
                ['name' => 'issue'],
                ['name' => 'date'], // injecté à partir de l'issue

                // Date / Language / Pagination / Format
                ['name' => 'group', 'label' => 'Informations bibliographiques'],
                ['name' => 'language', 'table' => ['languages']],
                ['name' => 'pagination'],
                ['name' => 'format'],
                ['name' => 'doi'],
/*
                // Editor / Collection / Edition / Isbn
                ['name' => 'group', 'label' => 'Informations éditeur'],
                ['name' => 'editor'],
                ['name' => 'collection'],
                ['name' => 'edition'],
                ['name' => 'isbn'],
*/
                // Event / Degree
                ['name' => 'group', 'label' => 'Congrès et diplômes'],
//                ['name' => 'event'],
//                ['name' => 'degree'],

                // Topic / Abstract / Note
                ['name' => 'group', 'label' => 'Indexation et résumé'],
                ['name' => 'topic', 'table' => ['prisme', 'names', 'geo', 'free']],
                ['name' => 'abstract', 'table' => ['languages']],
                ['name' => 'note'],

                // Liens et relations
                ['name' => 'group', 'label' => 'Liens et relations'],
                ['name' => 'link'],
                ['name' => 'relations'],

                // Ref / Owner / Creation / Lastupdate
                ['name' => 'group', 'label' => 'Informations de gestion'],
                ['name' => 'ref'],
                ['name' => 'owner'],
                ['name' => 'creation'],
                ['name' => 'lastupdate'],
            ]
        ]);
        // @formatter:on
    }
}