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
namespace Docalist\Biblio\Field;

use Docalist\Type\Text;

/**
 * Le titre de la notice.
 */
class Title extends Text
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

    public function getEditorForm($options = null)
    {
        return parent::getEditorForm($options)->addClass('large-text');
    }
}
