<h1>Thông tin Server</h1>
<table>
    <?php
    foreach ($_SERVER as $k => $v) {
    ?>
        <tr>
            <td><?php echo $k; ?></td>
            <td><?php echo $v; ?></td>
        </tr>
    <?php
    }
    ?>
</table>