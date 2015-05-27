<?php

namespace GoogleShopping\Loop;

use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class UnAssociatedCategory extends BaseLoop implements ArraySearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildArray()
    {
        $file = file("http://www.google.com/basepages/producttype/taxonomy.en-US.txt");

        $res = array();

        foreach ($file as $ligne) {
            if ($ligne[0] !== '#') {
                $res[] = $ligne;
                $level = substr_count($ligne, ">") + 1;
                $cats = explode(">", $ligne);
                $row = "<CAT nb='". $level ."'><![CDATA[\"".end($cats)."\"]]></CAT>";
                echo str_replace(array("\n", "\t", "\r"), '', $row);
            }
        }

        $result = implode(';', $res);

        return array('string'=>$result);
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $data) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow->set("CAT", $data);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

}