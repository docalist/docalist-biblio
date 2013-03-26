<?php
/**
* @package     Prisme
* @subpackage  modules
* @author      Daniel Ménard <daniel.menard.35@gmail.com>
* @version     SVN: $Id$
*/
namespace Docalist\Biblio\Import;

use Iterator;
use stdClass;

/**
 * Classe permettant de lire les fichiers Prisme au format texte tabulé.
 *
 * Cette classe implémente l'interface Iterator. L'utilisation classique est une simple boucle :
 * <code>
 * foreach(new PrismeReader($fileToLoad) as $index => $record)
 * {
 *     // chargement du index-ième enregistrement
 *     // $record est un tableau de la forme nom de champ => contenu du champ
 * }
 *
 * </code>
 *
 * @package     Prisme
 * @subpackage  modules
 * @author      Daniel Ménard <daniel.menard.35@gmail.com>
 */
class Prisme implements Iterator
{
    /**
     * Délimiteur utilisé pour marquer les fins de ligne (les fins d'enregistrement).
     *
     * @var string
     */
    const RECORD_DELIMITER = '+++';

    /**
     * Délimiteur utilisé entre les champs.
     *
     * @var string
     */
    const FIELD_DELIMITER = ';;';

    /**
     * Longueur maximale d'un enregistrement.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 65536;

    /**
     * Path du fichier en cours.
     *
     * @var string
     */
    private $path;

    /**
     * Handle du fichier en cours.
     *
     * @var resource
     */
    protected $file;

    /**
     * Noms des champs.
     *
     * @var array
     */
    protected $headers;

    /**
     * Données de l'enregistrement en cours.
     *
     * @var array
     */
    protected $data;

    /**
     * Numéro de l'enregistrement en cours.
     *
     * @var int
     */
    protected $recordNumber;

    /**
     * Flag indiquant si la fin de fichier a été atteinte
     *
     * @var bool
     */
    protected $eof;

    /**
     * Indique s'il faut convertir les données de l'ancien format
     * (éclatement des champs Url, So, etc.)
     *
     * @var bool
     */
    protected $conversion = true;

    protected $fieldIndex;
    protected $handles = array();

//        $data = stream_get_line($this->file, self::MAX_LINE_LENGTH, self::RECORD_DELIMITER);

    protected $cache;

    // workaround pour le bug #63240 de php sur stream_get_line
    protected function stream_get_line() {
        while (false === $pt = strpos($this->cache, self::RECORD_DELIMITER)) {
            if (feof($this->file)) {
                $result = $this->cache;
                $this->cache = '';

                return $result;
            }

            $this->cache .= fread($this->file, self::MAX_LINE_LENGTH);
        }

        $result = substr($this->cache, 0, $pt);
        $this->cache = substr($this->cache, $pt + strlen(self::RECORD_DELIMITER));

        return $result;
    }

    /**
     * Constructeur. Ouvre le fichier passé en paramètre.
     *
     * @param string $path
     */
    public function __construct($path, $conversion = true, $fieldIndex = true)
    {
        $this->conversion = $conversion;
        $this->path = $path;
        $this->file = fopen($path, 'rb');
        $this->fieldIndex = $fieldIndex;
    }


    /**
     * Destructeur. Ferme le fichier en cours.
     */
    public function __destruct()
    {
        fclose($this->file);
        foreach($this->handles as $file) {
            fclose($file);
        }
    }


    /**
     * Lit une ligne du fichier et retourne un tableau contenant les données.
     *
     * @return array|false retourne un tableau contenant les données ou false si la fin
     * du fichier est atteinte (dans ce cas, la propriété $eof est mise à true).
     */
    protected function read()
    {
        if (feof($this->file))
        {
            $this->eof = true;
            return false;
        }
//        $data = stream_get_line($this->file, self::MAX_LINE_LENGTH, self::RECORD_DELIMITER);
        $data = $this->stream_get_line();
        $data = utf8_encode($data);
        $data = explode(self::FIELD_DELIMITER, $data);
        return $data;
    }


    /**
     * Interface Iterator. Initialise une boucle foreach.
     *
     * Charge la ligne d'entête du fichier, initialise le numéro de l'enregistrement en cours et
     * le flag de fin de fichier puis charge le premier enregistrement.
     */
    function rewind()
    {
        fseek($this->file, 0);
        $this->headers = $this->read();
        $this->recordNumber = 0;
        $this->eof = false;
        $this->next();
    }


