<?php

return [

    /*
    |--------------------------------------------------------------------------
    | System Nodes Id
    |--------------------------------------------------------------------------
    |
    | This value is the id of the System node in your Marble CMS Admin 
    | view.
    */
    
    'system_nodes_id' => 23,


    /*
    |--------------------------------------------------------------------------
    | Entry Nodes Id
    |--------------------------------------------------------------------------
    |
    | This value is the id of the Entry node in your Marble CMS Admin 
    | view. You can define any Node Id here, which will be the root of your 
    | tree view.
    */
    
    'entry_node_id' => 1,


    /*
    |--------------------------------------------------------------------------
    | Entry Nodes Id
    |--------------------------------------------------------------------------
    |
    | When this value is set to true, the slugs of your nodes will be prefixed
    | with the appropriate locale prefix. For example /some-news-article will
    | be accessed through /en/some-news-article
    */
    
    'uri_locale_prefix' => true
];
