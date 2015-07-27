<?php
if (!defined('INCLUDE')) { exit; }

class Layouter
{
    private $content = '';

    function addToContent($content) {
        $this->content .= $content;
    }

    function printOut() {
        global $gliederung;
        printf(file_get_contents(PATH . '/templates/layout.html', false), $gliederung, $this->content);
    }
}

function possibleElements()
{
    return [
        'date' => file_get_contents(PATH . '/templates/elements/label.html', false) .
            file_get_contents(PATH . '/templates/elements/date.html', false),
        'email' => file_get_contents(PATH . '/templates/elements/label.html', false) .
            file_get_contents(PATH . '/templates/elements/email.html', false),
        'select' => file_get_contents(PATH . '/templates/elements/label.html', false) .
            file_get_contents(PATH . '/templates/elements/select.html', false),
        'text' => file_get_contents(PATH . '/templates/elements/label.html', false) .
            file_get_contents(PATH . '/templates/elements/text.html', false),
        'textfield' => file_get_contents(PATH . '/templates/elements/label.html', false) .
            file_get_contents(PATH . '/templates/elements/textfield.html', false),
        'infotext' => file_get_contents(PATH . '/templates/elements/infotext.html', false),
        'requiredMarker' => file_get_contents(PATH . '/templates/elements/requiredMarker.html', false),
        'select-option' => file_get_contents(PATH . '/templates/elements/select-option.html', false),
    ];
}

function field($id, $element)
{
    $possibleElements = possibleElements();

    $name = $element['name'] . ($element['required'] ? $possibleElements['requiredMarker'] : '');
    $e = null;
    $default = isset($element['default']) ? $element['default'] : '';
    $required = $element['required'] ? 'required' : '';
    switch ($element['type']) {
        case 'date':
            $e = sprintf($possibleElements['date'], $id, $name, $required, $default);
            break;
        case 'select':
            $options = [];
            foreach ($element['options'] as $key => $value) {
                $options[] = sprintf($possibleElements['select-option'], $key, $value);
            }
            $options = implode($options);
            $e = sprintf($possibleElements['select'], $id, $name, $options);
            break;
        /** @noinspection PhpMissingBreakStatementInspection */
        case 'infotext-replace':
            $value = isset($_SESSION['beitrittData']['step0'][$element['replace']]) ?
                $_SESSION['beitrittData']['step0'][$element['replace']] : null;
            $value = $element['options'][$value];
            $default = sprintf($element['default'], $value);
            $element['type'] = 'infotext';
            // fallthrough
        default:
            $elem = $possibleElements[$element['type']];
            $e = sprintf($elem, $id, $name, $required, $default);
            break;
    }
    return $e;
}

function zahlungsartField()
{
    $possibleElements = possibleElements();

    global $zahlungsarten;
    $options = [];
    foreach ($zahlungsarten as $key => $value) {
        $options[] = sprintf($possibleElements['select-option'], $key, $value['name']);
    }
    $options = implode($options);
    return sprintf($possibleElements['select'], 'zahlungsart', 'Zahlungsart', $options);
}

function checkErrors($step)
{
    switch ($step) {
        case 1:
            return checkErrorsStep0();
        case 2:
            return checkErrorsStep1();
        case 3:
            return checkErrorsStep2();
        case 4:
            return [];
        default:
            return ['ERROR'];
    }
}

function checkErrorsStep0()
{
    $ret = [];
    $missing = [];
    $results = [];
    $errorAnywhere = false;
    global $fields;
    foreach ($fields as $id => $element) {
        $error = false;
        $value = isset($_POST[$id]) && strlen(trim($_POST[$id])) > 0 ? trim($_POST[$id]) : null;
        if ($value === null) { // das Element gibt es nicht
            if ($element['required']) {
                $missing[] = $element['name'];
                $error = true;
            }
        } else {
            if ($element['type'] == 'date') {
                // wir machen ein wenig einfache Validierung
                if (!validateDate($value)) {
                    $ret[] = 'Bitte geben Sie ' . $element['name'] . ' im Format dd.mm.jjjj an.';
                    $error = true;
                }
            } else {
                if ($element['type'] == 'email') {
                    if (!isValidEmail($value)) {
                        $ret[] = 'Bitte geben Sie ' . $element['name'] . ' als gültige E-Mail-Adresse an.';
                        $error = true;
                    }
                }
            }
        }
        $errorAnywhere = $errorAnywhere || $error;
        if (!$error) {
            $results[$id] = $value;
        }
    }
    $results['zahlungsart'] = isset($_POST['zahlungsart']) ? (int)$_POST['zahlungsart'] : null;
    if (!$errorAnywhere) {
        // wir haben keinen Fehler und speichern die Ergebnisse in der Session
        $_SESSION['beitrittData']['step0'] = $results;
    }
    if (count($missing) > 0) {
        $ret[] = 'Fehlende Angaben: ' . implode(', ', $missing);
    }
    return $ret;
}

