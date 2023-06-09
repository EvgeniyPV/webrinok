<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Cross search</td>
    <td>
        This option enables the searching and replacing of subsite domains and paths that link to each other.  <br>
        Check this option if hyperlinks of at least one subsite point to another subsite.<br>
        Uncheck this option there if there are at least <?php echo MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH ?> 
        subsites and no subsites hyperlinking to each other (Checking this option in this scenario would unnecessarily load your server).<br><br>
        Check this option If you unsure if you need this option.<br></td>
</tr>