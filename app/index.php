<?php
require_once 'vendor/autoload.php';

use mikehaertl\pdftk\Pdf;

error_reporting(0);

$data = [];
foreach ($_POST as $k => $v) {
    $data[$k] = json_decode(stripslashes($v), true);
};

$pdf = new Pdf('/var/www/html/public/template2.pdf', [
    'command' => '/usr/bin/pdftk',
    'useExec' => true
]);
$pdf->tempDir = '/var/www/html/public/work';

$name = getName($data);

if (!$pdf->allow('AllFeatures')->fillForm([
    'personName' => $name,
    'personRole' => getRole($data),
    'personInfo.lastName' => $data['personInfo']['lastName'],
    'personInfo.firstName' => $data['personInfo']['firstName'],
    'personInfo.patronymic' => $data['personInfo']['patronymic'],
    'personInfo.nameChanged' => checkbox($data['personInfo']['nameChanged']),
    'personInfo.previousLastName' => $data['personInfo']['previousLastName'],
    'personInfo.previousFirstName' => $data['personInfo']['previousFirstName'],
    'personInfo.previousPatronymic' => $data['personInfo']['previousPatronymic'],
    'personInfo.nameChangedDate' => $data['personInfo']['nameChangedDate'],
    'personInfo.birthDate' => $data['personInfo']['birthDate'],
    'personInfo.nameChangedReason' => $data['personInfo']['nameChangedReason'],
    'personInfo.birthPlace' => $data['personInfo']['birthPlace'],
    'personInfo.genderMale' => checkbox($data['personInfo']['gender'] === 'male'),
    'personInfo.genderFemale' => checkbox($data['personInfo']['gender'] === 'female'),
    'personInfo.citizenshipRussia' => checkbox($data['personInfo']['citizenship']  === 'russia'),
    'personInfo.citizenshipOther' => checkbox($data['personInfo']['citizenship'] === 'other'),
    'personInfo.employmentStatus:work' => checkbox($data['personInfo']['employmentStatus'] === 'work'),
    'personInfo.employmentStatus:army' => checkbox($data['personInfo']['employmentStatus'] === 'army'),
    'personInfo.employmentStatus:pensioner' => checkbox($data['personInfo']['employmentStatus'] === 'pensioner'),
    'personInfo.employmentStatus:unemployment' => checkbox($data['personInfo']['employmentStatus'] === 'unemployment'),
])
    ->needAppearances()
    ->send(preg_replace("/[\s]+/", '_', $name) . '.pdf')) {
    $error = $pdf->getError();

    echo '1' . $error;
}


function getName($data, $glue = ' ')
{
    return implode($glue, [$data['personInfo']['lastName'], $data['personInfo']['firstName'], $data['personInfo']['patronymic']]);
}

function getRole($data) {
    return $data['personInfo']['role'] === 'borrower' ? 'Заёмщик' : 'Созаёмщик';
}

function checkbox($value) {
    return $value ? 'Yes' : 'No';
}