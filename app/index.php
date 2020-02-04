<?php
require_once 'vendor/autoload.php';

use mikehaertl\pdftk\Pdf;

$pdf = new Pdf('/var/www/html/public/sample.pdf', [
    'command' => '/usr/bin/pdftk',
    'useExec' => true
]);
$pdf->tempDir = '/var/www/html/public/work';

if (!$pdf->allow('AllFeatures')->fillForm([
    'personInfo.lastName' => ('qqq1Соснин Кирилл2'),
    'personInfo.nameChanged' => 'Yes',
])
    ->needAppearances()
    ->saveAs('filled.pdf')) {
    $error = $pdf->getError();

    echo '1' . $error;
}
