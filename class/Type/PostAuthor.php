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
use WP_User;

/**
 * L'auteur WordPress de la notice (login).
 */
class PostAuthor extends Text
{
    public static function loadSchema()
    {
        return [
            'label' => __('Créé par', 'docalist-biblio'),
            'description' => __("Nom de login de l'utilisateur WordPress qui a créé la fiche.", 'docalist-biblio'),
        ];
    }

    public function getFormattedValue($options = null)
    {
        $author = get_user_by('id', $this->getPhpValue()); /** @var WP_User $author */

        return $author->display_name;
    }
}
