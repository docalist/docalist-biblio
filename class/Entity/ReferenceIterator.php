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
namespace Docalist\Biblio\Entity;

use Docalist\Search\SearchRequest;
use Iterator;

/**
 * Un itérateur de références (pour l'export).
 *
 */
class ReferenceIterator implements Iterator {

    /**
     * La requête en cours.
     *
     * @var SearchRequest
     */
    protected $request;

    /**
     * Les hits de la page actuelle.
     *
     * @var array
     */
    protected $hits;

    /**
     * L'index du hit en cours
     *
     * @var int
     */
    protected $current;

    /**
     * Indique si l'itérateur retourne des objets Reference (true) ou les
     * données brutes (false).
     *
     * @var boolean
     */
    protected $raw;

    /**
     * Construit l'itérateur.
     *
     * @param SearchRequest $request
     * @param boolean $raw Par défaut (true), l'itérateur retourne des objets
     * Reference. Si raw est à false, l'itérateur retournera un tableau
     * contenant les données brutes.
     */
    public function __construct(SearchRequest $request, $raw = false) {
        $this->request = $request;
        $this->raw = $raw;
    }

    public function rewind() {
        $this->loadPage(1);
    }

    public function valid() {
        return $this->current < count($this->hits);
    }

    public function current() {
        return docalist('docalist-biblio')->getReference($this->key(), $this->raw);
    }

    public function key() {
        return $this->hits[$this->current]->_id;
    }

    public function next() {
        ++$this->current;
        if (! $this->valid()) {
            $this->loadPage($this->request->page() + 1);
        }
    }

    /**
     * Charge une page de résultats.
     *
     * @param int $page La page à charger.
     */
    protected function loadPage($page) {
        $this->request->page($page);
        var_dump($this->request->execute()); die();
        $this->hits = $this->request->execute()->hits();
        $this->current = 0;
    }

    /**
     * Retourne la requête docalist-search en cours.
     *
     * @return SearchRequest
     */
    public function searchRequest() {
        return $this->searchRequest();
    }
}