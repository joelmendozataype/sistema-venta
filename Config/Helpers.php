<?php
function strClean($cadena)
{
    $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $cadena);
    $string = trim($string);
    $string = stripslashes($string);
    $string = str_ireplace('<script>', '', $string);
    $string = str_ireplace('</script>', '', $string);
    $string = str_ireplace('<script type=>', '', $string);
    $string = str_ireplace('<script src>', '', $string);
    $string = str_ireplace('SELECT * FROM', '', $string);
    $string = str_ireplace('DELETE FROM', '', $string);
    $string = str_ireplace('INSERT INTO', '', $string);
    $string = str_ireplace('SELECT COUNT(*) FROM', '', $string);
    $string = str_ireplace('DROP TABLE', '', $string);
    $string = str_ireplace("OR '1'='1", '', $string);
    $string = str_ireplace('OR ´1´=´1', '', $string);
    $string = str_ireplace('IS NULL', '', $string);
    $string = str_ireplace('LIKE "', '', $string);
    $string = str_ireplace("LIKE '", '', $string);
    $string = str_ireplace('LIKE ´', '', $string);
    $string = str_ireplace('OR "a"="a', '', $string);
    $string = str_ireplace("OR 'a'='a", '', $string);
    $string = str_ireplace('OR ´a´=´a', '', $string);
    $string = str_ireplace('--', '', $string);
    $string = str_ireplace('^', '', $string);
    $string = str_ireplace('[', '', $string);
    $string = str_ireplace(']', '', $string);
    $string = str_ireplace('==', '', $string);
    return $string;
}
// Motivo obligatorio de las acciones auditadas (anulaciones, ajustes de inventario).
// Devuelve el texto limpio o null si no cumple el mínimo.
function motivoAuditoria()
{
    $motivo = trim(strClean($_POST['motivo'] ?? ''));
    $largo = mb_strlen($motivo);
    return ($largo >= 10 && $largo <= 255) ? $motivo : null;
}
const MSG_MOTIVO = array('msg' => 'Indica el motivo (mínimo 10 caracteres)', 'icono' => 'warning');
// URL de un archivo propio (js/css) con versión según su fecha de modificación,
// así el navegador descarga la versión nueva después de cada cambio
function asset(string $ruta)
{
    $version = file_exists($ruta) ? filemtime($ruta) : 0;
    return BASE_URL . $ruta . '?v=' . $version;
}
// Genera el hash seguro de una contraseña (bcrypt)
function claveHash(string $clave)
{
    return password_hash($clave, PASSWORD_DEFAULT);
}
// Verifica la contraseña; acepta también los hash SHA256 antiguos
function claveVerificar(string $clave, string $hash)
{
    if (password_verify($clave, $hash)) {
        return true;
    }
    return strlen($hash) == 64 && hash_equals($hash, hash("SHA256", $clave));
}
// Indica si el hash debe regenerarse (hash antiguo SHA256 o algoritmo desactualizado)
function claveRehash(string $hash)
{
    return strlen($hash) == 64 || password_needs_rehash($hash, PASSWORD_DEFAULT);
}


?>