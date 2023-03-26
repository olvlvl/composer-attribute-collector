<?php

namespace Acme\PSR4\ActiveRecord;

use Acme\Attribute\ActiveRecord\Boolean;
use Acme\Attribute\ActiveRecord\Id;
use Acme\Attribute\ActiveRecord\Index;
use Acme\Attribute\ActiveRecord\Serial;
use Acme\Attribute\ActiveRecord\Text;
use Acme\Attribute\ActiveRecord\Varchar;

#[Index('active')]
class Article
{
    #[Id]
    #[Serial]
    public int $id;

    #[Varchar(80)]
    public string $title;

    #[Varchar(80, unique: true)]
    public string $slug;

    #[Text]
    public string $body;

    #[Boolean]
    public bool $active;
}
