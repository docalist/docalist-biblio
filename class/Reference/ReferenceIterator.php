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
namespace Docalist\Biblio\Reference;

use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResults;
use Iterator, Countable;
use Docalist\Biblio\Reference;

/**
 * Un itérateur de références (pour l'export).
 *
 */
class ReferenceIterator implements Iterator, Countable {

    /**
     * La requête en cours.
     *
     * @var SearchRequest
     */
    protected $request;

    /**
     * Les résultats en cours.
     *
     * @var SearchResults
     */
    protected $results;

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
     * Indique la grille à utiliser pour charger les notices.
     *
     * @var string|null Le nom de la grille à utiliser (base, edit...) pour que
     * l'itérateur retourne des objets Reference ou null pour qu'il retourne
     * un tableau contenant les données brutes de la notice.
     */
    protected $grid;

    /**
     * Nombre maximum de notices à itérer.
     *
     * @var int
     */
    protected $limit;

    /**
     * Nombre de hits déjà itérés.
     *
     * @var int
     */
    protected $count;

    /**
     * Construit l'itérateur.
     *
     * @param SearchRequest $request
     * @param boolean $grid Le nom de la grille à utiliser (base, edit...) pour
     * que l'itérateur retourne des objets Reference ou null pour qu'il retourne
     * un tableau contenant les données brutes de la notice.
     * @param int $limit Nombre maximum de notices à itérer.
     */
    public function __construct(SearchRequest $request, $grid = null, $limit = null) {
        $this->request = $request;
        $this->grid = $grid;
        $this->limit = $limit;
        $this->count = 0;
    }

    public function rewind() {
        $this->loadPage(1);
    }

    public function valid() {
        if ($this->current >= count($this->hits)) {
            return false;
        }

        if ($this->limit && $this->count >= $this->limit) {
            return false;
        }

        return true;
    }

    public function current() {
        return docalist('docalist-biblio')->getReference($this->key(), $this->grid);
    }

    public function key() {
        return $this->hits[$this->current]->_id;
    }

    public function next() {
        ++$this->current;
        ++$this->count;
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
        $this->results = $this->request->execute();
        $this->hits = $this->results->hits();
        $this->current = 0;
    }

    /**
     * Retourne la requête docalist-search en cours.
     *
     * @return SearchRequest
     */
    public function searchRequest() {
        return $this->request;
    }

    /**
     * Retourne le nombre de notices dans l'itérateur.
     *
     * @return int
     */
    public function count() {
        is_null($this->results) && $this->rewind();
        return $this->results->total();
    }
}