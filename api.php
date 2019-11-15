<?php

namespace PogoApi;

class Construct
{

    public $pokemonJ;
    public $gameMasterJ;
    public $movesJ;
    public $descriptionsJ;
    public $typeKey;
    private $pokemon;
    private $pokemonExt;
    private $moves;
    private $key;
    private $types;
    private $items;
    private $grunts;

    public function __construct()
    {
        $this->pokemonJ = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/data/pokemon.json'));
        $this->gameMasterJ = json_decode(file_get_contents('https://raw.githubusercontent.com/PokeMiners/game_masters/master/latest/latest.json'));
        $this->movesJ = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/data/moves.json'));
        $this->subscriptionsJ = json_decode(file_get_contents('https://raw.githubusercontent.com/KartulUdus/Professor-Poracle/master/src/util/description.json'));
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
    private function ProcessGameMaster()
    {
        $gm = $this->gameMasterJ;
        foreach ($gm as $k => $v) {
            $id = explode('_', $v->templateId);
            switch ($id[0]) {
                case 'COMBAT':
                    if (substr($id[1], 0) === 'V') {
                        moves($v->combatMove, $id[1]);;
                    }
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


echo json_encode($template, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
