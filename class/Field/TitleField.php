<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Data\Field\TitleField as BaseTitle;

/**
 * Champ "title" : titre original du document catalogué.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TitleField extends BaseTitle
{
    public static function loadSchema()
    {
        return [
            'description' => __('Titre original du document.', 'docalist-biblio'),
        ];
    }
}
