<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Extraction Mode</td>
    <td>
        <b>Manual Archive Extraction</b><br/>
        Set the Extraction value to "Manual Archive Extraction" when the archive file has already been manually extracted on the server.  
        This can be done through your host's control panel such as cPanel or by your host directly. 
        This setting can be helpful if you have a large archive files or are having issues with the installer extracting
        the file due to timeout issues.
        <br/><br/>

        <b>PHP ZipArchive</b><br/>
        This extraction method will use the PHP <a href="http://php.net/manual/en/book.zip.php" target="_blank">ZipArchive</a> 
        code to extract the archive zip file.
        <br/><br/>

        <b>PHP ZipArchive Chunking</b><br/>
        This extraction method will use the PHP <a href="http://php.net/manual/en/book.zip.php" target="_blank">ZipArchive</a>
        code with multiple execution threads to extract the archive zip file.
        <br/><br/>

        <b>Shell-Exec Unzip</b><br/>
        This extraction method will use the PHP <a href="http://php.net/manual/en/function.shell-exec.php" target="_blank">shell_exec</a> 
        to call the system unzip command on the server.  This is the default mode that is used if its avail on the server.
        <br/><br/>

        <b>DupArchive</b><br/>
        This extraction method will use the DupArchive extractor code to extract the daf-based archive file.
        <br/><br/>
    </td>
</tr>