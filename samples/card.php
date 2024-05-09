<table>
    <thead>
        <th>ID</th>
        <th>INFO</th>
    </thead>
    <tbody>
        <?php for($i = 0; $i < 5; $i++):?>
            <tr>
                <td>#<?= $i;?></td>
                <td>This is card number <?= $i;?></td>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>

