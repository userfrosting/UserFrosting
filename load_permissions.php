<?php
/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

include('models/db-settings.php');
include('models/config.php');

// Load all permissions settings.  Recommended access level: admin only.
if (!securePage($_SERVER['PHP_SELF'])){die();}

extract($_GET);

// Parameters: [limit]

$results = array();

$db = pdoConnect();

$sqlVars = array();

$query = "select * from uc_permissions order by name asc";    

//echo $query;
$stmt = $db->prepare($query);
$stmt->execute($sqlVars);

if (!isset($limit)){
    $limit = 9999999;
}
$i = 0;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC) and $i < $limit) {
    $id = $r['id'];
    $results[$id] = $r;
    $i++;
}
$stmt = null;

echo json_encode($results);

?>