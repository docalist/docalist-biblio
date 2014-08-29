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
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Forms\Table;

/**
 * Une liste de contenus.
 */
class Contents extends Repeatable {
    use SettingsFormTrait;

    static protected $type = 'Content';

    public function editForm() {
        $field = new Table($this->schema->name());
        $field->TableLookup('type', $this->schema->table())
              ->addClass('content-type');
        $field->textarea('value')->addClass('content-value');

        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des types de contenus", 'docalist-biblio'))
             ->options($this->tablesOfType('content'));

        return $form;
    }
}