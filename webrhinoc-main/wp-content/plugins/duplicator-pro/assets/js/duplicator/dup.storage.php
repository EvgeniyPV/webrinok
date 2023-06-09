<?php

/*! ============================================================================
*  STORAGE NAMESPACE: All methods at the top of the Duplicator Namespace
*  =========================================================================== */

/**
* Returns the FontAwesome storage type icon.
*
* @param int        id   An id based on the PHP class DUP_PRO_Storage_Types
* @return string    Returns the font-awesome icon
*
* @see DUP_PRO_Storage_Types in file class.storage.entity.php
*      DUP_PRO_Storage_Entity::getStorageIcon
*/

?>
<script>
Duplicator.Storage.getFontAwesomeIcon = function(id) {
    var icon;
    switch (id) {
        case 0: icon = '<i class="fa fa-server"></i>';              break;
        case 1: icon = '<i class="fab fa-dropbox"></i>';            break;
        case 2: icon = '<i class="fas fa-network-wired"></i>';      break;
        case 3: icon = '<i class="fab fa-google-drive"></i>';       break;
        case 4: icon = '<i class="fab fa-aws"></i>';                break;
        case 5: icon = '<i class="fas fa-network-wired"></i>';      break;
        case 6:
        case 7: icon = '<i class="fas fa-cloud"></i>';              break;
        default:icon = '<i class="fas fa-cloud"></i>';              break;
    }
    return icon;
};

</script>