<?php
namespace App;

use Graph;
use JpGraph\JpGraph;
use ScatterPlot;
use SciPhp\NdArray;

class Plot
{
    public static function scatter(NdArray $X, $file)
    {
        JpGraph::load();
        JpGraph::module('scatter');

        $datax = $datay = [];
        foreach ($X->data as $record) {
            $datax[] = $record[0];
            $datay[] = $record[1];
        }

        $graph = new Graph(1024, 768);
        $graph->SetScale("linlin");

        $graph->img->SetMargin(40, 40, 40, 40);
        $graph->SetShadow();

        $graph->title->Set("A simple scatter plot");
        $graph->title->SetFont(FF_FONT1, FS_BOLD);

        $sp1 = new ScatterPlot($datay, $datax);

        $graph->Add($sp1);
        $graph->Stroke($file);
    }
}
