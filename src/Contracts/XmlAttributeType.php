<?php

namespace Lazychaser\DataImportTools\Contracts;

use XMLReader;

interface XmlAttributeType
{
    /**
     * @param XMLReader $reader
     *
     * @return array
     */
    public function parse(XMLReader $reader);
}