<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\TableEntry;

/**
 * Champ "genre" : genre du document.
 *
 * Ce champ répétable permet d'indiquer des mots-clés qui décrivent la nature et les catégories stylistiques
 * auxquelles appartient le document catalogué (roman, essai, science-fiction, documentaire, législation...)
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:genres" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Genre extends TableEntry
{
    /*
     * Remarque : sur le fond, ce champ est juste un type particulier d'indexation (d'ailleurs la table par
     * défaut est un théaurus). On pourrait envisager de le supprimer et d'utiliser le champ "topic" à la place.
     * On garde un champ distinct pour le moment, à reconsidérer si un jour le champ topic supporte "explode" et
     * qu'on peut mettre l'indexation "genre" au bon endroit.
     */

    public static function loadSchema()
    {
        return [
            'name' => 'genre',
            'repeatable' => true,
            'label' => __('Genre', 'docalist-biblio'),
            'description' => __('Nature du document.', 'docalist-biblio'),
            'table' => 'thesaurus:genres',
        ];
    }
}
