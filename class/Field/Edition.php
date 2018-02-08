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

use Docalist\Type\Text;

/**
 * Champ "edition" : mentions permettant d'identifier un tirage ou une version spécifique du document catalogué.
 *
 * Ce champ répétable permet de préciser s'il s'agit d'une nouvelle édition, d'une édition revue et corrigée,
 * d'une version expurgée, etc.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Edition extends Text
{
    public static function loadSchema()
    {
        return [
            'name' => 'edition',
            'repeatable' => true,
            'label' => __("Mentions d'édition", 'docalist-biblio'),
            'description' => __(
                "Mentions permettant d'identifier un tirage ou une version spécifique du document catalogué
                (nouvelle édition, version expurgée...)",
                'docalist-biblio'
            ),
        ];
    }
}
