<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Biblio\Field;

use Docalist\Type\Composite;

// A supprimer une fois que adb et prisme auront été migrés

/**
 * Une erreur.
 *
 * @property Docalist\Type\Text $code
 * @property Docalist\Type\Text $value
 * @property Docalist\Type\Text $message
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ErrorField extends Composite
{
    public static function loadSchema()
    {
        return [
            'fields' => [
                'code' => 'Docalist\Type\Text',
                'value' => 'Docalist\Type\Text',
                'message' => 'Docalist\Type\Text',
            ],
        ];
    }
}
