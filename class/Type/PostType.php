<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Type\Text;

/**
 * Le PostType WordPress de la notice.
 */
class PostType extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Type de post WordPress', 'docalist-biblio'),
            'description' => __('Nom de code interne du type de post WordPress', 'docalist-biblio'),
        ];
    }
}
