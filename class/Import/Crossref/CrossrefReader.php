<?php declare(strict_types=1);
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Import\Crossref;

use Docalist\Json\JsonReader;
use Docalist\Json\JsonParseException;
use Docalist\Data\Import\Reader;

/**
 * Lit un fichier JSON généré par l'API Crossref pour des Works.
 *
 * Exemple : https://api.crossref.org/works?query=incest&rows=10
 * {
 *     "status": "ok",
 *     "message-type": "work-list",
 *     "message-version": "1.0.0",
 *     "message":
 *     {
 *         "facets": { },
 *         "totals-results": 100,
 *         "items":
 *         [
 *             { ... },
 *             { ... }
 *         ],
 *         "items-per-page": 20,
 *         "query":
 *         {
 *             "start-index": 0,
 *             "search-terms": "incest"
 *         }
 *     }
 * }
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class CrossrefReader implements Reader
{
    public function getRecords(string $filename): Iterable
    {
        // Ouvre le fichier JSON
        $json = new JsonReader($filename);

        // Entre dans l'objet root et recherche la clé "message"
        $json->get('{');
        $this->findKey($json, 'message');

        // Entre dans l'objet message et recherche la clé "items"
        $json->get('{');
        $this->findKey($json, 'items');

        // Items doit être un tableau
        $json->get('[');

        // Génère une valeur pour chaque item du tableau
        while (!$json->is(']')) {
            yield $json->getObject(true);
            $json->is(',') && $json->get(',');
        }

        // Fin du tableau
        $json->get(']');

        // Ignore le reste du fichier
        unset($json);
    }

    /**
     * Recherche la clé indiquée dans l'objet en cours.
     *
     * @param JsonReader $json En entrée, le fichier JSON doit être positionné sur la première clé d'un objet.
     * En sortie, le fichier json est positionné sur la valeur associée à la clé recherchée (si on a trouvé la clé).
     *
     * @param string $key Clé recherchée
     *
     * @return array Les clés et les valeurs qui figurent avant la clé recherchée.
     *
     * @throws JsonParseException Si la clé demandée n'est pas trouvée (i.e. on a atteint '}').
     */
    protected function findKey(JsonReader $json, string $key)
    {
        $meta = [];

        while (!$json->is('}')) {
            $found = $json->getString();
            $json->get(':');
            if ($key === $found) {
                return $meta;
            }

            $meta[$found] = $json->getValue();
            $json->is(',') && $json->get(',');
        }

        // Génère une exception (on est sur '}', on demande à lire la clé, on sait que ça va échouer)
        $json->get($key);
    }
}
