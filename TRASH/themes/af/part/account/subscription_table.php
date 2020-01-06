<?php
global $af_users;
$user_subscriptions = $af_users->af_subscription_data();
?>
<div class="row">
    <div class="small-12 large-10 large-centered columns">
        <?php if (empty($user_subscriptions)) { ?>
        <p class="callout warning">You currently have no subscriptions.</p>
        <?php } else { ?>
        <table class="table-subscriptions table-responsive">
            <thead>
                <tr>
                    <th>Subscriptions</th>
                    <th>Start Date</th>
                    <th>Expiration Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($user_subscriptions as $sub) { ?>
                <tr>
                    <td data-label="Subscription" class="subscription-name"><a href="<?php echo get_permalink($sub['sub_pub_id']); ?>"><?php echo $sub['sub_name']; ?></a></td>
                    <td data-label="Start Date" class="subscription-start"><?php echo $sub['sub_start']; ?></td>
                    <td data-label="Expiration Date" class="subscription-end"><?php echo $sub['sub_end']; ?></td>
                    <td class="subscription-renew"><?php echo $sub['sub_renew']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>