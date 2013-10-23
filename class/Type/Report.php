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
 * Décrit un rapport.
 *
 * - Un rapport est un document analysant et évaluant un fonctionnement ou une
 *   activité, dans un ou plusieurs domaines, pour une période donnée.
 *   @see http://fr.wikipedia.org/wiki/Reporting
 *
 * - A report or account is any informational work made with the specific
 *   intention of relaying information or recounting certain events in a
 *   widely presentable form. Written reports are documents which present
 *   focused, salient content to a specific audience. Reports are often used
 *   to display the result of an experiment, investigation, or inquiry.
 *   The audience may be public or private, an individual or the public in
 *   general. Reports are used in government, business, education, science,
 *   and other fields.
 *   @see http://en.wikipedia.org/wiki/Report
 *
 * Principales caractéristiques :
 * - pas d'éditeur
 * - pas d'isbn
 */
class Report extends AbstractType {
    public function __construct() {
        // @formatter:off
        parent::__construct([
            'name' => 'report',
            'label' => __('Rapport', 'docalist-biblio'),
            'description' => __('Un rapport d\'activité ou une étude non publiée', 'docalist-biblio'),
            'fields' => [

                // Type, Genre, Media
                ['name' => 'group', 'label' => 'Nature du document'],
                ['name' => 'type', 'table' => ['dclreftype']],
                ['name' => 'genre', 'table' => ['dclrefgenre']],
                ['name' => 'media', 'table' => ['dclrefmedia']],

                // Title, OtherTitle, Translation
                ['name' => 'group', 'label' => 'Titres'],
                ['name' => 'title'],
                ['name' => 'othertitle', 'table' => ['dclreftitle'], 'split' => true],
                ['name' => 'translation', 'table' => ['dcllanguage']],

                // Author, Organisation
                ['name' => 'group', 'label' => 'Auteurs'],
                ['name' => 'author', 'table' => ['dclrefrole'], 'format' => 'fmt1'],
                ['name' => 'organisation', 'table' => ['dclcountry', 'dclrefrole']],

                // Journal, Issn, Volume, Issue
                ['name' => 'group', 'label' => 'Journal / Périodique'],
                ['name' => 'journal'],
                ['name' => 'issn'],
                ['name' => 'volume'],
                ['name' => 'issue'],

                // Date / Language / Pagination / Format
                ['name' => 'group', 'label' => 'Informations bibliographiques'],
                ['name' => 'date'],
                ['name' => 'language', 'table' => ['dcllanguage']],
                ['name' => 'pagination'],
                ['name' => 'format'],
                ['name' => 'doi'],

                // Editor / Collection / Edition / Isbn
                ['name' => 'group', 'label' => 'Informations éditeur'],
                ['name' => 'editor'],
                ['name' => 'collection'],
                ['name' => 'edition'],
                ['name' => 'isbn'],

                // Event / Degree
                ['name' => 'group', 'label' => 'Congrès et diplômes'],
                ['name' => 'event'],
                ['name' => 'degree'],

                // Topic / Abstract / Note
                ['name' => 'group', 'label' => 'Indexation et résumé'],
                ['name' => 'topic', 'table' => ['prisme', 'names', 'geo', 'free']],
                ['name' => 'abstract'],
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