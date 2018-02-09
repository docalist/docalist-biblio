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
 * Numéro de périodique.
 *
 * Décrit un numéro particulier d'un périodique.
 *
 * - A single instance of a periodically published journal, magazine, or
 *   newspaper.
 *   @see http://en.wikipedia.org/wiki/Issue
 *
 * Principales caractéristiques :
 * - a un parent de type Periodical
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class PeriodicalIssue extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'periodical-issue',
            'label' => __('Numéro de périodique', 'docalist-biblio'),
            'description' => __('Une parution d\'un périodique.', 'docalist-biblio'),
            'fields' => [
                'genre'         => ['unused' => true],
                'media'         => ['unused' => true],
                'othertitle'    => ['unused' => true],
                'translation'   => ['unused' => true],
                'author'        => ['unused' => true],
                'corporation'   => ['unused' => true],
                'journal'       => ['unused' => true],
                'language'      => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Titres', 'docalist-biblio')                         => 'number,title,context',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,edition,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
