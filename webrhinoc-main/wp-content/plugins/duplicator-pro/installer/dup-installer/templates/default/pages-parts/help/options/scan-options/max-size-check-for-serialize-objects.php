<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<tr>
    <td class="col-opt">Serialized obj max size</td>
    <td>
        Large serialized objects can cause a fatal error when Duplicator attempts to transform them. <br>
        If a fatal error is generated, lower this limit. <br>
        If a warning of this type appears in the final report <br>
        <pre style="white-space: pre-line;">
        DATA-REPLACE ERROR: Serialization
        ENGINE: serialize data too big to convert; data len: XXX Max size: YYY
        DATA: .....
        </pre>
        And you think that the serialized object is necessary you can increase the limit or <b>set it to 0 to have no limit</b>.
    </td>
</tr>