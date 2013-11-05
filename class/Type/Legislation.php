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
class Legislation extends AbstractType {
    public function __construct() {
        // @formatter:off
        parent::__construct([
            'name' => 'legislation',
            'label' => __('Législation', 'docalist-biblio'),
            'description' => __('Un texte législatif ou réglementaire.', 'docalist-biblio'),
            'fields' => [

                // Type, Genre, Media
                ['name' => 'group', 'label' => 'Nature du document'],
//                ['name' => 'type', 'table' => ['dclreftype']],
                ['name' => 'genre', 'table' => ['genres-legislation']],
                ['name' => 'media', 'table' => ['medias']],

                // Title, OtherTitle, Translation
                ['name' => 'group', 'label' => 'Titres'],
                ['name' => 'title'],
//                ['name' => 'othertitle', 'table' => ['titles']],
//                ['name' => 'translation', 'table' => ['languages']],

                // Author, Organisation
                ['name' => 'group', 'label' => 'Auteurs'],
                ['name' => 'author', 'table' => ['roles-author'], 'format' => 'fmt1'],
                ['name' => 'organisation', 'table' => ['countries', 'roles-organisation']],

                // Journal, Issn, Volume, Issue
                ['name' => 'group', 'label' => 'Journal / Périodique'],
                ['name' => 'journal'],
                ['name' => 'issn'],
                ['name' => 'volume'],
                ['name' => 'issue'],

                // Date / Language / Pagination / Format
                ['name' => 'group', 'label' => 'Informations bibliographiques'],
                ['name' => 'date'],
                ['name' => 'language', 'table' => ['languages']], // fre par défaut
                ['name' => 'pagination'],
                ['name' => 'format'],
//                ['name' => 'doi'],

// numéro de texte officiel

                // Editor / Collection / Edition / Isbn
//                 ['name' => 'group', 'label' => 'Informations éditeur'],
//                 ['name' => 'editor'],
//                 ['name' => 'collection'],
//                 ['name' => 'edition'],
//                 ['name' => 'isbn'],

                // Event / Degree
//                ['name' => 'group', 'label' => 'Congrès et diplômes'],
//                ['name' => 'event'],
//                ['name' => 'degree'],

                // Topic / Abstract / Note
                ['name' => 'group', 'label' => 'Indexation et résumé'],
                ['name' => 'topic', 'table' => ['prisme', 'names', 'geo', 'free']],
                ['name' => 'abstract', 'table' => ['languages']],
                ['name' => 'note', 'table' => ['notes']],

                // Liens et relations
                ['name' => 'group', 'label' => 'Liens et relations'],
                ['name' => 'link', 'table' => ['links']],
                ['name' => 'relations', 'table' => ['relations']],

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