<?php
require_once 'vendor/autoload.php';

use mikehaertl\pdftk\Pdf;

error_reporting(E_ERROR);

$data = json_decode(stripslashes($_POST['data']), true);;

$pdf = new Pdf('/var/www/html/public/template.pdf', [
    'command' => 'java -jar /var/www/html/mcpdf/mcpdf.jar',
    //'useExec' => true
]);
$pdf->tempDir = '/var/www/html/public/work';

$name = getName($data);

if (!$pdf
    ->fillForm(
        array_merge([
            'personName' => $name,
            'personRole' => getRole($data)
        ], $data)
    )
    ->flatten()
    ->send(preg_replace('/[\s]+/', '_', $name) . '.pdf', true)) {

    $error = $pdf->getError();
    echo $error;
}

function getName($data, $glue = ' ')
{
    return implode($glue,
        [$data['personInfo.lastName'], $data['personInfo.firstName'], $data['personInfo.patronymic']]);
}

function getRole($data)
{
    return $data['personInfo.role'] === 'borrower' ? 'Заёмщик' : 'Созаёмщик';
}
