<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Type;

use Docalist\Biblio\Type\String;
use Docalist\Forms\Input;
use Docalist\Schema\Field;

/**
 * Une date/heure stockée sous forme de chaine au format 'yyyy-MM-dd HH:mm:ss'.
 *
 * Exemple : "2014-09-02 11:19:24"
 *
 * Utilisé pour les champs creation et lastupdate.
 */
class DateTime extends String {
    public function editForm() {
        $field = new Input($this->schema->name());

        return $field;
    }

    public function map(array & $doc) {
        $doc[$this->schema->name()] = $this->value();
    }

    public static function ESmapping(array & $mappings, Field $schema = null) {
        $mappings['properties'][$schema->name()] = [
            'type' => 'date',
            'format' => 'yyyy-MM-dd HH:mm:ss',
            'ignore_malformed' => true,
        ];
    }
}