<?php

    $spBaseUrl = 'http://example.com';

    $settingsInfo = array (
        'strict' => true,
        'debug' => true,
        'sp' => array (
            'entityId' => $spBaseUrl.'/samlauth/metadata.php',
            'assertionConsumerService' => array (
                'url' => $spBaseUrl.'/samlauth/index.php?acs',
            ),
            'singleLogoutService' => array (
                'url' => $spBaseUrl.'/samlauth/index.php?sls',
            ),
            'x509cert' => '',
            'privateKey' > '',
        ),
        'idp' => array (
            'entityId' => 'https://idp.example.com/saml/saml2/idp/metadata.php',
            'singleSignOnService' => array (
                'url' => 'https://idp.example.com/saml/saml2/idp/SSOService.php',
            ),
            'singleLogoutService' => array (
                'url' => 'https://idp.example.com/saml/saml2/idp/SingleLogoutService.php',
            ),
            'x509cert' => ''
        ),
        'security' => array (
            'nameIdEncrypted' => false,
            'authnRequestsSigned' => false,
            'logoutRequestSigned' => false,
            'logoutResponseSigned' => false,
            'signMetadata' => false,
            'wantMessagesSigned' => false,
            'wantAssertionsSigned' => false,
            'wantAssertionsEncrypted' => false,
            'wantNameIdEncrypted' => false,
        ),
    );
