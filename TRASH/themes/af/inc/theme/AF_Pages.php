<?php
/**
 * Controls data/logic for pages
 */

class AF_Pages extends AF_Theme {
    // Placeholder construct function to prevent re-running parent construct
    public function __construct() {}

    // Get watchlist
    public function af_get_user_watchlist() {
        global $af_users;
        $ar_tickers = $af_users->getUserTickers($af_users->objAFUser['id']);
        return $ar_tickers;
    }

    // Get Financial Profile
    public function af_get_user_profile() {
        global $af_users;
        $ar_charts = $af_users->getUserProfile($af_users->objAFUser['id']);
        return $ar_charts;
    }

    // Get saved articles
    public function af_get_user_articles() {
        global $af_users;
        $ar_user = $af_users->af_get_user_data();
        $ar_posts = $af_users->getUserArticles($af_users->objAFUser['id']);
        return $ar_posts;
    }
}
