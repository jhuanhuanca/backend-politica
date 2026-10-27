<?php

return [
    /*
    | HMAC para email_hash y ci_hash. Si está vacío, se usa APP_KEY.
    | Rotar esta clave invalida coincidencias con firmas existentes.
    */
    'hash_key' => env('IDENTITY_HASH_KEY', env('APP_KEY')),
];
