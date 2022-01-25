<?php

use App\Tests\MainXmlTest;

class BookXmlTest extends MainXmlTest
{
    protected static $singleXMLBook = "
        <books>
            <title>New title</title>
            <description>New description</description>
            <type>poetry</type>
        </books>
    ";

}