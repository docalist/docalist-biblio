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
 * Mot de passe WordPress requis pour accéder à la fiche.
 */
class PostPassword extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Mot de passe', 'docalist-biblio'),
            'description' => __('Mot de passe WordPress requis pour consulter la fiche.', 'docalist-biblio'),
        ];
    }
}
