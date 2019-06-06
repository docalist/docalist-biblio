<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Field;

use Docalist\Data\Type\IndexableTableEntry;

/**
 * Champ "genre" : mots-clés décrivant le genre et la nature du document catalogué.
 *
 * Ce champ répétable permet d'indiquer des mots-clés qui décrivent la nature et les catégories stylistiques
 * auxquelles appartient le document catalogué (roman, essai, science-fiction, documentaire, législation...)
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:genres" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class GenreField extends IndexableTableEntry
{
    /*
     * Remarque : sur le fond, ce champ est juste un type particulier de topic (d'ailleurs la table par
     * défaut est un thésaurus). On pourrait envisager de le supprimer et d'utiliser le champ "topic" à la place.
     * On garde un champ distinct pour le moment, à reconsidérer si un jour le champ topic supporte "explode" et
     * qu'on peut mettre l'indexation "genre" au bon endroit.
     */

    /**
     * {@inheritDoc}
     */
    public static function loadSchema(): array
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
