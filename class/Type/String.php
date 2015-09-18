<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Type;

use Docalist\Forms\Input;

/**
 * Type de base pour tous les champs texte
 */
class String extends \Docalist\Type\Text implements BiblioField {
    use BiblioFieldTrait;

    public function editForm() {
        return new Input($this->schema->name());
    }

    public function format() {
        return $this->value;
    }
}