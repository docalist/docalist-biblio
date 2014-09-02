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
use Docalist\Forms\TableLookup;

/**
 * Une collection de traductions du titre.
 */
class Languages extends Repeatable {
    static protected $type = 'Language';

    public function editForm() {
        $field = new TableLookup($this->schema->name(), $this->schema->table());
        $field->multiple(true);


        return $field;
    }

    public function settingsForm() {
        $form = parent::settingsForm();
        $form->select('table')
             ->label(__("Table des langues", 'docalist-biblio'))
             ->options($this->tablesOfType('languages'));

        return $form;
    }

    public function map(array & $doc) {
        // Ouvre la table utilisée par ce champ pour convertir les codes en libellés
        $table = $this->openTable();

        foreach($this->value as $item) {
            $item = $item->value();
            $label = $table->find('label', sprintf('code="%s"', $item));
            $label === false && $label = '';

            $doc['language'][] = $item . '¤' . $label;
        }
    }
}