<?php
// functions
function data($move)
{
    $data = new stdClass();
    $data->Key = 'move_name_0' . $move->id;
    $move = $move->name;
    $data->English = $move;
    $data->Japanese = translation($move, 'Japanese');
    $data->French = translation($move, 'French');
    $data->Spanish = $move;
    $data->German = translation($move, 'German');
    $data->Italian = $move;
    $data->Korean = translation($move, 'Korean');
    $data->ChineseTraditional = translation($move, 'ChineseTraditional');
    $data->BrazilianPortuguese = translation($move, 'BrazilianPortuguese');
    return $data;
}
function translation($language, $move)
{
    global $translations;
    if (!empty($translations->$language->$move)) {
        $result = $translations->$language->$move;
    } else {
        $result = $move;
    }
    return $move;
}
//process
$translations = new stdClass();
$translations->German = "de";
$translations->French = "fr";
$translations->Japanese = "ja";
$translations->Korean = "ko";
$translations->BrazilianPortuguese = "pt_br";
$translations->ChineseTraditional = "zh_cn";

foreach ($translations as $k => $v) {
    $translations->$k = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/locales/' . $v . '.json'));
}
$translationFile = json_decode(file_get_contents("C:/Users/fearn/OneDrive/projects/pogo_json/src/data/MOVES_TRANSLATIONS.json"));
$currentMoves = json_decode(file_get_contents('https://raw.githubusercontent.com/cecpk/OSM-Rocketmap/master/static/data/moves.json'));

$done = [];
foreach ($translationFile as $k => $v) {
    $num = str_replace('move_name_0', '', $v->Key);
    array_push($done, $num);
}
foreach ($currentMoves as $k => $v) {
    if (!in_array($k, $done)) {
        $move = new stdClass();
        $move->id = $k;
        $move->name = $v->name;
        $data = data($move);
        array_push($translationFile, $data);
    }
}
file_put_contents('C:/Users/fearn/OneDrive/projects/pogo_json/src/data/MOVES_TRANSLATIONS.json', json_encode($translationFile, JSON_UNESCAPED_UNICODE));
