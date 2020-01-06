<?php
global $post, $af_theme;

if (defined('DISQUS_SUBDOMAIN') && DISQUS_SUBDOMAIN != '' && DISQUS_SUBDOMAIN != 'DISQUS_SUBDOMAIN' && (has_category($af_theme->af_get_free_article_cats()) || defined('FREE_ARTICLES_ONLY') && FREE_ARTICLES_ONLY === true)) {
?>
<div class="row">
    <div class="small-12 large-10 large-centered columns">
        <div id="disqus_thread"></div>
        <script>
        /**
         *  RECOMMENDED CONFIGURATION VARIABLES: EDIT AND UNCOMMENT THE SECTION BELOW TO INSERT DYNAMIC VALUES FROM YOUR PLATFORM OR CMS.
         *  LEARN WHY DEFINING THESE VARIABLES IS IMPORTANT: https://disqus.com/admin/universalcode/#configuration-variables
         */

        var disqus_config = function() {
            this.page.url = '<?php echo $post->permalink; ?>'; // Replace PAGE_URL with your page's canonical URL variable
            this.page.identifier = '<?php echo $post->ID; ?>'; // Replace PAGE_IDENTIFIER with your page's unique identifier variable
        };

        (function() { // DON'T EDIT BELOW THIS LINE
            var d = document,
                s = d.createElement('script');
            s.src = 'https://<?php echo DISQUS_SUBDOMAIN ?>.disqus.com/embed.js';
            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
        </script>
        <noscript>Please enable JavaScript to view the <a href="//disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    </div>
</div>
<?php
}