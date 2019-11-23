<?php

namespace PogoApi\Construct;

class Construct
{

    private $pokemonJ;
    private $gameMasterJ;
    private $movesJ;
    private $descriptionsJ;
    private $evoJ;
    private $pokemon;
    private $pokemonExt;
    private $moves;
    private $key;
    private $types;
    private $items;
    private $grunts;
    private $evo;

    public function __construct()
    {
        $this->pokemonJ = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/data/pokemon.json'));
        $this->gameMasterJ = json_decode(file_get_contents('https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json'));
        $this->movesJ = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/data/moves.json'));
        $this->descriptionsJ = json_decode(file_get_contents('https://raw.githubusercontent.com/KartulUdus/Professor-Poracle/master/src/util/description.json'));
        $this->itemsJ = json_decode(file_get_contents('https://raw.githubusercontent.com/whitewillem/PMSF/master/static/data/items.json'));
        if (!isset($GLOBALS['PogoApi'])) {
            $GLOBALS['PogoApi'] = new \stdClass();
        }
        $GLOBALS['PogoApi']->key = $this->createKey();
        return $this;
    }

    public function saveBase($path, $file = [])
    {
        if (is_dir($path)) {
            if (!empty($file)) {
                $file = ['pokemon', 'gamemaster', 'moves', 'descriptions'];
            }
            if (in_array('pokemon', $file)) {
                file_put_contents($path . '/pokemon_origional.json', $this->pokemonJ);
            }
            if (in_array('gamemaster', $file)) {
                file_put_contents($path . '/gamemaster_origional.json', $this->gameMasterJ);
            }
            if (in_array('moves', $file)) {
                file_put_contents($path . '/moves_origional.json', $this->movesJ);
            }
            if (in_array('desctriptions', $file)) {
                file_put_contents($path . '/descriptions_origional.json', $this->descriptionsJ);
            }
        }
    }
    public function buildJsons()
    {
        $this->pokemon = new \stdClass();
        $this->pokemonExt = new \stdClass();
        $this->moves = new \stdClass();
        $this->key = new \stdClass();
        $this->types = new \stdClass();
        $this->items = new \stdClass();
        $this->grunts = new \stdClass();
        $this->evo = new \stdClass();
        $this->processGameMaster();
    }
    private function createKey()
    {
        $base = json_decode(file_get_contents(__DIR__ . '/../../data/keys.json'));
        $base->forms = new \stdClass();
        $base->mon = new \stdClass();
        foreach ($this->pokemonJ as $k => $v) {
            if (!empty($v->forms)) {
                $pokemon = $v->name;
                $base->forms->$pokemon = new \stdClass();
                foreach ($v->forms as $num => $form) {
                    $formName = $form->formName;
                    if (isset($form->assetSuffix) && ($form->assetSuffix === 'pm0001_00_pgo_fall2019' || $form->assetSuffix === 'pm0004_00_pgo_fall2019' || $form->assetSuffix === 'pm0007_00_pgo_fall2019')) {
                        $formName = 'Fall 2019';
                    }
                    $base->forms->$pokemon->$formName = new \stdClass();
                    $base->forms->$pokemon->$formName->id = $num;
                    if (isset($form->assetSuffix)) {
                        $base->forms->$pokemon->$formName->asset = $form->assetSuffix;
                    } else {
                        $base->forms->$pokemon->$formName->asset = $form->assetId;
                    }
                }
            }
            if (!empty($v->name)) {
                $name = $v->name;
                $base->mon->$name = $k;
            }
        }
        $base->moves = new class ($this->movesJ)
        {
            public function __construct($moves)
            {
                foreach ($moves as $k => $v) {
                    $name = $v->name;
                    $this->$name = $k;
                }
            }
        };
        $base->items = new class ($this->itemsJ)
        {
            public function __construct($items)
            {
                foreach ($items as $k => $v) {
                    $name = $v->protoname;
                    $this->$name = $k;
                }
            }
        };
        file_put_contents(__DIR__ . '/../../data/keys.json', json_encode($base, JSON_UNESCAPED_UNICODE));
        return $base;
    }
    private function processGameMaster()
    {
        $gm = $this->gameMasterJ;
        foreach ($gm as $k => $v) {
            $idarray = explode('_', $v->templateId);
            switch ($idarray[0]) {
                case 'COMBAT':
                    if (substr($idarray[1], 0) === 'V') {
                        $id = $id = ltrim(substr($idarray[1], 0), '0');
                        $this->moves->$id = new Move($v->combatMove);
                    }
                    break;
                case 'SMEARGLE':
                    $id = '235';
                    if (isset($this->pokemon->$id)) {
                        $this->pokemon->$id->smeargleMoves($v->smeargleMovesSettings);
                    } else {
                        $this->pokemon->$id = new Pokemon($v->smeargleMovesSettings, 'smeargle');
                    }
                    break;
                case 'SPAWN':
                    $id = ltrim(substr($idarray[1], 0), '0');
                    $namePieces = ['mime', 'a', 'oh', 'jr', 'z'];
                    if (isset($idarray[4])) {
                        if (!in_array($idarray[4], $namePieces)) {
                            $form = join(' ', array_slice($idarray, 4));
                        }
                        if (in_array($idarray[4], $namePieces) && isset($idarray[5])) {
                            $form = join(' ', array_slice($idarray, 5));
                        }
                    } else {
                        $form = null;
                    }
                    if (!isset($this->pokemon->$id)) {
                        $this->pokemon->$id = new Pokemon($v->genderSettings, 'spawn', $form);
                    } else {
                        $this->pokemon->$id->addGender($v->genderSettings, $form);
                    }
                    break;
                default:
                    if (substr($idarray[0], 0, 1) === "V" && $idarray[1] === 'POKEMON') {
                        $id = ltrim(substr($idarray[0], 1), '0');
                        $namePieces = ['mime', 'oh', 'jr', 'z'];
                        $form = null;
                        if (isset($idarray[3])) {
                            if (!in_array($idarray[3], $namePieces)) {
                                $form = join(' ', array_slice($idarray, 3));
                            }
                            if (in_array($idarray[3], $namePieces) && isset($idarray[4])) {
                                $form = join(' ', array_slice($idarray, 5));
                            }

                            if (!isset($this->pokemon->$id)) {
                                $this->pokemon->$id = new Pokemon($v->pokemonSettings, 'pokemon', $form);
                            } else {
                                $this->pokemon->$id->editPokemon($v->pokemonSettings, $form);
                            }
                            if (!empty($v->pokemonSettings->familyId)) {
                                $fam = $v->pokemonSettings->familyId;
                                if (!isset($this->evo->$fam)) {
                                    $this->evo->$fam = new \stdClass();
                                }
                                if (!isset($this->evo->$fam->$id) && isset($v->evolutionBranch)) {
                                    $this->evo->$fam->$id = $v->evolutionBranch;
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }
    public function displayOrigional($file = [])
    {
        if (!empty($file)) {
            $file = ['pokemon', 'gamemaster', 'moves', 'descriptions'];
        }
        if (in_array('pokemon', $file)) {
            echo json_encode($this->pokemonJ);
        }
        if (in_array('gamemaster', $file)) {
            echo json_encode($this->gameMasterJ);
        }
        if (in_array('moves', $file)) {
            echo json_encode($this->movesJ);
        }
        if (in_array('desctriptions', $file)) {
            echo json_encode($this->descriptionsJ);
        }
    }
}
