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
 * Genre du document.
 *
 * Ce champ permet de préciser la nature du document catalogué : pour un livre, par exemple, il permet d'indiquer
 * s'il s'agit d'un roman, d'un essai, etc.
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:genres" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Genre extends TableEntry
{
    public static function loadSchema()
    {
        return [
            'label' => __('Genre', 'docalist-biblio'),
            'description' => __('Nature du document.', 'docalist-biblio'),
            'table' => 'thesaurus:genres',
        ];
    }
}
