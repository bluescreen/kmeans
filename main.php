<?php

include_once "vendor/autoload.php";

use JpGraph\JpGraph;
use SciPhp\NdArray;
use SciPhp\NumPhp as np;

function dd($value)
{
    dump($value);
    die;
}

function dm($M){
    echo (string)$M."\n";
}

function xrange($start, $limit, $step = 1)
{
    if ($start < $limit) {
        if ($step <= 0) {
            throw new LogicException('Schrittweite muss positiv sein');
        }

        for ($i = $start; $i <= $limit; $i += $step) {
            yield $i;
        }
    } else {
        if ($step >= 0) {
            throw new LogicException('Schrittweite muss negativ sein');
        }

        for ($i = $start; $i >= $limit; $i -= $step) {
            yield $i;
        }
    }
}

function plot(NdArray $X, $file)
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

function nrand($mean, $sd)
{
    $x = mt_rand() / mt_getrandmax();
    $y = mt_rand() / mt_getrandmax();
    return sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $sd + $mean;
}

function randn($size, $dim = 2)
{
    $X = np::zeros($size, $dim);
    $X->walk(function (&$data) {
        $data[0] = nrand(0, 1);
        $data[1] = nrand(0, 1);
    });
    return $X;
}

function concat(NdArray $ar1, NdArray $ar2)
{
    $newData = $ar1->data;
    foreach ($ar2->data as $row) {
        $newData[] = $row;
    }
    return new NdArray($newData);
}

function distance(NdArray $u, NdArray $v)
{
    $diff = $u->subtract($v);
    return $diff->dot($diff);
}

// https://github.com/lazyprogrammer/machine_learning_examples/blob/master/unsupervised_class/kmeans.py
function kmeans(NdArray $X, $K, $maxIter = 20, $beta = 1.0)
{
    list($N, $D) = $X->shape;

    $R = np::zeros($N, $K); // Responsibility Matrix
    $exponents = np::zeros($N, $K); // Exponents

    // initialize M with random values of X
    $M = randomize($X, $K, $N);

    for ($i = 0; $i < $maxIter; $i++) {
        // step 1: determine assignments / resposibilities
        for ($k = 1; $k <= $K; $k++) {
            for ($n = 1; $n <= $N; $n++) {
                // Python:  exponents[n,k] = np.exp(-beta*d(M[k], X[n]))
                $exponents["$n,$k"] = np::exp(-$beta * distance($M[$k], $X[$n]));

                //echo $exponents; die;
                // Python: R = exponents / exponents.sum(axis=1, keepdims=True)
                $sum = $exponents->sumAxis(1, true);
                dm($exponents);
                dm($sum);
                dd($sum->shape);

                $R = $exponents->divide($sum);
            }
        }
        dd($R );

        // step 2: recalculate means
        for ($k = 0; $k < $K; $k++) {
            // Python: M = R.T.dot(X) / R.sum(axis=0, keepdims=True).T
            $sum = np::transpose($R->sumAxis(0,true));
            $M[$k] = np::transpose($R)->dot($X)->divide($sum);
        }
    }

    echo $M;
    die;
}

/**
 * @param NdArray $X
 * @param $K
 * @param $N
 * @return NdArray
 */
function randomize(NdArray $X, $K, $N)
{
    $randomMeans = [];
    for ($k = 0; $k < $K; $k++) {
        $randomMeans[$k] = $X[rand(0, $N)]->data;
    }
    return np::ar($randomMeans);
}

$D = 2; // Dimension
$s = 6;  // Distanz
$N = 20; // Anzahl Punkte
$mu1 = np::ar([0, 0]);
$mu2 = np::ar([$s, $s]);
$mu3 = np::ar([0, $s]);

$X = new NdArray([[14, 17, 12, 33, 44],
                        [15, 6, 27, 8, 19],
                        [23, 2, 54, 1, 4,]]  );

/*
dm($X->sumAxis());
dm($X->sumAxis(0,false));
dm($X->sumAxis(1,false));
dm($X->sumAxis(1,true));
dm($X->sumAxis(0,true));
die;*/

$X1 = randn($N, $D)->add($mu1);
$X2 = randn($N, $D)->add($mu2);
$X3 = randn($N, $D)->add($mu3);

$X = concat(concat($X1, $X2), $X3);

$result = kmeans($X, 3, 20, 1.0);

echo $X->count() . "\n";
plot($X, 'data.jpeg');
