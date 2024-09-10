<?php

namespace Wncms\Tags\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Wncms\Tags\HasTags;

class Post extends Model
{
    use HasTags;

    protected $fillable = ['title', 'content'];
    protected $isTranslatable = true;
}