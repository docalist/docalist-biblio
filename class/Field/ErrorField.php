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
class Error extends Composite {
    static public function loadSchema() {
        return [
            'fields' => [
                'code' => 'Docalist\Type\Text',
                'value' => 'Docalist\Type\Text',
                'message' => 'Docalist\Type\Text',
            ],
        ];
    }
/*
    public function setupMapping(MappingBuilder $mapping)
    {
        $mapping->addField('error')->text()->filter();
    }

    public function mapData(array & $document) {
        $document['error'][] = $this->message();
    }
*/
}
