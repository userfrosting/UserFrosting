<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

/**
 * Tanzanian swahili message token translations for the 'account' sprinkle.
 *
 * @author Fadhluilahi Mohammed
 */
return [
    'ACCOUNT' => [
        '@TRANSLATION'=> 'Akaunti',

        'ACCESS_DENIED'=> 'Hmm, inaonekana kama huna ruhusa ya kufanya hivyo.',

        'DISABLED'=> 'Akaunti hii imezimwa. Tafadhali wasiliana nasi kwa maelezo zaidi.',

        'EMAIL_UPDATED'=> 'Barua pepe ya akaunti imesasishwa',

        'INVALID'=> 'Akaunti hii haipo. Huenda imefutwa. Tafadhali wasiliana nasi kwa maelezo zaidi.',

        'MASTER_NOT_EXISTS'=> 'Huwezi kusajili akaunti hadi akaunti kuu iwe imeundwa!',

        'MY'=> 'Akaunti Yangu',

        'SESSION_COMPROMISED' => [
            '@TRANSLATION'=> 'Kipindi chako kimeathirika. Unapaswa kuondoka kwenye vifaa vyote, kisha uingie tena na uhakikishe kuwa data yako haijaingiliwa.',

            'TITLE'=> 'Akaunti yako inaweza kuwa imeingiliwa',

            'TEXT'=> 'Kuna mtu anaweza kuwa ametumia taarifa yako ya kuingia kufikia ukurasa huu. Kwa usalama wako, vipindi vyote viliondolewa. Tafadhali <a href="{{url}}">ingia</a> na uangalie akaunti yako kwa shughuli za kutiliwa shaka. Unaweza pia kutaka kubadilisha nenosiri lako.',

        ],
        'SESSION_EXPIRED'=> 'Kipindi chako kimeisha. Tafadhali ingia tena.',

        'SETTINGS' => [
            '@TRANSLATION'=> 'Mipangilio ya akaunti',

            'DESCRIPTION'=> 'Sasisha mipangilio ya akaunti yako, ikijumuisha barua pepe, jina, na nenosiri.',

            'UPDATED'=> 'Mipangilio ya akaunti imesasishwa',

        ],
        'TOOLS'=> 'Zana za akaunti',

        'UNVERIFIED'=> 'Akaunti yako bado haijathibitishwa. Angalia barua pepe zako / folda ya barua taka kwa maagizo ya kuwezesha akaunti.',

        'VERIFICATION' => [
            'NEW_LINK_SENT'=> 'Tumetuma kiungo kipya cha uthibitishaji kwa barua pepe{{email}}. Tafadhali angalia kikasha chako na folda za barua taka kwa barua pepe hii.',

            'RESEND'=> 'Tuma tena barua pepe ya uthibitishaji',

            'COMPLETE'=> 'Umefanikiwa kuthibitisha akaunti yako. Sasa unaweza kuingia.',

            'EMAIL'=> 'Tafadhali ingiza anwani ya barua pepe uliyotumia kujisajili, na barua pepe yako ya uthibitishaji itatupwa.',

            'PAGE'=> 'Tuma tena barua pepe ya uthibitishaji kwa akaunti yako mpya.',

            'SEND'=> 'Barua pepe kiungo cha uthibitishaji cha akaunti yangu',

            'TOKEN_NOT_FOUND'=> 'Tokeni ya uthibitishaji haipo / Akaunti tayari imethibitishwa',

        ],
    ],
    'EMAIL' => [
        'INVALID'=> 'Hakuna akaunti ya <strong>{{email}}</strong>.',

        'IN_USE'=> 'Barua pepe <strong>{{email}}</strong> tayari inatumika.',

        'VERIFICATION_REQUIRED'=> 'Barua pepe (uthibitishaji unahitajika - tumia anwani halisi!)',

    ],
    'EMAIL_OR_USERNAME'=> 'Jina la mtumiaji au anwani ya barua pepe',

    'FIRST_NAME'=> 'Jina la kwanza',

    'HEADER_MESSAGE_ROOT'=> 'UMEINGIA KAMA MTUMIAJI Mzizi',

    'LAST_NAME'=> 'Jina la ukoo',

    'LOCALE' => [
        'ACCOUNT'=> 'Lugha na eneo la kutumia kwa akaunti yako',

        'INVALID'=> '<strong>{{locale}}</strong> si eneo halali.',

    ],
    'INGIA' => [
        '@TRANSLATION'=> 'Ingia',

        'ALREADY_COMPLETE'=> 'Tayari umeingia!',

        'SOCIAL'=> 'Au ingia na',

        'REQUIRED'=> 'Samahani, lazima uwe umeingia ili kufikia rasilimali hii.',

    ],
    'LOGOUT'=> 'Toka',

    'NAME'=> 'Jina',

    'NAME_AND_EMAIL'=> 'Jina na barua pepe',

    'PAGE' => [
        'LOGIN' => [
            'DESCRIPTION'=> 'Ingia kwa yako{{site_name}}akaunti, au kujiandikisha kwa akaunti mpya.',

            'SUBTITLE'=> 'Jiandikishe bila malipo, au ingia na akaunti iliyopo.',

            'TITLE'=> 'Hebu tuanze!',

        ],
    ],
    'PASSWORD' => [
        '@TRANSLATION'=> 'Nenosiri',

        'BETWEEN'=> 'Kati ya{{min}}-{{max}}wahusika',

        'CONFIRM'=> 'Thibitisha nenosiri',

        'CONFIRM_CURRENT'=> 'Tafadhali thibitisha nenosiri lako la sasa',

        'CONFIRM_NEW'=> 'Thibitisha Nenosiri Jipya',

        'CONFIRM_NEW_EXPLAIN'=> 'Ingiza tena nenosiri lako jipya',

        'CONFIRM_NEW_HELP'=> 'Inahitajika tu ikiwa unachagua nenosiri jipya',

        'CREATE' => [
            '@TRANSLATION'=> 'Tengeneza Nenosiri',

            'PAGE'=> 'Chagua nenosiri kwa akaunti yako mpya.',

            'SET'=> 'Weka Nenosiri na Ingia',

        ],
        'CURRENT'=> 'Nenosiri la Sasa',

        'CURRENT_EXPLAIN'=> 'Lazima uthibitishe nenosiri lako la sasa kufanya mabadiliko',

        'FORGOTTEN'=> 'Nenosiri Lililosahauliwa',

        'FORGET' => [
            '@TRANSLATION'=> 'Nimesahau nenosiri langu',

            'COULD_NOT_UPDATE'=> 'Haikuweza kusasisha nenosiri.',

            'EMAIL'=> 'Tafadhali ingiza anwani ya barua pepe uliyotumia kujisajili. Kiungo chenye maagizo ya kuweka upya nenosiri lako kitatumwa kwako kwa barua pepe.',

            'EMAIL_SEND'=> 'Kiungo cha Kuweka upya Nenosiri la Barua pepe',

            'INVALID'=> 'Ombi hili la kuweka upya nenosiri halikuweza kupatikana, au muda wake umeisha. Tafadhali jaribu <a href="{{url}}">kuwasilisha upya ombi lako<a>.',

            'PAGE'=> 'Pata kiungo cha kuweka upya nenosiri lako.',

            'REQUEST_CANNED'=> 'Ombi la nenosiri lililopotea limeghairiwa.',

            'REQUEST_SENT'=> 'Kama barua pepe <strong>{{email}}</strong> inalingana na akaunti katika mfumo wetu, kiungo cha kuweka upya nenosiri kitatumwa kwa <strong>{{email}}</strong>.',

        ],
        'HASH_FAILED'=> 'Nenosiri hashing imeshindwa. Tafadhali wasiliana na msimamizi wa tovuti.',

        'INVALID'=> 'Nenosiri la sasa halilingani na tulilonalo kwenye rekodi',

        'NEW'=> 'Nenosiri Jipya',

        'NOTHING_TO_UPDATE'=> 'Huwezi kusasisha kwa neno la siri sawa',

        'RESET' => [
            '@TRANSLATION'=> 'Weka upya Nenosiri',

            'CHOOSE'=> 'Tafadhali chagua nenosiri jipya ili kuendelea.',

            'PAGE'=> 'Chagua nenosiri jipya kwa akaunti yako.',

            'SEND'=> 'Weka Nenosiri Jipya na Ingia',

        ],
        'UPDATED'=> 'Nenosiri la akaunti limesasishwa',

    ],
    'PROFILE' => [
        'SETTINGS'=> 'Mipangilio ya wasifu',

        'UPDATED'=> 'Mipangilio ya wasifu imesasishwa',

    ],
    'RATE_LIMIT_EXCEEDED'=> 'Kikomo cha kiwango cha kitendo hiki kimepitwa. Lazima usubiri mwingine{{delay}}sekunde chache kabla utaruhusiwa kufanya jaribio lingine.',

    'REGISTER'=> 'Jiandikishe',

    'REGISTER_ME'=> 'Nisajili',

    'REGISTRATION' => [
        'BROKEN'=> 'Samahani, kuna tatizo katika mchakato wa usajili wa akaunti yetu. Tafadhali wasiliana nasi moja kwa moja kwa usaidizi.',

        'COMPLETE_TYPE1'=> 'Umefanikiwa kujiandikisha. Sasa unaweza kuingia.',

        'COMPLETE_TYPE2'=> 'Umefanikiwa kujiandikisha. Kiungo cha kuwezesha akaunti yako kimetumwa kwa <strong>{{email}}</ strong>. Hutaweza kuingia hadi ukamilishe hatua hii.',

        'DISABLED'=> 'Samahani, usajili wa akaunti umezimwa.',

        'LOGOUT'=> 'Samahani, huwezi kujiandikisha kwa akaunti ukiwa umeingia. Tafadhali ondoka kwanza.',

        'WELCOME'=> 'Usajili ni wa haraka na rahisi.',

    ],
    'REMEMBER_ME'=> 'Niweke nikiwa nimeingia',

    'REMEMBER_ME_ON_COMPUTER'=> 'Nikumbuke kwenye kompyuta hii (haipendekezwi kwa kompyuta za umma)',

    'SIGN_IN_HERE'=> 'Tayari una akaunti? <a href="{{url}}">Ingia hapa.</a>',

    'SIGNIN'=> 'Ingia',

    'SIGNIN_OR_REGISTER'=> 'Ingia au sajili',

    'SIGNUP'=> 'Jisajili',

    'TOS'=> 'Sheria na Masharti',

    'TOS_AGREEMENT'=> 'Kwa kusajili akaunti na{{site_title}}, unakubali <a{{link_attributes | raw}}>sheria na masharti</a>.',

    'TOS_FOR'=> 'Sheria na Masharti ya{{title}}',

    'USERNAME' => [
        '@TRANSLATION'=> 'Jina la mtumiaji',

        'CHOOSE'=> 'Chagua jina la mtumiaji la kipekee',

        'INVALID'=> 'Jina la mtumiaji batili',

        'IN_USE'=> 'Jina la mtumiaji <strong>{{user_name}}</strong> tayari inatumika.',

        'NOT_AVAILABLE'=> "Jina la mtumiaji <strong>{{user_name}}</strong> haipatikani. Chagua jina tofauti, au ubofye 'pendekeza'.",

    ],
    'USER_ID_INVALID'=> 'Kitambulisho cha mtumiaji kilichoombwa hakipo.',

    'USER_OR_EMAIL_INVALID'=> 'Jina la mtumiaji au anwani ya barua pepe ni batili.',

    'USER_OR_PASS_INVALID'=> 'Mtumiaji hajapatikana au nenosiri ni batili.',

    'WELCOME'=> 'Karibu tena,{{first_name}}',

];
