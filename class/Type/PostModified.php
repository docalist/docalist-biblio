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

use Docalist\Type\DateTime;

/**
 * La date de dernière modification de la notice.
 */
class PostModified extends DateTime
{
    public static function loadSchema()
    {
        return [
            'label' => __('Dernière modification', 'docalist-biblio'),
            'description' => __('Date/heure de dernière modification de la fiche.', 'docalist-biblio'),
        ];
    }
}
