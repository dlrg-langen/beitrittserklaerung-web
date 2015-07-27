<?php
define('INCLUDE', true);

require 'config.php';
require 'lib/functions.php';
$layouter = new Layouter();

session_start();

if (!isset($_SESSION['beitrittData'])) {
    $_SESSION['beitrittData'] = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // das Formular wurde schon abgesendet, also müssen wir min. Schritt 1 haben
        $_GET['step'] = 1;
    } else {
        $_GET['step'] = 0;
    }
}

$step = isset($_GET['step']) && $_GET['step'] > 0 && $_GET['step'] <= 4 ?
    (int)$_GET['step'] : 0;

if ($step == 0) {
    $_SESSION['beitrittData'] = [];
    unset($_SESSION['zahlungsartFields']);
    unset($_SESSION['triggerFields']);
}

if ($step > 0) {
    $errors = checkErrors($step);
    if (count($errors) > 0) {
        --$step;
        $layouter->addToContent(sprintf(file_get_contents(PATH . '/templates/elements/error.html', false), implode('<br>', $errors)));
    }
}

$elements = [];

// wir bauen uns die Elemente für die Ausgabe zusammen
if ($step == 0) {
    foreach ($fields as $id => $element) {
        $elements[] = field($id, $element);
    }

    $elements[] = zahlungsartField();
    $actionUrl = $_SERVER['PHP_SELF'] . '?step=1';
    $layouter->addToContent(
        sprintf(file_get_contents(PATH . '/templates/form.html', false), $actionUrl, implode(PHP_EOL, $elements), 'Weiter'));
}

if ($step == 1) {
    $elements = checkForTriggerFields();
    if (count($elements) == 0) {
        $step = 2;
    } else {
        $actionUrl = $_SERVER['PHP_SELF'] . '?step=2';
        $layouter->addToContent(
            sprintf(file_get_contents(PATH . '/templates/form.html', false), $actionUrl, implode(PHP_EOL, $elements), 'Weiter'));
    }
}

if ($step == 2) {
    $elements = checkForZahlungsartFields();
    if (count($elements) == 0) {
        $step = 3;
    } else {
        $actionUrl = $_SERVER['PHP_SELF'] . '?step=3';
        $layouter->addToContent(
            sprintf(file_get_contents(PATH . '/templates/form.html', false), $actionUrl, implode(PHP_EOL, $elements), 'Weiter'));
    }
}

if ($step == 3) {
    foreach ($infos as $id => $element) {
        $elements[] = field($id, $element);
    }

    $actionUrl = $_SERVER['PHP_SELF'] . '?step=4';
    $layouter->addToContent(
        sprintf(file_get_contents(PATH . '/templates/form.html', false), $actionUrl, implode(PHP_EOL, $elements),
            'Kostenpflichtige Mitgliedsvertrag abschließen'));
}

if ($step == 4) {
    $step0 = isset($_SESSION['beitrittData']['step0']) ? $_SESSION['beitrittData']['step0'] : [];
    $step1 = isset($_SESSION['beitrittData']['step1']) ? $_SESSION['beitrittData']['step1'] : [];
    $step2 = isset($_SESSION['beitrittData']['step2']) ? $_SESSION['beitrittData']['step2'] : [];
    $beitrittData = array_merge($step0, $step1, $step2);

    if ($submit['type'] == 'email') {
        require 'lib/PHPMailer/PHPMailerAutoload.php';

        $mail = new PHPMailer();

        if ($submit['config']['smtp']['use']) {
            $mail->isSMTP();
            $mail->Host = $submit['config']['smtp']['server'];
            $mail->SMTPAuth = true;
            $mail->Username = $submit['config']['smtp']['user'];
            $mail->Password = $submit['config']['smtp']['password'];
            $mail->Port = 587;
        }

        $mail->CharSet = 'utf-8';
        $mail->From = $submit['config']['fromAddress'];
        $mail->FromName = $submit['config']['fromName'];
        $mail->addAddress($beitrittData['email']);
//        $mail->addBCC($submit['config']['address']);
        $mail->isHTML(true);

        $mail->Subject = "Ihr Mitgliedsantrag";
        $mail->Body = sprintf(file_get_contents(PATH . '/templates/email.html', false),
            $beitrittData['vorname'] . ' ' . $beitrittData['name'],
            implode(getMailElements($beitrittData)));

        if (!$mail->send()) {
            $layouter->addToContent(
                sprintf(file_get_contents(PATH . '/templates/endseite.html', false), 'error', 'Beim Versenden Ihres Mitgliedsantrags ' .
                    'ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal.', $back));
        } else {
            $layouter->addToContent(
                sprintf(file_get_contents(PATH . '/templates/endseite.html', false), 'success', 'Ihr Mitgliedsantrag wurde erfolgreich' .
                    ' versendet. Ihnen wurde eine eine Kopie des Mitgliedsantrages zugesendet. Überprüfen Sie unter ' .
                    'Umständen bitte Ihrem Werbeordner.', $back));
        }
    }
}

$layouter->printOut();