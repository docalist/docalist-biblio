<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Entity;

use Docalist\Biblio\Entity\ReferenceEntity;

/**
 * Un rapport.
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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReportEntity extends ReferenceEntity
{
    public static function loadSchema()
    {
        return [
            'name' => 'report',
            'label' => __('Rapport', 'docalist-biblio'),
            'description' => __('Un rapport d\'activité ou une étude non publiée', 'docalist-biblio'),
            'fields' => [
                'othertitle'    => ['unused' => true],
                'journal'       => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
                'edition'       => ['unused' => true],
                'context'       => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Type de rapport', 'docalist-biblio')                => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title,translation',
            __('Auteurs', 'docalist-biblio')                        => 'corporation,author',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,number,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
