<?php

/*
 * Mensagens de autenticação, em português.
 *
 * A pasta chama-se `en` de propósito: o APP_LOCALE da aplicação é `en` e
 * mudá-lo obrigava a alterar o .env de todos os ambientes, incluindo o do
 * servidor — e uma linha esquecida lá faz a aplicação voltar a inglês sem
 * avisar. O `lang/en/passwords.php` já seguia esta convenção.
 *
 * Tratamento por "tu", como o resto da aplicação.
 */

return [
    'failed' => 'Estas credenciais não correspondem a nenhuma conta.',
    'password' => 'A palavra-passe está incorreta.',
    'throttle' => 'Demasiadas tentativas de entrada. Tenta outra vez daqui a :seconds segundos.',
];
