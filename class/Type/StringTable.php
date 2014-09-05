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
use Docalist\Schema\Field;

/**
 * Un champ texte avec une table associée (genre, media, languages, format, etc.)
 */
class StringTable extends String {
    public function format() {
        return $this->label();
    }

    /**
     * Retourne le libellé associé à la valeur du champ.
     *
     * @return string
     */
    public function label() {
        return $this->lookup($this->value);
    }
}