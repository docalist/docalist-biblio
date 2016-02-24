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

use Docalist\Search\Indexer\PostIndexer;
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Search\IndexManager;

/**
 * Un indexeur pour les notices d'une base.
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
        $this->database = $database;
    }

    public function getType()
    {
        return $this->database->postType();
    }

    public function getLabel()
    {
        return $this->database->label();
    }

    public function getCategory()
    {
        return __('Bases Docalist', 'docalist-biblio');
    }

    public function buildIndexSettings(array $settings)
    {
        $types = $this->database->settings()->types;
        foreach($types as $type) {  /* TypeSettings $type */
            $class = Database::getClassForType($type->name());
            $ref = new $class();
            $settings = $ref->buildIndexSettings($settings, $this->database);
        }

        return $settings;
    }

    protected function index($post, IndexManager $indexManager)
    {
        $ref = $this->database->fromPost($post);
        $esType = $this->database->postType() . '-' . $ref->type();

        $indexManager->index($this->getType(), $this->getID($post), $this->map($ref), $esType);
    }

    protected function remove($post, IndexManager $indexManager)
    {
        $ref = $this->database->fromPost($post);
        $esType = $this->database->postType() . '-' . $ref->type();

        $indexManager->delete($this->getType(), is_scalar($post) ? $post : $this->getID($post), $esType);
    }

    protected function map($ref) /* @var Type $ref */
    {
        $document = $ref->map();
        $document['database'] = $this->database->postType(); // mapping créé dans Type::buildIndexSettings()

        return $document;
    }
}
