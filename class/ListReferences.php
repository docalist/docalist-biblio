<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio;

/**
 * Page "Liste des notices" d'une base
 */
class ListReferences{
    /**
     * La base de données documentaire.
     *
     * @var Database
     */
    protected $database;

    /**
     *
     * @param Database $settings
     */
    public function __construct(Database $database) {
        $this->database = $database;
        $this->id = 'list-' . $database->postType();
    }
}