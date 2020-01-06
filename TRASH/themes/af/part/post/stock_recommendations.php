<?php
global $af_theme;

$stock_recommendations = $post_data['article_stock_recommendations'];

if ($stock_recommendations) {
?>
<h4 class="centered">Actions to Take</h4>
<table class="table-responsive recommendations-table table-centered">
    <thead>
        <tr>
            <th>Action</th>
            <th>Symbol</th>
            <th>Name</th>
            <th>Action Description</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($stock_recommendations as $stock) {
            if (($stock['action'] === 'Buy') || ($stock['action'] === 'buy')) {
                $label = 'success';
                $action = 'Buy';
            }
            if (($stock['action'] === 'Sell') || ($stock['action'] === 'sell')) {
                $label = 'alert';
                $action = 'Sell';
            }
            if (($stock['action'] === 'Hold') || ($stock['action'] === 'hold')) {
                $label = 'warning';
                $action = 'Hold';
            }
            if (($stock['action'] === 'Buy To Close') || ($stock['action'] === 'btoclose')) {
                $label = 'alert';
                $action = 'Buy To Close';
            }
            if (($stock['action'] === 'Buy To Open') || ($stock['action'] === 'btoopen')) {
                $label = 'success';
                $action = 'Buy To Open';
            }
            if (($stock['action'] === 'Sell To Open') || ($stock['action'] === 'stoopen')) {
                $label = 'success';
                $action = 'Sell To Open';
            }
            if (($stock['action'] === 'Sell To Close') || ($stock['action'] === 'stoclose')) {
                $label = 'alert';
                $action = 'Sell To Close';
            }
        ?>
        <tr>
            <td data-label="Action"><span class="label <?php echo $label; ?>"><?php echo $action; ?></span></td>
            <td data-label="Symbol"><?php echo $stock['ticker_symbol']; ?></td>
            <td data-label="Name"><?php echo $stock['name']; ?></td>
            <td data-label="Action Description"><?php echo $stock['action_description']; ?></td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
<hr class="custom-separator">
<?php
}