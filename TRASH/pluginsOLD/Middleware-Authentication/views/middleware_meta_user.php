<div id="accordion">

    <?php if(!empty($content)) { display_object($content); } ?>

</div>

<script>
    jQuery('h2').each(function(){
        jQuery(this).nextUntil('h2')
            .wrapAll('<div>').parent().add(this);
        if(typeof jQuery(this).nextUntil('h2')[0] === 'undefined'){
            jQuery(this).after('<div></div>');
        }
    });
    jQuery(function() {
        jQuery('#accordion').accordion({
            heightStyle: "content",
            widthStyle: "content",
            collapsible: true,
            autoHeight: false,
            navigation: true,
            active: false
        });
    });
</script>

<?php

function display_object($input){
    if(!empty($input) AND is_array($input)){
        foreach($input as $key=>$value){
            if(is_array($value)){

                echo "<h2> $key:  </h2>";
                display_object($value);

            }else if(is_object($value)){

                if($key !== 'subscriptionsAndOrders'){
                    echo "<h3> $key:  </h3>";
                }
                display_object($value);

            }else{
                echo "<p> $key: $value </p>";
            }
        }
    }
}
