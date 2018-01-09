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
 * Support du document.
 *
 * Ce champ permet d'indiquer le support physique sur lequel réside le document catalogué : pour un livre, par
 * exemple, il permet d'indiquer s'il s'agit d'un livre broché, d'un DVD, d'une clé USB, etc.
 *
 * Le champ est associé à une table d'autorité qui indique les valeurs possibles ("thesaurus:medias" par défaut).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Media extends TableEntry
{
    public static function loadSchema()
    {
        return [
            'label' => __('Support', 'docalist-biblio'),
            'description' => __('Support physique du document (imprimé, numérique, dvd...)', 'docalist-biblio'),
            'table' => 'thesaurus:medias',
        ];
    }
}
