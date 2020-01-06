<?php
/**
 * Template parts legend
 */

class AF_Templates extends AF_Theme {

    // Placeholder construct function to prevent re-running parent construct
    public function __construct() {}

    // Core structure
    public function af_head($noindex = false) {         include(locate_template('part/structure/head.php'));             }
    public function af_toolbar() {                      get_template_part('part/structure/toolbar');                     }
    public function af_header() {                       get_template_part('part/structure/header');                      }
    public function af_modal_login() {                  get_template_part('part/structure/modal_login');                 }
    public function af_nav_main() {                     get_template_part('part/structure/nav_main');                    }
    public function af_nav_footer() {                   get_template_part('part/structure/nav_footer');                  }
    public function af_footer() {                       get_template_part('part/structure/footer');                      }
    public function af_scripts_head() {                 get_template_part('part/structure/scripts_head');                }
    public function af_scripts_body() {                 get_template_part('part/structure/scripts_body');                }
    public function af_scripts_footer() {               get_template_part('part/structure/scripts_footer');              }


    // Branding
    public function af_logo() {                         get_template_part('part/branding/logo');                         }
    public function af_copyright() {                    get_template_part('part/branding/copyright');                    }
    public function af_favicons() {                     get_template_part('part/branding/favicons');                     }
    public function af_social() {                       get_template_part('part/branding/social');                       }

    // Default
    public function af_single() {                       get_template_part('part/default/single');                        }
    public function af_archive() {                      get_template_part('part/default/archive');                       }
    public function af_author() {                       get_template_part('part/default/author');                        }
    public function af_masthead() {                     get_template_part('part/default/masthead');                      }
    public function af_breadcrumbs() {                  get_template_part('part/default/breadcrumbs');                   }
    public function af_masthead_archive() {             get_template_part('part/default/masthead_archive');              }
    public function af_masthead_author() {              get_template_part('part/default/masthead_author');               }

    // Posts
    public function af_breadcrumbs_post() {             get_template_part('part/post/breadcrumbs_post');                 }
    public function af_single_post() {                  get_template_part('part/post/single_post');                      }
    public function af_single_post_pdf() {              get_template_part('part/post/single_post_pdf');                  }
    public function af_single_post_free() {             get_template_part('part/post/single_post_free');                 }
    public function af_masthead_blog() {                get_template_part('part/post/masthead_blog');                    }
    public function af_blog() {                         get_template_part('part/post/blog');                             }
    public function af_blog_archive() {                 get_template_part('part/post/blog_archive');                     }
    public function af_masthead_category() {            get_template_part('part/post/masthead_category');                }
    public function af_category() {                     get_template_part('part/post/category');                         }
    public function af_category_archive() {             get_template_part('part/post/category_archive');                 }
    public function af_archive_hero_articles() {        get_template_part('part/post/archive_hero_articles');            }
    public function af_post_excerpt() {                 get_template_part('part/post/post_excerpt');                     }
    public function af_post_label() {                   get_template_part('part/post/post_label');                       }
    public function af_post_meta() {                    get_template_part('part/post/post_meta');                        }
    public function af_post_actions() {                 get_template_part('part/post/post_actions');                     }
    public function af_stock_recommendations() {        get_template_part('part/post/stock_recommendations');            }
    public function af_author_bio() {                   get_template_part('part/post/author_bio');                       }
    public function af_related_posts() {                get_template_part('part/post/related_posts');                    }
    public function af_related_feature() {              get_template_part('part/post/related_feature');                  }
    public function af_disqus_thread() {                get_template_part('part/post/disqus_thread');                    }
    public function af_paygate_post() {                 ob_start();
                                                        get_template_part('part/post/paygate_post');
                                                        return ob_get_clean();                                           }

    // Pages
    public function af_page() {                         get_template_part('part/page/page');                             }
    public function af_404() {                          get_template_part('part/page/404');                              }
    public function af_masthead_404() {                 get_template_part('part/page/masthead_404');                     }
    public function af_page_full_width() {              get_template_part('part/page/page_full_width');                  }
    public function af_page_sidebar_left() {            get_template_part('part/page/page_sidebar_left');                }
    public function af_page_sidebar_right() {           get_template_part('part/page/page_sidebar_right');               }
    public function af_page_help() {                    get_template_part('part/page/page_help');                        }
    public function af_page_account() {                 get_template_part('part/page/page_account');                     }
    public function af_page_profile() {                 get_template_part('part/page/page_profile');                     }
    public function af_page_subscriptions() {           get_template_part('part/page/page_subscriptions');               }
    public function af_page_watchlist() {               get_template_part('part/page/page_watchlist');                   }
    public function af_masthead_watchlist() {           get_template_part('part/page/masthead_watchlist');               }
    public function af_search_bar_watchlist() {         get_template_part('part/page/search_bar_watchlist');             }
    public function af_page_ticker() {                  get_template_part('part/page/page_ticker');                      }
    public function af_masthead_ticker() {              get_template_part('part/page/masthead_ticker');                  }
    public function af_search_bar_ticker() {            get_template_part('part/page/search_bar_ticker');                }
    public function af_page_saved_articles() {          get_template_part('part/page/page_saved_articles');              }
    public function af_page_authors() {                 get_template_part('part/page/page_authors');                     }
    public function af_page_about() {                   get_template_part('part/page/page_about');                       }
    public function af_page_choose_path() {             get_template_part('part/page/page_choose_path');                 }
    public function af_page_whats_new() {               get_template_part('part/page/page_whats_new');                   }
    public function af_page_co_reg() {                  get_template_part('part/page/page_co_reg');                      }

