<?php

namespace App;

use Corcel\Post;

class Machine extends Post
{
    protected $postType = 'machine';

    protected $fillable = [
        'post_content',
        'post_title',
        'post_name',
        'post_excerpt',
        'post_type',
        'to_ping',
        'pinged',
        'post_content_filtered'
    ];
}
