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
namespace Docalist\Biblio;

use Docalist\Search\PostIndexer;
use Docalist\MappingBuilder;

/**
 * Un indexeur pour les notices d'une base documentaire.
 */
class DatabaseIndexer extends PostIndexer
{
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
    public function __construct(Database $database)
    {
        parent::__construct($database->postType());
        $this->database = $database;
    }

    public function getMapping()
    {
        // Crée une référence vide
        $type = $this->database->type();
        $ref = new $type(); /* @var $ref Reference */

        // Récupère l'analyseur par défaut pour les champs texte (dans les settings)
        $defaultAnalyzer = $this->database->settings()->stemming();

        // Construit le mapping
        $mapping = docalist('mapping-builder'); /* @var MappingBuilder $mapping */
        $mapping->reset()->setDefaultAnalyzer($defaultAnalyzer);
        $ref->setupMapping($mapping);

        // Ok
        return $mapping->getMapping();
    }

    public function map($post)
    {
        // Crée la référence à partir des données du post passé en paramètre
        if ($post instanceof Reference) {
            $ref = $post;
        } else {
            $ref = $this->database->fromPost($post);
        }

        // Mappe la notice
        $document = [];
        $ref->mapData($document);

        return $document;
    }
}
