<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>

<tr>
    <td class="col-opt">Unlocked</td>
    <td>
        "Unlocked" means that if your installer is on a public server that anyone can access it.  
        This is a less secure way to run your installer. If you are running the
        installer very quickly then removing all the installer files, then the chances of exposing it is going to be low depending  
        on your sites access history.
        <br/><br/>

        While it is not required to have a password set it is recommended.  
        If your URL has little to no traffic or has never been the target of an attack
        then running the installer without a password is going to be relatively safe if ran quickly.  
        However, a password is always a good idea.  
        Also, it is absolutely required and recommended to remove <u>all</u> installer files after 
        installation is completed by logging into the WordPress admin and following the Duplicator prompts.
    </td>
</tr>