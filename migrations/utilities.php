<?php

    /**
     * Reads in a password from standard input, masking typed characters.
     *
     * @see http://docstore.mik.ua/orelly/webprog/pcook/ch20_05.htm
     * @param string $os The name of the current operating system
     * @return string
     */
    function readPassword($os)
    {
        $password = "";

        if (strtoupper(substr($os, 0, 3)) === 'WIN') {
            /*
             * Password masking in Windows is difficult.  We tried to implement it, but it's not really working.
             * Sorry, go get a *nix environment instead if you want password masking on the command line.
             */

            // read password
            $password = rtrim(fgets(STDIN), "\r\n");

            /*
            // Windows.  Need to use _getch()
            // An alternative solution for Windows would be to create a special C program: https://blog.dsl-platform.com/hiding-input-from-console-in-php/

            // load the w32api extension and register _getch()
            dl('php_w32api.dll');
            w32api_register_function('msvcrt.dll','_getch','int');

            while(true) {
                // get a character from the keyboard
                $c = chr(_getch());
                if ( "\r" == $c ||  "\n" == $c ) {
                    // if it's a newline, break out of the loop, we've got our password
                    break;
                } elseif ("\x08" == $c) {
                    // if it's a backspace, delete the previous char from $password
                    $password = substr_replace($password,'',-1,1);
                } elseif ("\x03" == $c) {
                    // if it's Control-C, clear $password and break out of the loop
                    $password = NULL;
                    break;
                } else {
                    // otherwise, add the character to the password
                    $password .= $c;
                }
            }
            */
        } else {
            // *nix.  Can use stty

            // turn off echo
            `/bin/stty -echo`;

            // read password
            $password = rtrim(fgets(STDIN), "\r\n");

            // turn echo back on
            `/bin/stty echo`;
        }

        return $password;
    }