    /**
     * Interface Iterator. Charge le prochain enregistrement du fichier.
     *
     */
    function next()
    {
        $this->data = $this->read();
        if ($this->data === false) return;

        // Détection d'erreurs
        if (count($this->data) !== count($this->headers)) {
            echo "<p>Notice erronnée dans le fichier CSV : le nombre de champs dans la notice ne correspond pas au nombre de champs dans la ligne d'entête</p>";
            echo '<table border="1"><tr><th>Entête</th><th>Notice</th></tr>';
            $max = max(count($this->data), count($this->headers));
            for($i=0; $i < $max; $i++) {
                echo '<tr>';
                echo '<th>', isset($this->headers[$i]) ? $this->headers[$i] : '-', '</th>';
                echo '<td>', isset($this->data[$i])    ? $this->data[$i]    : '-', '</td>';
                echo '</tr>';
            }
            echo '</table>';
            return $this->next();
        }

        $this->data = array_combine($this->headers, $this->data);
        ++$this->recordNumber;

        // Supprime les champs vides
        $this->data = array_map('trim', $this->data); // trim all
        $this->data = array_filter($this->data, 'strlen'); // ne garde que ceux dont len !== 0
        if ($this->fieldIndex) {
            foreach($this->data as $key=>$value) {
                if (! isset($this->handles[$key])) {
                    $path = $this->path . "-$key.txt";
                    $this->handles[$key] = fopen($path, 'w');
                }
                fwrite($this->handles[$key], "$value\r\n");
            }
        }

        if ($this->conversion) $this->data = $this->convert();
    }


    /**
     * Interface Iterator. Indique si la fin de fichier a été atteinte.
     *
     * @return bool
     */
    function valid()
    {
        return ! $this->eof;
    }


    /**
     * Interface Iterator. Retourne le numéro d'enregistrement en cours.
     *
     * @return int
     */
    function key()
    {
        return $this->recordNumber;
    }


    /**
     * Interface Iterator. Retourne les données de l'enregistrement en cours.
     *
     *  @return array
     */
    function current()
    {
        return $this->data;
    }
    protected $errors;
    protected function error($message) {
        $this->errors[] = $message;
    }

