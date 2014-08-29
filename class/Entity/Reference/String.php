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

use Docalist\Type\String as BaseString;
use Docalist\Forms\Input;
use Docalist\Forms\Fragment;

/**
 * Type de base pour tous les champs texte
 */
class String extends BaseString {
    use SettingsFormTrait;

    public function editForm() {
        return new Input($this->schema->name());
    }
}