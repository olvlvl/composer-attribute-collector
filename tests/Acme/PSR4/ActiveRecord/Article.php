<?php

namespace Acme\PSR4\ActiveRecord;

use Acme\Attribute\ActiveRecord\Index;
use Acme\Attribute\ActiveRecord\Serial;
use Acme\Attribute\ActiveRecord\Text;
use Acme\Attribute\ActiveRecord\Varchar;

#[Index('slug', unique: true)]
class Article
{
    #[Serial(primary: true)]
    public int $id;

    #[Varchar(80)]
    public string $title;

    #[Varchar(80)]
    public string $slug;

    #[Text]
    public string $body;
}
