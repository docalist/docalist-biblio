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
 * Législation.
 *
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
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Legislation extends Reference
{
    public static function loadSchema()
    {
        return [
            'name' => 'legislation',
            'label' => __('Législation', 'docalist-biblio'),
            'description' => __('Un texte législatif ou réglementaire.', 'docalist-biblio'),
            'fields' => [
                'othertitle'    => ['unused' => true],
                'translation'   => ['unused' => true],
                'editor'        => ['unused' => true],
                'collection'    => ['unused' => true],
                'event'         => ['unused' => true],
            ],
        ];
    }

    public static function getEditGrid()
    {
        return static::buildEditGrid([
            __('Nature du document', 'docalist-biblio')             => 'genre,media',
            __('Titres', 'docalist-biblio')                         => 'title',
            __('Auteurs', 'docalist-biblio')                        => 'author,corporation',
            __('Journal / Périodique', 'docalist-biblio')           => 'journal,number,edition',
            __('Informations bibliographiques', 'docalist-biblio')  => 'date,language,extent,format',
            __('Indexation et résumé', 'docalist-biblio')           => 'topic,content',
            __('Liens et relations', 'docalist-biblio')             => 'link,relation',
            __('Informations de gestion', 'docalist-biblio')        => '-,type,ref,owner',
        ]);
    }
}
