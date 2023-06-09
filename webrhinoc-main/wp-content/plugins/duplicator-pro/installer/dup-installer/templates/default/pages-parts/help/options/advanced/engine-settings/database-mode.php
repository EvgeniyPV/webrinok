<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Database Mode</td>
    <td>
        <p>
            <b>Chunking mode:</b><br/>
            Split the work of inserting data across several requests.  
            If your host throttles requests or you're on a shared server that is being heavily utilized by other sites then you should choose this option.
            This is the default option.
        </p>        
        <p>
            <b>Single step:</b><br/>
            Perform data insertion in a single request.  
            This is typically a bit faster than chunking, however it is more susceptible to problems when the database is large or the host is constrained.
        </p>
    </td>
</tr>