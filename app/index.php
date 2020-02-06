<?php
require_once 'vendor/autoload.php';

use mikehaertl\pdftk\Pdf;

error_reporting(E_ERROR);

$data = json_decode(stripslashes($_POST['data']), true);;

if (!$data)
    exit;

$pdf = new Pdf('/var/www/html/public/template.pdf', [
    'command' => 'java -jar /var/www/html/mcpdf/mcpdf.jar',
]);

if (!$pdf
    ->fillForm($data)
    ->flatten()
    ->send(preg_replace('/[\s]+/', '_', $data['personName']) || 'print' . '.pdf', true)) {

    $error = $pdf->getError();
    echo $error;
}
