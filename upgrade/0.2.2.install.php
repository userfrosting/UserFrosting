--update config table with version information

(13, 'software_version', $version);


--update with new sql tables
$filelist_sql = "CREATE TABLE IF NOT EXISTS `".$db_table_prefix."uf_filelist` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`path` varchar(150) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `path` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
";

$filelist_entry = "INSERT INTO `".$db_file_prefix."uf_filelist` (`id`, `path`) VALUES
(1, 'account'),
(2, 'forms');
";

$stmt = $db->prepare($filelist_sql);
if($stmt->execute())
{
    $successes[] = "<p>".$db_table_prefix."filelist table created.....</p>";
}
else
{
    $errors[] = "<p>Error constructing file list table.</p>";
    $db_issue = true;
}

$stmt = $db->prepare($filelist_entry);
if($stmt->execute())
{
    $successes[] = "<p>Added default file list to the database</p>";
}
else
{
    $errors[] = "<p>Error adding file list to the database</p>";
    $db_issue = true;
}

