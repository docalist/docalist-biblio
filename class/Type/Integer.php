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

use Docalist\Forms\Input;

/**
 * Type de base pour tous les champs entiers
 */
class Integer extends \Docalist\Type\Integer implements BiblioField {
    use BiblioFieldTrait;

    public function editForm() {
        return new Input($this->schema->name());
    }

    public function format() {
        return (string) $this->value;
    }
}