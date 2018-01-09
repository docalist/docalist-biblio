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
 * Mention d'édition.
 *
 * Ce champ permet de préciser s'il s'agit d'une nouvelle édition, d'une édition revue et corrigée, d'une version
 * expurgée, etc.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Edition extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __("Mentions d'édition", 'docalist-biblio'),
            'description' => __(
                "Nouvelle édition, édition revue et corrigée, version expurgée...",
                'docalist-biblio'
            ),
        ];
    }
}
