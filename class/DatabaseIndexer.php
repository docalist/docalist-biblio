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
 * @version     $Id$
 */
namespace Docalist\Biblio;

use Docalist\Search\PostIndexer;
use Docalist\Search\MappingBuilder;

/**
  * Un indexeur pour les notices d'une base documentaire.
 */
class DatabaseIndexer extends PostIndexer {
    /**
     * La base de données indexée.
     *
     * @var Database
     */
    protected $database;

    /**
     * Construit l'indexeur.
     *
     * @param Database $database La base à indexer.
     */
    public function __construct(Database $database) {
        parent::__construct($database->postType());
        $this->database = $database;
    }

    public function mapping() {
        // Crée une référence vide
        $type = $this->database->type();
        $ref = new $type(); /* @var $ref Reference */

        // Récupère l'analyseur par défaut pour les champs texte (dans les settings)
        $defaultAnalyzer = $this->database->settings()->stemming();

        // Construit le mapping
        $mappingBuilder = new MappingBuilder($defaultAnalyzer);
        foreach($ref->schema()->fieldNames() as $field) {
            $ref->$field->mapping($mappingBuilder, $this);
        }

        // Ok
        return $mappingBuilder->mapping();
    }

    public function map($post) {
        // Crée la référence à partir des données du post passé en paramètre
        $ref = $this->database->fromPost($post);

        // Mappe la notice
        $document = [];
        foreach($ref->fields() as $field) { /* @var $field BiblioField */
            $field->map($document, $this);
        }

        return $document;
    }
}