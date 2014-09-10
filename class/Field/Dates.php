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
namespace Docalist\Biblio\Field;

use Docalist\Biblio\Type\Repeatable;
use Docalist\Forms\Table;

/**
 * Une collection de dates.
 */
class Dates extends Repeatable {
    static protected $type = 'Date';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('date-type');
        $field->input('value')->addClass('date-value');

        return $field;
    }

    public function baseSettings() {
        $form = parent::baseSettings();
        return $this->addTableSelect($form, 'dates', __('Table des types de dates', 'docalist-biblio'));
    }

    public function editSettings() {
        $form = parent::editSettings();
        return $this->addTableSelect($form, 'dates', __('Table des types de dates', 'docalist-biblio'), true);
    }

    public function displaySettings() {
        $form = parent::displaySettings();
        return $this->addTableSelect($form, 'dates', __('Table des types de dates', 'docalist-biblio'), true);
    }
}