    public function convert()
    {
        // Réinitialise a liste des erreurs rencontrées
        $this->errors = array();

        // Commence à créer l'enreg
        $doc = new StdClass;

        // Stocke la notice d'origine
        $imported = '';
        foreach($this->data as $key=>$value) {
            $imported .= "$key:$value\n";
        }

        // Convertit les champs articles en tableaux de valeurs
        $repeatable = array_flip(explode(' ', 'AU AUCO AS GO HI DENP DE CD')); // , 'LA', 'DO'
        foreach($this->data as $name => & $value)
        {
            if (empty($value))
            {
                unset($this->data[$name]);
            }
            elseif (isset($repeatable[$name]))
            {
                $value = array_map('trim', explode(',', $value));
            }
        }
        unset($value);
        $data = (object) $this->data;

        // REF
        if (isset($data->REF)) {
            $doc->ref = (int) $data->REF;
            if ($doc->ref != $data->REF) {
                $this->error ('Champ REF incorrect');
            }
            unset($data->REF);
        } else {
            $this->error ('Champ REF absent');
        }

        // OP et DS
        $op = '';
        if (isset($data->OP)) {
            $op = $data->OP;
        } else {
            $this->error ('Champ OP absent');
        }

        $ds = '';
        if (isset($data->DS)) {
            $ds = $data->DS;
            if (preg_match('~^(\d{4})[\.-](\d{2})[\.-](\d{2})$~', $ds, $match)) {
                $ds = $match[1] . $match[2] . $match[3];
            } else {
                $this->error ('Date invalide dans DS : ' . $ds);
                $ds = '';
            }
        } else {
            $this->error ('Champ DS absent');
        }

        ($ds || $op) && $doc->creation = array('date' => $ds, 'by' => $op);
        $op && $doc->owner = array($op);
        unset($data->OP, $data->DS);

        // TY
        if (isset($data->TY)) {
            $doc->type = $data->TY;
            unset($data->TY);
        } else {
            $this->error ('Champ TY absent');
        }

        // GEN
        if (isset($data->GEN)) {
            $doc->genre[] = $data->GEN;
            unset($data->GEN);
        } else {
            $this->error ('Champ GEN absent');
        }

        // SUP
        if (isset($data->SUP)) {
            $doc->genre[] = $data->SUP;
            unset($data->SUP);
        } else {
            $this->error ('Champ SUP absent');
        }

        // URL, DCSITE

        // AU, AS
        foreach(array('AU' => '', 'AS' => 'interviewer') as $AU => $defaultRole) {
            if (isset($data->$AU)) {
                foreach($data->$AU as $aut) {
                    if (preg_match('~^([A-Z-]+)\s*\(([^\)]+)\)(.*)$~', $aut, $match)) {
                        $aut = array(
                            'name' => $match[1],
                            'firstname' => trim($match[2]),
                        );
                        $match[3] = trim($match[3]);
                        if (!$match[3] && $defaultRole) $match[3] = $defaultRole;
                        $match[3] && $aut['role'] = $match[3];

                        $doc->author[] = $aut;
                    } else {
                        $doc->author[] = array('name' => $aut);
                        $this->error ("Auteur invalide dans $AU : $aut");
                    }
                }
            }
        }
        unset($data->AU, $data->AS);

        // AUCO
        if (isset($data->AUCO)) {
            foreach($data->AUCO as $aut) {
                // Ressemble à un auteur physique ?
                if (preg_match('~^([A-Z]+)\s*\(([A-Z][^\)]+)\)$~', $aut, $match)) {
                    $aut = array(
                        'name' => $match[1],
                        'firstname' => trim($match[2]),
                        'role' => 'dir.',
                    );

                    $doc->author[] = $aut;

                } else {
                    $doc->organisation[] = array('name' => $data->AUCO);
                }
            }
            unset($data->AUCO);
        }

        // TI
        if (isset($data->TI)) {
            $doc->title = $data->TI;
            unset($data->TI);
        } else {
            $this->error ('Champ TI absent');
        }

        // TN
        if (isset($data->TN)) {
            $doc->othertitle[] = array('type' => 'dossier', 'title' => $data->TN);
            unset($data->TN);
        }

        // COL
        if (isset($data->COL)) {
            $doc->event[] = array('title' => $data->COL); // todo date, place, number
            // event multivalué. Utile ?
            unset($data->COL);
        }

        // TP
        if (isset($data->TP)) {
            $doc->journal = $data->TP;
            unset($data->TP);
        } else {
            $this->error ('Champ TP absent');
        }

        // DP (numéro de volume, dans la doc, c'est VOL)
        if (isset($data->DP)) {
            $doc->volume = $data->DP;
            unset($data->DP);
        }

        // NUMERO (issue, dans la doc, c'est SO)
        if (isset($data->NUMERO)) {
            $doc->issue = $data->NUMERO;
            unset($data->NUMERO);
        }

        // AUTRES (date, dans la doc, c'est DP)
        if (isset($data->AUTRES)) {
            $doc->date = $data->AUTRES;
            // strptime ?
            unset($data->AUTRES);
        }

        // cf ordre de la doc

        // PAGES
        if (isset($data->PAGES)) {
            // pagination de type analytique : "p. x", "p. x-y", "pp. x-y", etc.
            if (preg_match('~^p+[.\s]*(\d+(?:-\d+)?)$~', $data->PAGES, $match)) {
                $doc->pagination = $match[1];
            }

            // pagination de type monographique (x p.)
            elseif (preg_match('~^(\d+)\s*p+[.\s]*$~', $data->PAGES, $match)) {
                $doc->pagination = $match[1] . 'p';
            }

            // pagination non reconnue / erreur
            else {
                $this->error ('Champ PAGES incorrect');
                $doc->pagination = $data->PAGES;
            }
            unset($data->PAGES);
        } else {
            $this->error ('Champ PAGES absent');
        }

        // RESTE
        if (isset($data->RESTE)) {
            $doc->format = $data->RESTE;
            unset($data->RESTE);
        }

        // DE
        if (isset($data->DE)) {
            $doc->topic[] = array('type' => 'prisme', 'term' => $data->DE);
            unset($data->DE);
        } else {
            $this->error ('Champ DE absent');
        }

        // HI
        if (isset($data->HI)) {
            $doc->topic[] = array('type' => 'period', 'term' => $data->HI);
            unset($data->HI);
        }

        // DENP
        if (isset($data->DENP)) {
            $doc->topic[] = array('type' => 'names', 'term' => $data->DENP);
            unset($data->DENP);
        }

        // GO
        if (isset($data->GO)) {
            $doc->topic[] = array('type' => 'geo', 'term' => $data->GO);
            unset($data->GO);
        }

        // CD
        if (isset($data->CD)) {
            $doc->topic[] = array('type' => 'free', 'term' => $data->CD);
            unset($data->CD);
        }

        // Terminé
        $doc->imported = $imported;
        if ($this->errors) {
            $doc->errors = $this->errors;
        }

        unset($data->SO, $data->DATRI, $data->NO);

        $data=(array)$data;
        if ($data) $doc->todo = array_keys($data);

        return $doc;
    }
}