    // Publications
    public function af_masthead_publications() {        get_template_part('part/publications/masthead_publications');    }
    public function af_breadcrumbs_publications() {     get_template_part('part/publications/breadcrumbs_publications'); }
    public function af_single_publications() {          get_template_part('part/publications/single_publications');      }
    public function af_archive_publications() {         get_template_part('part/publications/archive_publications');     }
    public function af_nav_pubs() {                     get_template_part('part/publications/nav_pubs');                 }
    public function af_nav_subs() {                     get_template_part('part/publications/nav_subs');                 }
    public function af_nav_pub_tabs() {                 get_template_part('part/publications/nav_pub_tabs');             }
    public function af_pub_excerpt() {                  get_template_part('part/publications/pub_excerpt');              }
    public function af_pub_excerpt_subscription() {     get_template_part('part/publications/pub_excerpt_subscription'); }
    public function af_pub_static() {                   get_template_part('part/publications/pub_static');               }
    public function af_pub_faqs() {                     get_template_part('part/publications/pub_faqs');                 }
    public function af_pub_getting_started() {          get_template_part('part/publications/pub_getting_started');      }
    public function af_pub_articles_list() {            get_template_part('part/publications/pub_articles_list');        }
    public function af_pub_archive() {                  get_template_part('part/publications/pub_archive');              }
    public function af_pub_portfolio() {                get_template_part('part/publications/pub_portfolio');            }
    public function af_pub_portfolio_table() {          get_template_part('part/publications/pub_portfolio_table');      }
    public function af_editor_bio() {                   ob_start();
                                                        get_template_part('part/publications/editor_bio');
                                                        return ob_get_clean();                                           }
    public function af_paygate_pub() {                  ob_start();
                                                        get_template_part('part/publications/paygate_pub');
                                                        return ob_get_clean();                                           }

    // FAQs
    public function af_masthead_help() {                get_template_part('part/faq/masthead_help');                     }
    public function af_single_faq() {                   get_template_part('part/faq/single_faq');                        }
    public function af_faq_section() {                  get_template_part('part/faq/faq_section');                       }
    public function af_faq_category_list() {            get_template_part('part/faq/faq_category_list');                 }
    public function af_taxonomy_help() {                get_template_part('part/faq/taxonomy_help');                     }
    public function af_search_bar_help() {              get_template_part('part/faq/search_bar_help');                   }
    public function af_search_results_help() {          get_template_part('part/faq/search_results_help');               }

    // Front page
    public function af_front_page() {                   get_template_part('part/front-page/front_page');                 }
    public function af_home_pub_content() {             get_template_part('part/front-page/home_pub_content');           }

    // Search
    public function af_masthead_search() {              get_template_part('part/search/masthead_search');                }
    public function af_masthead_search_default() {      get_template_part('part/search/masthead_search_default');        }
    public function af_search_bar() {                   get_template_part('part/search/search_bar');                     }
    public function af_search_results() {               get_template_part('part/search/search_results');                 }
    public function af_search_results_default() {       get_template_part('part/search/search_results_default');         }

    // User
    public function af_nav_user() {                     get_template_part('part/user/nav_user');                         }

    // OpenX
    public function af_openx_home_sidebar() {           get_template_part('part/openx/openx_home_sidebar');              }
    public function af_openx_home_middle() {            get_template_part('part/openx/openx_home_middle');               }
    public function af_openx_archive() {                get_template_part('part/openx/openx_archive');                   }
    public function af_openx_bottom_frontend() {        get_template_part('part/openx/openx_bottom_frontend');           }
    public function af_openx_bottom_free() {            get_template_part('part/openx/openx_bottom_free');               }
    public function af_openx_related() {                get_template_part('part/openx/openx_related');                   }
    public function af_openx_exit_popup() {             get_template_part('part/openx/openx_exit_popup');                }
    public function af_openx_in_content() {             ob_start();
                                                        get_template_part('part/openx/openx_in_content');
                                                        return ob_get_clean();                                           }

    // Account
    public function af_password_form() {                get_template_part('part/account/password_form');                 }
    public function af_subscription_table() {           get_template_part('part/account/subscription_table');            }
    public function af_address_form() {                 get_template_part('part/account/address_form');                  }
}
