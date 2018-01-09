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

use Docalist\Biblio\Type\PostTitle;

/**
 * Le titre de la notice.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Title extends PostTitle
{
    public static function loadSchema()
    {
        return [
            'label' => __('Titre', 'docalist-biblio'),
            'description' => __(
                'Titre original du document.',
                'docalist-biblio'
            ),
        ];
    }
}
