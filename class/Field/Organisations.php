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
 * Une collection d'organismes.
 */
class Organisations extends Repeatable {
    static protected $type = 'Organisation';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->input('name')->addClass('organisation-name');
        $field->input('acronym')->addClass('organisation-acronym');
        $field->input('city')->addClass('organisation-city');
        $field->TableLookup('country', $this->schema->table())
              ->addClass('organisation-country');
        $field->TableLookup('role', $this->schema->table2())
              ->addClass('organisation-role');

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des pays", 'docalist-biblio'))
             ->options($this->tablesOfType('countries'));
        $form->select('table2')
             ->label(__("Table des rôles", 'docalist-biblio'))
             ->options($this->tablesOfType('roles'));

        return $form;
    }
}