function checkErrorsStep1()
{
    $ret = [];
    $missing = [];
    $results = [];
    $errorAnywhere = false;
    global $triggerFields;
    foreach ($triggerFields as $pElements) {
        foreach ($pElements as $element) {
            $error = false;
            $value = isset($_POST[$element['id']]) && strlen(trim($_POST[$element['id']])) > 0 ? trim($_POST[$element['id']]) : null;
            if ($value === null) { // das Element gibt es nicht
                if ($element['required']) {
                    $missing[] = $element['name'];
                    $error = true;
                }
            }
            $errorAnywhere = $errorAnywhere || $error;
            if (!$error) {
                $results[$element['id']] = $value;
            }
        }
    }
    if (!$errorAnywhere) {
        // wir haben keinen Fehler und speichern die Ergebnisse in der Session
        $_SESSION['beitrittData']['step1'] = $results;
    }
    if (count($missing) > 0) {
        $ret[] = 'Fehlende Angaben: ' . implode(', ', $missing);
    }
    return $ret;
}

function checkErrorsStep2()
{
    $ret = [];
    $missing = [];
    $results = [];
    $errorAnywhere = false;
    foreach ($_SESSION['zahlungsartFields'] as $id => $element) {
        $error = false;
        $value = isset($_POST[$id]) && strlen(trim($_POST[$id])) > 0 ? trim($_POST[$id]) : null;
        if ($value === null) { // das Element gibt es nicht
            if ($element['required']) {
                $missing[] = $element['name'];
                $error = true;
            }
        }
        $errorAnywhere = $errorAnywhere || $error;
        if (!$error) {
            $results[$id] = $value;
        }
    }
    if (!$errorAnywhere) {
        // wir haben keinen Fehler und speichern die Ergebnisse in der Session
        $_SESSION['beitrittData']['step2'] = $results;
    }
    if (count($missing) > 0) {
        $ret[] = 'Fehlende Angaben: ' . implode(', ', $missing);
    }
    return $ret;
}

function checkForTriggerFields()
{
    global $triggerFields;
    if ($_GET['step'] != 1 && isset($_SESSION['triggerFields'])) {
        return $_SESSION['triggerFields'];
    }
    $ret = [];
    foreach ($triggerFields as $id => $elements) {
        if (isset($_POST[$id])) { // es gibt ein Feld, dass entsprechend triggert
            if (isset($elements[$_POST[$id]])) {
                $ret[] = field($elements[$_POST[$id]]['id'], $elements[$_POST[$id]]);
            }
        }
    }
    $_SESSION['triggerFields'] = $ret;
    return $ret;
}

function checkForZahlungsartFields()
{
    global $zahlungsarten;
    $ret = [];
    $zahlungsart = $_SESSION['beitrittData']['step0']['zahlungsart'];
    $elements = $_SESSION['zahlungsartFields'] = $zahlungsarten[$zahlungsart]['fields'];
    foreach ($elements as $id => $element) {
        $ret[] = field($id, $element);
    }
    return $ret;
}

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL)
    && preg_match('/@.+\./', $email);
}

function validateDate($date, $format = null)
{
    if ($format == null) {
        if (strpos($date, '.') !== false) {
            $format = 'd.m.Y';
        } else {
            $format = 'Y-m-d';
        }
    }
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function getMailElements($beitrittData)
{
    $el = file_get_contents(PATH . '/templates/elements/email-row.html', false);
    $rows = [];

    foreach ($beitrittData as $key => $value) {
        if ($key == 'beitrittsdatum' || $key == 'geburtsdatum') {
            $dt = new DateTimeImmutable($value);
            $value = $dt->format('d.m.Y');
        } else if ($key == 'beitragsart') {
            global $fields;
            $value =  $fields['beitragsart']['options'][$value];
        } else if ($key == 'zahlungsart') {
            global $zahlungsarten;
            $value =  $zahlungsarten[$value]['name'];
        }
        $rows[] = sprintf($el, $key, nl2br($value));
    }

    return $rows;
}