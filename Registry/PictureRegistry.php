<?php

namespace Kern\PictureBundle\Registry;

use Kern\PictureBundle\Entity\Picture;

class PictureRegistry
{
    /** @var array */
    protected $pictures = [];

    public function add(Picture $picture)
    {
        if (!isset($this->pictures[$picture->getHash()])) {
            $this->pictures[$picture->getHash()] = $picture;
        }
    }

    public function getAll(): array
    {
        return $this->pictures;
    }
}