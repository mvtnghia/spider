<?php
//error_reporting(0);
set_time_limit(0);
ini_set('memory_limit', '1024M'); // 1G

include 'libs/simple_html_dom.php';
include 'libs/file.php';

// Testing purpose
function ll($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    die;
}

function handleInputData($filename) {
    $dataSource = explode(PHP_EOL, readFromFile($filename));
    $result = [];
    foreach ($dataSource as $item) {
        if (!$item) continue;
        $line = str_replace(', ', ',', trim($item, ','));
        $word = explode(",", $line);
        foreach ($word as $item) {
            $temp = new stdClass();
            $temp->word = trim($item, ",");
            $temp->wordFamily = implode($word, ",");
            $result[] = $temp;
        }
    }
    return $result;
}

function downloadAudio($html, $word, $rootPath, $dir = 'audio') {
    if (!$html->find($rootPath . 'span[geo=br] div.audio_play_button')) return;
    $mp3_br = $html->find($rootPath . 'span[geo=br] div.audio_play_button')[0]->attr["data-src-mp3"];
    $mp3_am = $html->find($rootPath . 'span[geo=n_am] div.audio_play_button')[0]->attr["data-src-mp3"];
    file_put_contents($dir . '/' . $word . '.br.mp3', fopen($mp3_br, 'r'));
    file_put_contents($dir . '/' . $word . '.am.mp3', fopen($mp3_am, 'r'));
}

function getWord($html, $rootPath) {
    $word = $html->find($rootPath . 'div.webtop-g h2.h')[0]->innertext;
    return preg_replace('/\<.*?\>.*?\<.*?\>/i', '', $word);
}

function getWordType($html, $rootPath) {
    $wt = $html->find($rootPath . 'div.webtop-g span.pos');
    return $wt ? $html->find($rootPath . 'div.webtop-g span.pos')[0]->innertext : '';
}

function getPhonetic($html, $rootPath, $isBr) {
    $geo = $isBr ? 'br' : 'n_am';
    $phonetic = $html->find($rootPath . "span[geo=$geo] span.phon");
    $plaintext = $phonetic ? $html->find($rootPath . "span[geo=$geo] span.phon")[0]->plaintext : '';
    return str_replace(['/', ' ', 'BrE', 'NAmE'], "", ltrim($plaintext));
}

function getOxfordContent($html, $rootPath) {
    $result = '';
    foreach($html->find($rootPath . '.h-g')[0]->children() as $element) {
        if($element->class === 'top-container' || $element->class === 'sn-gs') {
            $result = $result . $element->outertext;
        };
    };
    return $result;
}

function getIdioms($html, $rootPath) {
    $idioms = $html->find($rootPath . '.idm-gs');
    return $idioms ? $idioms[0]->outertext : '';
}

function getPhrasalVerbs($html, $rootPath) {
    $phrasalVerbs = $html->find($rootPath . '.pv-gs');
    return $phrasalVerbs ? $phrasalVerbs[0]->outertext : '';
}

function getOxfordUrl($word) {
    return "http://www.oxfordlearnersdictionaries.com/definition/english/" . trim($word);
}

function buildWordFamily($wordFamily, $rootPath) {
    $list = explode(',', $wordFamily);
    $result = [];
    foreach ($list as $i) {
        $url = getOxfordUrl($i);
        $html = file_get_html($url);
        if (!$html) {
            echo "WordFamily-Error: $i\n";
            continue;
        };
        $word = getWord($html, $rootPath);
        $wordType = getWordType($html, $rootPath);
        $result[] = "<a href='".getOxfordUrl($i)."'>$word</a>($wordType)";
    }
    return implode(', ', $result);
}
$words = handleInputData('570');
$rootPath = "body div[id=ox-container] div[id=main_column] div[id=main-container] div[id=entryContent] ";
$delimiter = "@";
$result = [];
$error = [];
foreach ($words as $w) {
    $html = file_get_html(getOxfordUrl($w->word));
    if (!$html) {
        $error[] = $w->word;
        continue;
    };
    echo $w->word . "|";
    $word = $w->word; 
    $wordData = [];
    $wordData['Word'] = getWord($html, $rootPath);
    $wordData['WordType'] = getWordType($html, $rootPath);
    $wordData['WordFamily'] = buildWordFamily($w->wordFamily, $rootPath);
    $wordData['Synonym'] = '';
    $wordData['Example'] = '';
    $wordData['Definition'] = '';
    $wordData['Vietnamese'] = '';
    $wordData['Image'] = '';
    $wordData['BrPhonetic'] = getPhonetic($html, $rootPath, 1);
    $wordData['AmPhonetic'] = getPhonetic($html, $rootPath, 0);
    $wordData['BrSound'] = "[sound:$word.br.mp3]";
    $wordData['AmSound'] = "[sound:$word.am.mp3]";
    $wordData['OxfordContent'] = getOxfordContent($html, $rootPath);
    $wordData['Idioms'] = getIdioms($html, $rootPath);
    $wordData['PhrasalVerbs'] = getPhrasalVerbs($html, $rootPath);
    $wordData['Source'] = '';

    downloadAudio($html, $word, $rootPath);
    $result[] = implode($delimiter, $wordData);
}

$finalData = implode(PHP_EOL, $result);
saveToFile('result', $finalData);
saveToFile('error', implode(PHP_EOL, $error));