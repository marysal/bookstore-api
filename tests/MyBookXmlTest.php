<?php

class MyBookXmlTest extends BaseXmlTest
{
    protected static $singleXMLBook = "
        <books>
            <title>New title</title>
            <description>New description</description>
            <type>poetry</type>
        </books>
    ";

    protected static $bookDataForUpdate = "
        <books>
            <title>Changed title</title>
            <description>Changed description</description>
            <type>prose</type>
        </books>
    ";

}