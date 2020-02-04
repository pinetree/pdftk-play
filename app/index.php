<?php
require_once 'vendor/autoload.php';

use mikehaertl\pdftk\Pdf;

error_reporting(E_ERROR);

$data = [];

foreach ($_POST as $k => $v) {
    $data[$k] = json_decode(stripslashes($v), true);
};

foreach ($data['schemas'] as $schemaName => $schema) {
    $path = explode('.', $schemaName);
    $temp = &$data['schemas'];
    foreach ($path as $parent) {
        $temp = &$temp[$parent];
    }

    $temp = array_reduce($schema, function ($acc, $item) {
        $acc[$item['name']] = $item;
        return $acc;
    }, []);
}


$pdf = new Pdf('/var/www/html/public/template.pdf', [
    'command' => '/usr/bin/pdftk',
    //'useExec' => true
]);
$pdf->tempDir = '/var/www/html/public/work';

$name = getName($data);

if (!$pdf->allow('AllFeatures')->fillForm(
    array_merge(
        [
            'personName' => $name,
            'personRole' => getRole($data),
            /*'personInfo.lastName' => $data['personInfo']['lastName'],
            'personInfo.firstName' => $data['personInfo']['firstName'],
            'personInfo.patronymic' => $data['personInfo']['patronymic'],
            'personInfo.nameChanged' => checkbox($data['personInfo']['nameChanged']),
            'personInfo.previousLastName' => $data['personInfo']['previousLastName'],
            'personInfo.previousFirstName' => $data['personInfo']['previousFirstName'],
            'personInfo.previousPatronymic' => $data['personInfo']['previousPatronymic'],
            'personInfo.nameChangeDate' => $data['personInfo']['nameChangeDate'],
            'personInfo.birthDate' => $data['personInfo']['birthDate'],
            'personInfo.nameChangedReason' => $data['personInfo']['nameChangedReason'],
            'personInfo.birthPlace' => $data['personInfo']['birthPlace'],
            'personInfo.genderMale' => checkbox($data['personInfo']['gender'] === 'male'),
            'personInfo.genderFemale' => checkbox($data['personInfo']['gender'] === 'female'),
            'personInfo.citizenshipRussia' => checkbox($data['personInfo']['citizenship'] === 'russia'),
            'personInfo.citizenshipOther' => checkbox($data['personInfo']['citizenship'] === 'other'),
            'personInfo.employmentStatus:work' => checkbox($data['personInfo']['employmentStatus'] === 'work'),
            'personInfo.employmentStatus:army' => checkbox($data['personInfo']['employmentStatus'] === 'army'),
            'personInfo.employmentStatus:pensioner' => checkbox($data['personInfo']['employmentStatus'] === 'pensioner'),
            'personInfo.employmentStatus:unemployment' => checkbox($data['personInfo']['employmentStatus'] === 'unemployment'),*/
            'document.passportNumber' => isResident($data['personInfo']) ? $data['documents']['passportNumber'] : $data['documents']['idNumber'],
            'document.passportIssueDate' => $data['documents']['passportIssueDate'],
            'document.passportCode' => $data['documents']['passportCode'],
            'document.passportIssueOrigin' => isResident($data['personInfo']) ? $data['documents']['passportIssueOrigin'] : $data['documents']['country'],
            'document.snils' => $data['documents']['snils'],
            'document.inn' => $data['documents']['inn'],
        ],
        block('personInfo', $data),
        block('documents', $data),
        block('socialStatus', $data),
        block('address.fact', $data),
        block('address.registration', $data),
        block('employment', $data),
        block('finances.income', $data),
        block('finances.expense', $data),
        block('actives.vehicles', $data),
        block('actives.properties', $data),
        block('actives.jobs', $data),
        block('special', $data),
        block('agreements', $data)
    )
)
    ->needAppearances()
    ->send(preg_replace("/[\s]+/", '_', $name) . '.pdf', true)) {
    $error = $pdf->getError();

    echo '1' . $error;
}

function block($blockName, $data): array
{
    $schema = path($data, 'schemas.' . $blockName);

    if (!$schema) {
        throw new Exception('No schema for block ' . $blockName);
    }

    $out = [];

    foreach ($schema as $k => $v) {
        if ($v['type']) {
            if ($v['type'] === 'boolean')
                $out[$blockName . "." . $k] = checkbox($data[$blockName][$k]);

            if ($v['type'] === 'options' && is_array($v['options'])) {

                foreach ($v['options'] as $optionKey => $optionValue) {
                    $out[$blockName . "." . $k . ':' . $optionKey] = checkbox($data[$blockName][$k] === $optionKey || $data[$blockName][$k] === $optionValue);
                }
            }
        } else
            $out[$blockName . "." . $k] = $data[$blockName][$k];
    }

    return $out;
}

function getName($data, $glue = ' ')
{
    return implode($glue, [$data['personInfo']['lastName'], $data['personInfo']['firstName'], $data['personInfo']['patronymic']]);
}

function getRole($data)
{
    return $data['personInfo']['role'] === 'borrower' ? 'Заёмщик' : 'Созаёмщик';
}

function checkbox($value)
{
    return $value ? 'Yes' : 'No';
}


function isResident($personInfo)
{
    return $personInfo['citizenship'] === 'russia';
}

function path(array &$array, $parents, $glue = '.')
{
    if (!is_array($parents)) {
        $parents = explode($glue, $parents);
    }

    $ref = &$array;

    foreach ((array)$parents as $parent) {
        if (is_array($ref) && array_key_exists($parent, $ref)) {
            $ref = &$ref[$parent];
        } else {
            return null;
        }
    }
    return $ref;
}
