<?php

namespace App\Entity;




class Btreecopy
{

    private ?string $_id = null;


    private array $_content;

    public function setId(?string $id): void
    {
        $this->_id = $id;
    }

    public function GetId(): string
    {
        return $this->_id;
    }

    public function setContent(array $content): void
    {
        $this->_content = $content;
    }

    public function getContent(): array
    {
        return $this->_content;
    }
}
