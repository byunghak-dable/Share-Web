<?php

namespace app\data;

class BoardReadData
{

    private $title;
    private $writer;
    private $date;
    private $hit;
    private $content;

    public function getWriter()
    {
        return $this->writer;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getHit()
    {
        return $this->hit;
    }

    public function getContent()
    {
        return $this->content;
    }
}