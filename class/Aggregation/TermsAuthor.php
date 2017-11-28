<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2017 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Biblio\Field\Author;
use stdClass;

/**
 * Une agrégation standard de type "terms" sur le champ "media".
 */
class TermsAuthor extends TermsAggregation
{
    /**
     * Constructeur
     *
     * @param array $parameters     Autres paramètres de l'agrégation.
     * @param array $options        Options d'affichage.
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        $parameters['exclude'] = 'et al.¤';
        !isset($parameters['size']) && $parameters['size'] = 10;
        !isset($options['title']) && $options['title'] = __('Auteur', 'docalist-search');
        parent::__construct('author.filter', $parameters, $options);
    }

    public function getBucketLabel(stdClass $bucket)
    {
        if ($bucket->key === static::MISSING) {
            return $this->getMissingLabel();
        }

        // Le bucket est de la forme 'nom¤prénom' (cf. Reference::map)
        list($name, $firstname) = explode('¤', $bucket->key);
        $author = new Author(['name' => $name, 'firstname' => $firstname]);

        return $author->getFormattedValue(['format' => 'f n']); // ou 'n (f)'

    }
}
