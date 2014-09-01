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

use Docalist\Forms\Fragment;

trait SettingsFormTrait {
    public function settingsForm() {
        // Champs communs
        $name = $this->schema->name();
        $form = new Fragment($name);
        $form->hidden('name')
             ->attribute('class', 'name');
        $form->input('label')
             ->attribute('id', $name . '-label')
             ->attribute('class', 'label regular-text')
             ->label(__('Libellé', 'docalist-biblio'));
        $form->textarea('description')
             ->attribute('id', $name . '-description')
             ->attribute('class', 'description large-text')
             ->attribute('rows', 2)
             ->label(__('Description', 'docalist-biblio'));

        return $form;
    }

    protected function tablesOfType($type) {
        /* @var $tableManager TableManager */
        $tableManager = docalist('table-manager');

        /* @var $tableInfo TableInfo */
        $tables = [];
        foreach($tableManager->info(null, $type) as $name => $tableInfo) {
            if ($tableInfo->format() !== 'conversion') {
                $key = $tableInfo->format() . ':' . $name;
                $tables[$key] = sprintf('%s (%s)', $tableInfo->label(), $name);
            }
        }

        return $tables;
    }

}