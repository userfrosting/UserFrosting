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
            // Windows. Use custom C++ hidden input reader
            
            // read password
            $password = rtrim(shell_exec('utilities\win32\hiddenInput.exe'));

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
