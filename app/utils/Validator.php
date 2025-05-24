<?php

class Validator {

    /**
     * Verifica que los campos requeridos estén presentes en un array de datos.
     * @param array $data El array de datos (ej. $_POST).
     * @param array $requiredFields Array con los nombres de los campos requeridos.
     * @return array Array con los nombres de los campos faltantes. Vacío si todos están presentes.
     */
    public static function requiredFields(array $data, array $requiredFields): array {
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $missingFields[] = $field;
            }
        }
        return $missingFields;
    }

    /**
     * Valida si un string es una dirección de correo electrónico válida.
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida la longitud de una contraseña.
     * @param string $password
     * @param int $minLength Longitud mínima requerida.
     * @return bool
     */
    public static function isValidPasswordLength(string $password, int $minLength = 6): bool {
        return mb_strlen($password) >= $minLength;
    }

    // Puedes añadir más métodos de validación aquí según sea necesario
    // ej. para números, fechas, URLs, etc.
}

?>