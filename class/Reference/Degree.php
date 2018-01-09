<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Reference;

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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Degree extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'degree',
            'label' => __('Mémoire ou thèse', 'docalist-biblio'),
            'description' => __('Un document élaboré en vue de l\'obtention d\'un diplôme.', 'docalist-biblio'),
            'fields' => [
                'translation'   => ['unused' => true],
                'journal'       => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
                'edition'       => ['unused' => true],
                'event'         => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Nature du document', 'docalist-biblio')             => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title,othertitle',
            __('Auteurs', 'docalist-biblio')                        => 'author,organisation',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,number,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
