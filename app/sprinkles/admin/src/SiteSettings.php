<?php

namespace UserFrosting\Sprinkle\Core\Model;

use \Illuminate\Database\Capsule\Manager as Capsule;

class SiteSettings extends UFModel {    
    
    /**
     * Get an array of system information for UserFrosting.
     *
     * @return array An array containing a list of information, such as software version, application path, etc.
     */ 
    public function getSystemInfo(){
        $results = [];
        $results['UserFrosting Version'] = $this->version;
        $results['Web Server'] = $_SERVER['SERVER_SOFTWARE'];
        $results['PHP Version'] = phpversion();
        $dbinfo = Database::getInfo();
        $results['Database Version'] = $dbinfo['db_type'] . " " .  $dbinfo['db_version'];
        $results['Database Name'] = $dbinfo['db_name'];
        $results['Table Prefix'] = $dbinfo['table_prefix'];
        $environment = static::$app->environment();
        $results['Application Root'] = static::$app->config('base.path');
        $results['Document Root'] = $this->uri['public'];
        return $results;
    }
    
    /**
     * Get the PHP error log as an array of lines.
     *
     * @param int $targetLines the number of lines to display.  Set to `null` to display all lines.
     * @param int $seekLen the number of bytes to grab at a time.
     * @return array An array containing 'path', which is the path of the PHP error log, and 'messages', which is an array of error messages sorted with the newest messages first.
     */
    static public function getLog($targetLines = null, $seekLen = 4096){
        // Check if error logging is enabled
        if (!ini_get("error_log")){
            $path = "Unavailable";
            $messages = ["You do not seem to have an error log set up.  Please check your php.ini file."];
        } else {
            if (!ini_get("log_errors")){
                $path = ini_get('error_log');
                $messages = ["Error logging appears to be disabled.  Please check your php.ini file."];
            } else {
                $path = ini_get('error_log');
                @$file = file($path);
                if (!$targetLines){
                    /* If they want all lines, give it to them */
                    @$file = file($path);
                    $messages = $messages = array_reverse($file);
                } else {

                    /** If they want a specific number of lines, seek
                     *  back from the end of the file, grabbing lines
                     *  as we go until we reach count.
                     *
                     * @var array $messages Log lines in reverse order
                     * @var int $linesRead Count of good lines stored to $messages
                     * @var int $targetLines Count of lines we want to read in total
                     * @var resource $fileHandle Log file handle
                     * @var int $sizeRemaining Bytes of file left to read
                     * @var int $seekLen Amount of bytes to read at a time
                     * @var string $remainder End of file remaining after previous loop
                     * @var string $current Current buffer chunk from file (plus remainder)
                     * @var array $curArray Current buffer chunk split by EOLs
                     * @var int $curLines Lines we still want to read from current buffer
                     */

                    $messages = [];
                    $fileHandle = fopen($path, 'r');
                    fseek($fileHandle, 0, SEEK_END);
                    $sizeRemaining = filesize($path);
                    $linesRead = 0;

                    /* If the end of the file is whitespace, discard
                       it. The remainder left over will be attached
                       to the back of the next line we read.          */
                    $remainder = ' ';
                    while (ctype_space($remainder) && $sizeRemaining){
                        fseek($fileHandle, -1, SEEK_CUR);
                        $remainder = fread($fileHandle, 1);
                        fseek($fileHandle, -1, SEEK_CUR);
                        $sizeRemaining -= 1;
                    }


                    while ($linesRead < $targetLines){

                        /* If there's no file left to read, return with
                           what we have. If the amount we want to read
                           is more than we have left, just take what's left. */
                        if ($sizeRemaining == 0){
                            break 1;
                        } elseif ($seekLen > $sizeRemaining){
                            if ($sizeRemaining < 0){
                                $sizeRemaining = 0;
                            }
                            $seekLen = $sizeRemaining;
                            $sizeRemaining = 0;
                        }
                        fseek($fileHandle, -$seekLen, SEEK_CUR); // Seek to the point we want to read from
                        $current = fread($fileHandle, $seekLen) . $remainder; // Attach the remainder from previous loop
                        fseek($fileHandle, -$seekLen, SEEK_CUR); // Reset back to same point after reading
                        $sizeRemaining -= $seekLen;
                        $curArray = explode(PHP_EOL, $current);
                        $curLines = count($curArray) - 1;

                        /* Take the buffer we've read and get as
                           many complete lines as we can from it. */
                        while ($curLines > 0){
                            $line = array_pop($curArray);
                            if (trim($line) !== ''){
                                $messages[] = $line;
                                $linesRead++;
                            }
                            $curLines--;
                            /* If we've got the lines we want,
                               break out of both while loops   */
                            if ($linesRead == $targetLines){
                                break 2;
                            }


                        }
                        /* Store the remainder for the next loop */
                        $remainder = $curArray[0];
                        /* If there's nothing left to grab,
                           break out of the outer while loop
                           with what we already have         */
                        if (($sizeRemaining == 0) && ($curLines == 0) && ($linesRead < $targetLines)){
                            $messages[] = $curArray[0];
                            break 1;
                        }
                    }
                }
            }
        }
        return [
            "path"      => $path,
            "messages"  => $messages
        ];
    }
}
